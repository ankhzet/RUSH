<?php

	class KnownSpells {

		const HEARTSTONE = 13;

		public function cast(SpellCaster $spellcaster) {
			
		}
	}

	class HeartstoneSpell extends KnownSpells {
		const ERR_OK            =  0;
		const ERR_ON_CINEMATICS = 10;
		const ERR_AT_HOME       = 20;

		public function cast(SpellCaster $spellcaster) {
			$char = $spellcaster->caster()->target();
			if (!is_a($char, 'Char'))
				return SpellCast::CAST_WRONGTARGET;

			if ($error = self::notCastableOn($char))
				return $error;

			// var_dump([$char->location->title, $char->home->title, $spellcaster->spellCast()->cooldown()]);
			CharHelper::teleport($char, $char->home);

			return SpellCast::CAST_OK;
		}


		public static function notCastableOn(Char $char) {
			if ($char->cinematics_id)
				return self::ERR_ON_CINEMATICS;

			return ($char->home_id != $char->location_id) ? self::ERR_OK : self::ERR_AT_HOME;
		}
	}

