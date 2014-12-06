<?php

	use Illuminate\Database\Eloquent\SoftDeletingTrait;

	class Char extends DataHolder {
		use SoftDeletingTrait;

		protected $table = 'chars';
		protected $fillable = array('name');

		protected static $cache = [];

		var $attribs = 10;
		var $istats = [];

		var $relocs = array(
			  'maxhp' => self::DATA_HPMX
			, 'maxmp' => self::DATA_MPMX
			, 'curhp' => self::DATA_HPCR
			, 'curmp' => self::DATA_MPCR
			);

		static function get($id = null) {
			if (!$id) $id = RUSH::oGet('char');

			$id = intval($id);

			if (!$id) return null;

			if (!isset(self::$cache[$id]))
				self::$cache[$id] = self::find($id);

			return self::$cache[$id];
		}

		public function user() {
      return $this->belongsTo('User'); 
    }

		public function location() {
      return $this->belongsTo('Location'); 
    }
		public function home() {
      return $this->belongsTo('Location'); 
    }
		public function cinematics() {
      return $this->belongsTo('Location'); 
    }

    static function updatedEquip($charId, $fillStats = false) {
    	$char = Char::get($charId);
    	$char->recalcStats($fillStats);
    	$char->save();
    }

		function specStr() {
			$r = RUSH::races();
			$s = RUSH::specs();
			$f = RUSH::fracs();
			return
				$r[$this->race] . ' - ' . $s[$this->spec] . ' (' . $f[$this->fraction] . ')';
		}

		public function makeClickable($sub = null, $title = null) {
			if ($sub) $sub = "$sub/"; 
			return '<a href="' . URL::to(strtolower(get_class($this)) . "/$sub" . ($this->name)) . '">' . ($title ?: $this->name) . '</a>';
		}


		static function levelToExp($level) {
			$base = array(
				0 => 100
			, 1 => 500
			, 2 => 2500
			, 3 => 12500
			, 4 => 62500
			, 5 => 312500
			, 6 => 1562500
			, 7 => 7812500
			, 8 => 39062500
			, 9 => 195312500
			, 10=> 976562500
			);
			$rest = $level % 10;
			$decade = floor($level / 10);
			if ($decade > 10) $decade = 10;
			$a = $base[$decade];
			$b = $base[$decade + 1];
//			echo "[$level> $a-$b:$decade ($rest)]<br>";
			return $a + intval(($b - $a) * ($rest / 10));
		}

		function gainExp($exp) {
			$e = $this->exp + intval($exp);
			while ($e > 0) {
				$le = self::levelToExp($this->level);
				if ($e >= $le) {
					$this->levelUp();
					$e -= $le;
				} else
					break;
			}
			$this->exp = $e;
			$this->save();
		}

		function gainGold($gold) {
			$this->gold += intval($gold);
			if ($this->gold < 0)
				$this->gold = 0;
			$this->save();

			return $this->gold;
		}

		static function baseStat($stat) {
			switch ($stat) {
			case RUSH::STAT_STAM : return 6;
			case RUSH::STAT_INT  : return 7;
			case RUSH::STAT_STR  : return 10;
			case RUSH::STAT_AGI  : return 10;
			case RUSH::STAT_AP   : return 20;
			case RUSH::STAT_MCRIT: return 6;
			case RUSH::STAT_SPD  : return 14;
			case RUSH::STAT_SCRIT: return 11;
			}
		}

		static function baseLvlStat($stat, $lvl) {
			switch ($stat) {
			case RUSH::STAT_STAM: return 4 * $lvl;
			case RUSH::STAT_INT : return 3 * $lvl;
			case RUSH::STAT_STR : return 2 * $lvl;
			case RUSH::STAT_AGI : return 1 * $lvl;
			}
		}

		function statToRel($stat, $noequip = false) {
			switch ($stat) {
			case RUSH::STAT_STAM : return $this->calcStat($stat, $noequip) * 20;
			case RUSH::STAT_INT  : return $this->calcStat($stat, $noequip) * 15;
			case RUSH::STAT_AP   : return $this->calcStat(RUSH::STAT_STR, $noequip) * 2;
			case RUSH::STAT_SPD  : return $this->calcStat(RUSH::STAT_INT, $noequip) * 2;
			case RUSH::STAT_MCRIT:
				$baseagi = $this->calcStat(RUSH::STAT_AGI, true);
				$curagi  = $this->calcStat(RUSH::STAT_AGI, $noequip);
				return normCrit(($curagi * self::baseStat($stat)) / $baseagi);
			case RUSH::STAT_SCRIT:
				$baseagi = $this->calcStat(RUSH::STAT_INT, true);
				$curagi  = $this->calcStat(RUSH::STAT_INT, $noequip);
				return normCrit(($curagi * self::baseStat($stat)) / $baseagi);
			}
		}

		function calcStat($stat, $noequip = false) {
			$v = self::baseStat($stat) + self::baseLvlStat($stat, $this->level);
			if ($noequip) 
				return $v;
			
			if (!$this->istats) 
				$this->fetchInventoryStats();

			return $v + (isset($this->istats[$stat]) ? intval($this->istats[$stat]) : 0);
		}

		function fetchInventoryStats() {
			$i = Equip::get($this->id);
			$s = array();
			if (count($i->items))
				foreach ($i->items as &$item) {
					$e = Item::fetchEffects($item->template->id);
					if (count($e))
						foreach ($e as $type => &$effect)
							switch ($type) {
							case RUSH::STAT_STAM :
							case RUSH::STAT_INT  :
							case RUSH::STAT_STR  :
							case RUSH::STAT_AGI  :
							case RUSH::STAT_SPD  :
							case RUSH::STAT_AP   :
							case RUSH::STAT_MCRIT:
							case RUSH::STAT_SCRIT:
								$s[$type] = intval($s[$type]) + intval($effect);
								break;
							default:
								if (!is_array($e[$type]))
									$e[$type] = array();
								$e[$type][] = $value;
							}
				}
			return $this->istats = $s;
		}

		function levelUp() {
			$this->level ++;
			if ($this->level > 100) $this->level = 100;
			$this->recalcStats();
		}

		function recalcStats($max = true) {
			$this->pset('maxhp', $hp = $this->statToRel(RUSH::STAT_STAM));
			$this->pset('maxmp', $mp = $this->statToRel(RUSH::STAT_INT));
			if ($max) {
				$this->pset('curhp', $hp);
				$this->pset('curmp', $mp);
			} else {
				if (($h = $this->pget('curhp')) > $hp) $this->pset('curhp', $hp);
				if (($m = $this->pget('curmp')) > $mp) $this->pset('curmp', $mp);
			}
		}

		function gainMana($amount) {
			$base = intval($this->pget('curmp'));
			$max  = intval($this->pget('maxmp'));
			$base += intval($amount);
			$this->pset('curmp', $base < 0 ? 0 : ($base > $max ? $max : $base));
			$this->save();
		}

		function gainHealth($amount) {
			$base = intval($this->pget('curhp'));
			$max  = intval($this->pget('maxhp'));
			$base += intval($amount);
			$this->pset('curhp', $base < 0 ? 0 : ($base > $max ? $max : $base));
			$this->save();
		}

		function teleport($location) {
			$this->location_id = $location;
			RUSH::oSet('npc', 0);
			RUSH::oSet('quest', 0);
		}

		function resurrect() {
			$this->cinematics_id = 0;
			$this->recalcStats();
			$this->save();
			RUSH::oSet('npc', 0);
			RUSH::save();
		}

		function locatedAt($noCinematics = false) {
			$cinematics = $noCinematics ? 0 : $this->cinematics_id;
			return $cinematics ? $cinematics : $this->location_id;
		}

		function fracRelation($fraction) {
			return RUSH::fracRelation($fraction, $this->fraction);
		}
	}
