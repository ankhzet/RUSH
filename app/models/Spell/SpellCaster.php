<?php

	class SpellCaster {
		protected $caster = null;
		protected $target = null;
		protected $spellcast = null;
		protected $manaCost = null;
		protected $casterBaseMana = null;
		protected $casterMana = null;

		var $hasCriticalEffect = false;

		public static function cast(SpellCast $spellcast, $caster, $target = null) {
			return (new self($spellcast, $caster, $target))->_cast();
		}

		public function caster() {
			return $this->caster;
		}

		public function target() {
			return $this->target;
		}

		public function spellCast() {
			return $this->spellcast;
		}

		protected function __construct(SpellCast $spellcast, $caster, $target = null) {
			$this->caster = CasterWrapper::wrap($caster);
			$this->target = $target ? CasterWrapper::wrap($target) : null;
			$this->spellcast = $spellcast;
		}

		protected function aquireCasterMana() {
			$this->casterBaseMana = $this->caster->baseMana();
			$this->casterMana = $this->caster->currentMana();
		}
		protected function aquireManaCost() {
			if ($this->manaCost = $this->spellcast->spell->manacost) {
				$this->casterMana();
				$this->manaCost *= $this->casterBaseMana / 100.0;
			}
		}

		protected function casterMana() {
			if ($this->casterMana === null) 
				$this->aquireCasterMana();

			return $this->casterMana;
		}

		protected function manaCost() {
			if ($this->manaCost === null) 
				$this->aquireManaCost();

			return $this->manaCost;
		}

		protected function _cast() {
			$costsMana = $this->manaCost();
			if ($costsMana && ($costsMana > $this->casterMana()))
				return SpellCast::CAST_NOMANA;

			$type = $this->spellcast->spell->type;
			switch (true) {
				case $type & Spell::TYPE_HOSTILE: 
					$type = 'hostileCast'; break;
				case $type & Spell::TYPE_FRIENDLY: 
					$type = 'friendlyCast'; break;
				default: //  Spell::TYPE_NEUTRAL
					$type = 'neutralCast';
			}

			if ((($casted = $this->{$type}()) == SpellCast::CAST_OK) && $costsMana)
				$this->caster->modifyMana(-$costsMana);

			return $casted;
		}

		// melee hit, magic hit, DoTs, defuffs, stun etc
		protected function hostileCast() {
			$type = $this->spellcast->spell->type;
			switch (true) {
				// melee/magic hit
				case $type & Spell::TYPE_DMG_HEAL: 
					return $this->damageCast();

				// DoTs, debufs, stuns
				case $type & Spell::TYPE_AURA: 
					return $this->debuffCast();

				default:
					return SpellCast::CAST_UNKNOWN;
			}
		}

		// heal, HoT, shields, buffs
		protected function friendlyCast() {
			$type = $this->spellcast->spell->type;
			switch (true) {
				// magic heal
				case $type & Spell::TYPE_DMG_HEAL: 
					return $this->healCast();

				// HoTs, bufs, auras
				case $type & Spell::TYPE_AURA: 
					return $this->buffCast();

				default:
					return SpellCast::CAST_UNKNOWN;
			}
		}

		protected function spellEffect() {
			return intval($this->spellcast->spell->pget(Spell::DATA_EFFECT));
		}


		function calculateEffect() {
			$melee = !$this->spellcast->spell->magic_family;

			$this->caster->damageAP($melee, $attackPower, $critChance);

			$effect = intval($attackPower * ($this->spellEffect() / 100.0));
			$effect = intval($effect * rand(900, 1100) / 1000);

			if ($spellcast->hasCriticalEffect = (rand(0, 100) <= $critChance))
				$effect *= Spell::CRIT_MODIFIER;

			return $spellcast->effect = $effect;
		}



		// damage can be run only on enemy
		protected function damageCast() {
			if (!$this->target)
				return SpellCast::CAST_NOTARGET;

			$damage = $this->calculateEffect();

			if (!$this->target->modifyHealth(-$damage))
				$this->target->kill();

			return SpellCast::CAST_OK;
		}
		// debuff can be run only on enemy
		protected function debuffCast() {
			if (!$this->target)
				return SpellCast::CAST_NOTARGET;


			return SpellCast::CAST_UNKNOWN;
		}

		protected function selfCast() {
			if (!$this->target)
				$this->target = $this->caster;
		}

		protected function healCast() {
			$this->selfCast();

			$healed = $this->calculateEffect();

			$this->target->modifyHealth($healed);

			return SpellCast::CAST_OK;
		}

		protected function buffCast() {
			$this->selfCast();

			return SpellCast::CAST_UNKNOWN;
		}

		// dispells, teleports, items etc.
		protected function neutralCast() {
			$spellId = $this->spellcast->spell->id;

			$spell = null;
			if ($spellId == KnownSpells::HEARTSTONE) 
				$spell = new HeartstoneSpell;

			return $spell ? $spell->cast($this) : SpellCast::CAST_UNKNOWN;
		}

	}
