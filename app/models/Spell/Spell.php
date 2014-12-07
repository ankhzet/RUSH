<?php

	class Spell extends DataHolder {
		protected $table = 'spells';

		protected static $cache = [];

		const ID_HEARTSTONE = 13;

//	const TYPE_NEUTRAL  = 0x000000;
		const TYPE_HOSTILE  = 0x000001;
		const TYPE_FRIENDLY = 0x000002;

		// hostile dmg: melee/magic hit; friendly heal: magic heal
		const TYPE_DMG_HEAL = 0x000010;

		// hostile aura: fear, stun, DoT, -stats; friendly aura: immunity, +stats, HoT
		const TYPE_AURA     = 0x000100;

		const CRIT_MODIFIER = 2.0;

		var $attribs = 5;

		const DATA_AURA    = 0;
		const DATA_EFFECT  = 1;
		const DATA_PHYSICAL= 2;

		static function get($id) {
			if (!isset(self::$cache[$id = intval($id)]))
				self::$cache[$id] = self::find($id);

			return self::$cache[$id];
		}
	}

