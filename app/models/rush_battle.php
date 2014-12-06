<?php
	require_once 'rush_npc.php';
	require_once 'rush_charachter.php';

	class Spell {
		const SE_HEAL   = 1;
		const SE_DAMAGE = 2;
		const SE_DRAIN  = 3;
		const SE_ABSORB = 4;
		const SE_IMMUNE = 5;

		const SS_PHYSIC = 1;
		const SS_MAGIC  = 2;

		static $cache   = array();

		static function get($id) {
			if (!isset(self::$cache[$id]))
				self::$cache[$id] = new self($id);

			return self::$cache[$id];
		}

		private function __construct($id) {
			$o = msqlDB::o();
			$s = $o->select('_spell', '`id` = ' . $id . ' limit 1');
			if ($s && ($r = @mysql_fetch_assoc($s))) {
				$this->id = $id;
				$this->title = $r[title];
				$this->desc = $r[desc];
				$this->type = intval($r[type]);
				$this->cd = intval($r[cd]);
				$this->school = intval($r[school]);
				$this->dmg = intval($r[dmg]);
				$this->mana = intval($r[mana]);
			}
		}

		function aquireDmg($char) {
			switch ($this->school) {
			case Spell::SS_PHYSIC:
				$ap = $char->statToRel(RUSH::STAT_AP);
				$cr = $char->statToRel(RUSH::STAT_MCRIT);
				break;
			case Spell::SS_MAGIC :
				$ap = $char->statToRel(RUSH::STAT_SPD);
				$cr = $char->statToRel(RUSH::STAT_SCRIT);
				break;
			}

			$crit = rand(0, 100) <= $cr;
			$ap = intval($ap * ($this->dmg / 100));
			return array(0 => $ap, 1 => $crit);
		}
	}

	class Battle {
		const BS_INPROGRESS = 0;
		const BS_FINISHED   = 1;
		const BS_CANJOIN    = 2;

		static $cache = array();
		var $chars = array();

		static function get($enemy = 0) {
			if (!$enemy) $enemy = RUSH::oGet('enemy');

			if (!$enemy) RUSH::redirect('location');
			$enemy = intval($enemy);

			if (!isset(self::$cache[$enemy]))
				self::$cache[$enemy] = new self($enemy);

			return self::$cache[$enemy];
		}

		private function __construct($enemyid) {
			$this->enemy = $enemyid;
			$char = Char::get();
			$this->char  = $char->id;
			$this->basemana = $char->statToRel(RUSH::STAT_INT, true);
			$this->o = msqlDB::o();
			$this->load();
		}
		function load() {
			$s = $this->o->select('_battle', '`enemy` = ' . intval($this->enemy) . ' limit 1');
			if ($s && ($r = @mysql_fetch_array($s))) {
				$this->id    = intval($r['id']);
				$this->start = intval($r['started']);
				$this->stop  = intval($r['stopped']);
				$this->win   = intval($r['win']);
			}
		}

		static function begin($enemyid, $char) {
			$b = new self($enemyid);
			if (!$b->id) {
				$s = $b->o->insert('_battle', array('enemy' => $enemyid, 'started' => time()), true);
				$r = @mysql_fetch_row($s);
				$id = intval($r[0]);
				$b = new self($enemyid);
			}
			return $b->casterJoin($char);
		}

		function fetchCasters() {
			if (!$this->casters) {
				$this->casters = array();
				$s = $this->o->select('_battle_caster', '`battle` = ' . intval($this->id), '`id` as `0`, `caster` as `1`, `fleed` as `2`, `damage` as `3`');
				if ($s && count($r = $this->o->fetchrows($s)))
					foreach ($r as $row)
						$this->casters[intval($row[1])] = array(0 => intval($row[0]), 1 => intval($row[2]), 2 => intval($row[3]));

				$this->aquireEnemy();
			}
			return $this->casters;
		}

		function calcAggro() {
			if (!$this->casters)
				$this->fetchCasters();

			$a = 0;
			$i = $this->char;
			if (count($this->casters))
				foreach ($this->casters as $id => &$c)
					if (!$c[1] && $c[2] > $a) {
						$a = $c[2];
						$i = $id;
					}
			return $i;
		}

		function state($charid) {
			if ($this->stop > $this->start) // already finished
				return self::BS_FINISHED;

			return $this->inBattle($charid) // joined and not fleed
				? self::BS_INPROGRESS // you in this battle already
				: self::BS_CANJOIN; // you can join
		}

		function inBattle($charid) {
			$casters = $this->fetchCasters();
			return ($self = $casters[$charid]) && !$self[1];
		}

		function casterJoin($charid) {
			if ($this->state($charid) != self::BS_CANJOIN)
				return false;

			$u = $this->casters[$charid];
			if ($u) // already joined, but fleed
				$this->o->update('_battle_caster', array('fleed' => 0), '`id` = ' . ($id = $u[0]));
			else {
				$s = $this->o->insert('_battle_caster', array('battle' => $this->id, 'caster' => $charid), true);
				$r = @mysql_fetch_row($s);
				$id = intval($r[0]);
				$this->casters[$charid] = array(0 => $id, 1 => 0, 2 => 0);
			}
			return $id;
		}

		function casterFlee($charid) {
			if ($this->state($charid) != self::BS_INPROGRESS)
				return false;

			$u = $this->casters[$charid];
			if ($u) // already joined, but fleed
				$this->o->update('_battle_caster', array('fleed' => 1), '`id` = ' . ($id = $u[0]));
			else
				$id = 0;

			return $id;
		}

		function log($caster, $target, $spell, $amount, $crit) {
			return $this->o->insert('_battle_log', array(
				'battle' => $this->id
			, 'caster' => $caster
			, 'target' => $target
			, 'spell' => $spell
			, 'crit' => $crit
			, 'amount' => $amount
			, 'time' => ($spell > 0) ? time() : time() + 1
			));
		}

		function fetchLog() {
			$s = $this->o->select('_battle_log', '`battle` = ' . $this->id . ' order by `id` desc limit 10', '`caster`, `target`, `spell`, `crit`, `amount`, `time`');
			return $this->_log = $this->o->fetchrows($s);
		}


		// $spellid = 0 -> exp, -1 -> kill, -2 -> objective, -3 -> enemy hit
		function logExp($caster, $exp) {
			return $this->log($caster, 0, 0, $exp, 0);
		}

		function logKill($objective) {
			return $this->log($caster, 0, $objective ? -2 : -1, 0, 0);
		}

		function renderLog() {
			$t = '';
			$log = $this->fetchLog();
			if (count($log)) {
				$t .= '<hr /><div id="battlelog"><h4>{$battlelog}</h4>';
				$this->aquireEnemy();
				foreach ($log as $rec) {
					$time     = date('H:i:s', intval($rec['time']));
					$casterid = intval($rec['caster']);
					$caster   = $this->aquireCaster($casterid)->nick;
					$targetid = intval($rec['target']);
					$target   = $this->aquireCaster($targetid)->nick;
					$spellid  = intval($rec['spell']);
					$amount = intval($rec['amount']);
					switch ($spellid) {
					case-2:
						$t .= '<p>[<span style="color: gray">' . $time . '</span>] Quest objective progress!</p>';
					case-1:
						$t .= '<p>[<span style="color: gray">' . $time . '</span>] <a>' . $target . '</a> killed!</p>';
						break;
					case 0:
						$t .= '<p>[<span style="color: gray">' . $time . '</span>] <a>' . $caster . '</a> received ' . $amount . ' experiance!</p>';
						break;
					default:
						$crit   = intval($rec['crit']) ? '<b style="color: red">' . normValue($amount) . '</b>' : normValue($amount);
						$spell  = Spell::get($spellid);
						switch ($spell->type) {
						case Spell::SE_HEAL:
							$f = '%s hp healed';
							break;
						case Spell::SE_DAMAGE:
							$f = '%s damage points';
							break;
						case Spell::SE_DRAIN:
							$f = '%s hp drained';
							break;
						case Spell::SE_ABSORB:
							$f = 'target now imune to damage';
							break;
						case Spell::SE_IMMUNE:
							$f = '%s hp drained';
							break;
						}
						$t .=
								'<p>'
							. '[<span style="color: gray">' . $time . '</span>] <a>' . $caster . '</a>\'s [<a>' . $spell->title . '</a>] on <a>' . $target . '</a>'
							. ', ' . sprintf($f, $crit)
							. '</p>';
					}
				}
				$t .= '</div>';
			}
			return $t;
		}

		function aquireEnemy() {
			if (!isset($this->chars[0])) {
				$this->chars[0] = Creature::loadSpawn($this->enemy);
				$this->chars[0]->nick = ($this->chars[0]->npc->subtitle ? $this->chars[0]->npc->subtitle . ' ' : '') . $this->chars[0]->npc->title;
			}
			return $this->chars[0];
		}

		function aquireCaster($charid) {
			if (!isset($this->chars[$charid]))
				$this->chars[$charid] = Char::get($charid);

			return $this->chars[$charid];
		}

		function render() {
			$e = $this->aquireEnemy();
			if (!$e->id) return;
			$t = '';
			$t .= $e->render(0, true);
			$state = $this->state($this->char);
//			debug($this);

			$aggro = $this->calcAggro();
			$c = $this->casters;
			if (count($c)) {
				$t .= '<h4>Casters:</h4>';
				$f = array('lime', 'silver');
				foreach ($c as $id => $caster) {
					$e  = $this->aquireCaster($id);
					$t .= '<p class="cstat"><img class="battle" src="/theme/img/icons/' . ($aggro == $id ? 'def' : 'att') . '.png"><a href="/char/view/' . $id . '">' . $e->nick . '</a><span>[<b style="color: ' . $f[$caster[1]] . '">' . $caster[2] . '</b> AP]</span></p>';
				}
			}

			switch ($state) {
			case self::BS_INPROGRESS:
				View::dlgButton('/battle/leave', 'leave');
				$s = $this->fetchSpells();
//				debug($s);
				if (count($s)) {
					$t .= '<h4>Spells:</h4>';
					foreach ($s as $id => $spell) {
//						$e  = $this->aquireCaster($id);
						$spd = $spell->aquireDmg($this->chars[$this->char]);
						$spd = intval($spd[0] * 0.9);
						$t .= '<p class="cstat">[<a href="/spell/' . $id . '">?</a>] '
						. '<a href="/battle/spell/' . $id . '">' . $spell->title . '</a>'
						. '<span style="color: lime; min-width: 120px;">AP: ~' . normValue($spd) . '</span>'
						. '<span style="color: skyblue; min-width: 60px;">' . $this->manacost($id) . ' mp</span></p>';
					}
				}

				$t .= $this->renderLog();
				break;
			case self::BS_FINISHED:
				if ($this->casters[$this->char])
					View::dlgButton('/battle/loot', 'loot');
				$t .= $this->renderLog();
				break;
			case self::BS_CANJOIN:
				View::dlgButton('/battle/join', 'join');
				break;
			}

			return $t;
		}

		function fetchSpells() {
			if ($this->state($this->char) != self::BS_INPROGRESS)
				return false;

			$a = array();
			$s = $this->o->select('_char_spells', '`char` = ' . $this->char, '`spell` as `0`');
			if ($s)
				while ($r = @mysql_fetch_row($s))
					$a[$id = intval($r[0])] = Spell::get($id);

			return $this->spells = $a;
		}

		function manacost($id) {
			return intval($this->basemana * ($this->spells[$id]->mana / 100.0));
		}

		function cast($spellid) {
			if ($this->state($this->char) != self::BS_INPROGRESS)
				return -1;

			if (!$this->spells)
				$this->fetchSpells();

			$s = $this->spells[$spellid];
			if (!isset($s))
				return -2;

//			debug($this);

			$char = $this->aquireCaster($this->char);

			$cost = $this->manacost($spellid);

			if (intval($char->pget('curmp')) < $cost)
				return -3;

//			echo 'spell costs ' . $cost . ' mana...<br>';

			$dmg = $s->aquireDmg($char);
			$crit = $dmg[1];
			$ap = ($crit ? 2.0 : 1.0) * intval($dmg[0] * rand(700, 1000) / 1000);

			$target = $char->id;
			$caster = $char->id;

			$enemyhp = 1;
			switch ($s->type) {
			case Spell::SE_HEAL:
				// todo: heal mechanics
				$this->recordDamage($ap / 2);
				$this->chars[$target]->gainHealth($ap);
//				var_dump($this->chars[$target]);
//				die();
				break;
			case Spell::SE_DAMAGE:
				$target = 0;
				$enemyhp = $this->chars[0]->hit($ap);
				$this->recordDamage($ap);
//				debug($this->chars[0]);
				$this->chars[0]->save();
				break;
			default:
				// todo: drain mechanics, absorb & immune mechanics
			}
			$this->log($caster, $target, $spellid, $ap, $crit);
			$char->gainMana($cost ? -$cost : $this->basemana / 50);

			if (!$enemyhp)
				$this->finish(true); // enemy is dead
			else {
				$aggro = $this->calcAggro();
				if ($aggro == $this->char) {
					$this->enemyRespond();
					if (intval($char->pget('curhp')) <= 0)
						$this->killed($char->id);
				}
			}
		}

		function recordDamage($amount) {
			$c = &$this->casters[$this->char];
			$c[2] += intval($amount);
			$this->o->update('_battle_caster', array('damage' => $c[2]), '`id` = ' . $c[0]);
		}

		function killed($charid) {
			require_once 'rush_loc.php';
			$dmg = $this->casters[$charid][2];
			$this->recordDamage(-$dmg);
			$this->casterFlee($charid);
			$loc = Location::get($this->chars[0]->loc);
			$cementary = $loc->aqiureCementary();
//			RUSH::oSet('location', $cementary[0]);
//			RUSH::save();
//			$this->chars[$charid]->teleport($cementary[0]);
			$this->chars[$charid]->pset('cinematics', $cementary[0]);
			$this->chars[$charid]->save();
			RUSH::redirect('npc/' . $cementary[1]);
		}

		function finish($win) {
			$this->o->update('_battle', array('stopped' => time(), 'win' => intval($win)), '`id` = ' . $this->id);

			$c = &$this->casters;
			$d = 0;
			if (count($c))
				foreach ($c as $caster)
					$d += $caster[2];
			$exp = $this->chars[0]->slainExp();

//			echo '[' . $d . ' damage total dealt]<br>';
//			echo '[' . $exp . ' exp for slain]<br>';
			require_once 'rush_quest.php';
			$objective = false;

			foreach ($c as $charid => $caster) {
				$char = $this->aquireCaster($charid);
				$cexp = intval($exp * ($caster[2] / $d));
				$char->gainExp($cexp);
				$p = QuestNode::onGain($char->id, $this->chars[0]->npc->guid, QObjective::QO_KILL);
				if ($charid == $this->char)
					$objective = $p;
				$this->logExp($charid, $cexp);
//				echo '[' . ->nick . ' receives ' .  . ' exp]<br>';
			}
			$this->logKill($objective);
			$this->genLoot();
		}

		function enemyRespond() {
			$c = &$this->chars[$this->char];
			$e = &$this->chars[0];

			switch ($e->npc->aiindex) {
			case 1: // ai idx#1 - animals, creeps etc
				$basemana = intval($e->pget('maxmp')); // enemy mana
				$mana = intval($e->pget('curmp')); // enemy mana
				$mana = $mana >= 0 ? $mana : 0;
//				echo "[base: $basemana, current: $mana]<br>";
				$seq = array(7, 6);
				$spellid = 0;
				foreach ($seq as $spell) {
					$s = Spell::get($spell); // [Strike]
					$cost = intval($basemana * ($s->mana / 100.0));
//					echo '[' . $s->title . '] costs ' . $cost . ' mana...<br>';
					if ($cost <= $mana) {
						$spellid = $spell;
						break;
					}
				}
				if (!$spellid) break;
				$lvl = $e->npc->level;
				$baseap = Char::levelToExp(floor($lvl / 2) + 1);
//				echo "[lvl: $lvl, baseap: $baseap]<br>";
//				die();
				$ap = $baseap * $s->dmg / 100;
				$cr = !!(rand(0, 100) < 25);
				$va = rand(70, 100) / 100;
				$ap = $va * ($cr ? 2 : 1) * $ap;
				$c->gainHealth(-$ap);
				$e->spendMana($cost ? $cost : -$basemana / 50);
				$e->save();

				$this->log(0, $c->id, $s->id, $ap, $cr);
				break;
			}
		}

		function genLoot() {
			$e = $this->aquireEnemy();
			$s = $this->o->select('_loot_template', '`source` = ' . $e->npc->guid . ' and `stype` = 1', '`guid` as `0`, `mincount` as `1`, `maxcount` as `2`, `chance` as `3`');
			if ($s && count ($r = $this->o->fetchrows($s))) {
				$chance = rand(0, 10000);
				$items = array();
				foreach ($r as $row)
					if ((100 * (float) $row[3]) >= $chance)
						$items[intval($row[0])] = (($max = intval($row[2])) > 0) ? rand(intval($row[1]), $max) : intval($row[1]);
				if (count($items))
					foreach ($items as $guid => $count)
						$this->o->insert('_corpse_loot', array('corpse' => $this->enemy, 'guid' => $guid, 'count' => $count));
			}
		}

		function getLoot() {
			if ($this->loot)
				return $this->loot;

			if (($this->state($this->char) != self::BS_FINISHED) || (!$this->win) || !$this->inBattle($this->char))
				return false;

			$this->loot = array();
			$s = $this->o->select('_corpse_loot', '`corpse` = ' . $this->enemy, '`id` as `0`, `guid` as `1`, `count` as `2`');
			if ($s && count($r = $this->o->fetchrows($s)))
				foreach ($r as $row)
					$this->loot[$id = intval($row[0])] = array(intval($row[1]), intval($row[2]));

			return $this->loot;
		}

		function renderLoot() {
			$i = '';
			$loot = $this->getLoot();
			if ($loot === false)
				echo '{$err_b3}';
			else
				if (count($loot)) {
					View::dlgButton('/battle/loot/all', 'takeall');
					require_once 'rush_item.php';
					foreach ($loot as $id => $t) {
						$item = Item::get($t[0]);
						$item->link = '/battle/loot/' . $id;
						$i .= '<div class="items">' . $item->renderCell(false, false, $t[1]) . '</div>';
					}
				} else
					$i .= '{$empty}';
			return $i;
		}

		function loot($id) {
			$l = &$this->getLoot();
			if ($id) {
				if (!($t = $l[$id])) return -2;

				$count = $t[1];
				$guid  = $t[0];

				require_once 'rush_inventory.php';
				$i = Inventory::get($this->char);
//				debug($i);
//				debug($t);

				if (!$i->put($guid, $count))
					return -1;

				require_once 'rush_quest.php';
				QuestNode::onGain($this->char, $guid, QObjective::QO_COLLECT, $count);

				$i->save();
				$this->o->delete('_corpse_loot', '`id` = ' . $id);
			} else
				foreach ($l as $id => $item)
					if (($status = $this->loot($id)) < 0)
						return $status;

			return 0;
		}

	}
?>
