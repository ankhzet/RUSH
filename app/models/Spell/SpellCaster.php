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
			$casted = (new self($caster, $spell, $target))->_cast();
			return $casted;
		}

		protected function __construct(SpellCast $spellcast, $caster, $target = null) {
			$this->caster = Caster::wrap($caster);
			$this->target = $target ? Caster::wrap($target) : null;
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
			if (($cost = $this->manaCost()) && ($cost > $this->casterMana()))
				return SpellCast::CAST_NOMANA;

			$type = $this->spellcast->spell->type;
			switch (true) {
				case $type & Spell::TYPE_HOSTILE: 
					return $this->hostileCast();
				case $type & Spell::TYPE_FRIENDLY: 
					return $this->friendlyCast();
				default: //  Spell::TYPE_NEUTRAL
				  return $this->neutralCast();
			}
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

			$this->caster->modifyMana(-$this->manaCost());
			if (!$this->target->modifyHealth(-$damage))
				$this->target->kill();

			return SpellCast::CAST_OK;
		}
		// debuff can be run only on enemy
		protected function debuffCast() {
			if (!$this->target)
				return SpellCast::CAST_NOTARGET;


		}

		protected function selfCast() {
			if (!$this->target)
				$this->target = $this->caster;
		}

		protected function healCast() {
			$this->selfCast();

			$healed = $this->calculateEffect();

			$this->caster->modifyMana(-$this->manaCost());
			$this->target->modifyHealth($healed);

			return SpellCast::CAST_OK;
		}

		protected function buffCast() {
			$this->selfCast();

		}

		// dispells, teleports, items etc.
		protected function neutralCast() {
			$this->selfCast();

		}

	}

	class Caster {
		protected $caster;

		static function wrap($target) {
			$class = ucfirst(strtolower(get_class($target))) . 'Caster';
			$caster = new {$class}();
			$caster->caster = $target;
			return $caster;
		}

		function kill() {
			$this->caster->kill();
		}

		function damageAP(boolean $melee, &$attackPower, &$critChance) {
			$ap = $melee ? RUSH::STAT_AP : RUSH::STAT_SPD;
			$cr = $melee ? RUSH::STAT_MCRIT : RUSH::STAT_SCRIT;

			$attackPower = $this->caster->statToRel($ap);
			$critChance = $this->caster->statToRel($cr);
		}


		protected currentMana() {
			return intval($this->caster->pget(DataHolder::DATA_MPCR));
		}
		protected totalMana() {
			return intval($this->caster->pget(DataHolder::DATA_MPMX));
		}
		protected currentHealth() {
			return intval($this->caster->pget(DataHolder::DATA_HPCR));
		}
		protected totalHealth() {
			return intval($this->caster->pget(DataHolder::DATA_HPMX));
		}
		protected baseMana() {
			return $this->totalMana();
		}
		protected baseHealth() {
			return $this->totalHealth();
		}

		protected modifyHealth(int $delta) {
			$health = $this->currentHealth() + $delta;
			$health = min($this->totalHealth(), max(0, $health));
			$this->caster->pset(DataHolder::DATA_HPCR, $health);

			return $health;
		}

		protected modifyMana(int $delta) {
			$mana = $this->currentMana() + $delta;
			$mana = min($this->totalMana(), max(0, $mana));
			$this->caster->pset(DataHolder::DATA_MPCR, $mana);

			return $mana;
		}
	}


	class CreatureCaster extends SpellCaster {
	
		function damageAP(boolean $melee, &$attackPower, &$critChance) {
			$ap = $melee ? RUSH::STAT_AP : RUSH::STAT_SPD;
			$cr = $melee ? RUSH::STAT_MCRIT : RUSH::STAT_SCRIT;

			$attackPower = intval($this->caster->pget($ap));
			$critChance = intval($this->caster->pget($cr));
		}

	}

	class CharCaster {
		protected baseHealth() {
			return $this->caster->statToRel(RUSH::STAT_STAM, true);
		}
		protected baseMana() {
			return $this->caster->statToRel(RUSH::STAT_INT, true);
		}
	}

