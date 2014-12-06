<?php

	require_once dirname(__FILE__) . '/rush_helper.php';

	class RUSH {
		const FRAC_ALLIANCE = 1;
		const FRAC_HORDE    = 2;

		const RACE_HUMAN  = 1;
		const RACE_GNOME  = 2;
		const RACE_NELF   = 3;
		const RACE_ORC    = 4;
		const RACE_THROLL = 5;
		const RACE_BELF   = 6;

		const SPEC_WARRIOR= 1;
		const SPEC_MAGE   = 2;

		const STAT_STAM  = 1;
		const STAT_INT   = 2;
		const STAT_STR   = 3;
		const STAT_AGI   = 4;
		const STAT_AP    = 5;
		const STAT_MCRIT = 6;
		const STAT_SPD   = 7;
		const STAT_SCRIT = 8;

		const STAT_ONEQ  = 10;
		const STAT_ONUSE = 11;
		const STAT_ONHIT = 12;

		const SLOT_ITEM  = 0;
		const SLOT_BAG   = 1;

		const REL_HOSTILE  = -1;
		const REL_NEUTRAL  = 0;
		const REL_FRIENDLY = 1;

		static function fracRaces() {
			return array(
				self::FRAC_ALLIANCE => array(self::RACE_HUMAN, self::RACE_GNOME, self::RACE_NELF)
			, self::FRAC_HORDE    => array(self::RACE_ORC, self::RACE_THROLL, self::RACE_BELF)
			);
		}

		static function fracs() {
			return array(
				self::FRAC_ALLIANCE => '{$alliance}'
			, self::FRAC_HORDE    => '{$horde}'
			);
		}

		static function races() {
			return array(
				self::RACE_HUMAN  => '{$human}'
			, self::RACE_GNOME  => '{$gnome}'
			, self::RACE_NELF   => '{$nightelf}'
			, self::RACE_ORC    => '{$orc}'
			, self::RACE_THROLL => '{$throll}'
			, self::RACE_BELF   => '{$bloodelf}'
			);
		}

		static function specs() {
			return array(
				self::SPEC_WARRIOR => '{$warrior}'
			, self::SPEC_MAGE    => '{$mage}'
			);
		}

		static $opts = array();
		const RUSH_SESSION_DATA_KEY = 'o';

		static function oSet($opt, $value) {
			self::$opts[$opt] = $value;
		}

		static function oGet($opt) {
			return isset(self::$opts[$opt]) ? self::$opts[$opt] : null;
		}

		static function load() {
			self::$opts = unserialize(Session::get(self::RUSH_SESSION_DATA_KEY));
		}

		static function save() {
			$save = array();
			foreach (self::$opts as $opt => $value)
				if ($value)
					$save[$opt] = $value;

			Session::put(self::RUSH_SESSION_DATA_KEY, serialize($save));
		}

		static function fraction($id) {
			$s = DB::table('fractions');
			return ($id = intval($id)) ? $s->where('id', $id)->first() : $s->get();
		}

		static function fracRelation($f1, $f2) {
			if ($f1 == $f2) return self::REL_FRIENDLY;

			$join = intval($f1) . ',' . intval($f2);
			$s = DB::table('fractions_relation')
				->select('relation')
				->whereRaw("`f1` in ($join) and `f2` in ($join)")
				->first();
			return $s ? $s->relation : self::REL_NEUTRAL;
		}

	}

	RUSH::load();