<?php

	class CasterWrapper {
		protected $target;

		static function wrap($target) {
			$class = ucfirst(strtolower(get_class($target))) . 'Caster';
			$wrapper = new $class();
			$wrapper->target = $target;
			return $wrapper;
		}

		function target() {
			return $this->target;
		}

		function kill() {
			$this->target->kill();
		}

		function damageAP(boolean $melee, &$attackPower, &$critChance) {
			$ap = $melee ? RUSH::STAT_AP : RUSH::STAT_SPD;
			$cr = $melee ? RUSH::STAT_MCRIT : RUSH::STAT_SCRIT;

			$attackPower = $this->target->statToRel($ap);
			$critChance = $this->target->statToRel($cr);
		}


		protected function currentMana() {
			return intval($this->target->pget(DataHolder::DATA_MPCR));
		}
		protected function totalMana() {
			return intval($this->target->pget(DataHolder::DATA_MPMX));
		}
		protected function currentHealth() {
			return intval($this->target->pget(DataHolder::DATA_HPCR));
		}
		protected function totalHealth() {
			return intval($this->target->pget(DataHolder::DATA_HPMX));
		}
		protected function baseMana() {
			return $this->totalMana();
		}
		protected function baseHealth() {
			return $this->totalHealth();
		}

		protected function modifyHealth(int $delta) {
			$health = $this->currentHealth() + $delta;
			$health = min($this->totalHealth(), max(0, $health));
			$this->target->pset(DataHolder::DATA_HPCR, $health);

			return $health;
		}

		protected function modifyMana(int $delta) {
			$mana = $this->currentMana() + $delta;
			$mana = min($this->totalMana(), max(0, $mana));
			$this->target->pset(DataHolder::DATA_MPCR, $mana);

			return $mana;
		}
	}


	class CreatureCaster extends CasterWrapper {
	
		function damageAP(boolean $melee, &$attackPower, &$critChance) {
			$ap = $melee ? RUSH::STAT_AP : RUSH::STAT_SPD;
			$cr = $melee ? RUSH::STAT_MCRIT : RUSH::STAT_SCRIT;

			$attackPower = intval($this->target->pget($ap));
			$critChance = intval($this->target->pget($cr));
		}

	}

	class CharCaster extends CasterWrapper{
		protected function baseHealth() {
			return $this->target->statToRel(RUSH::STAT_STAM, true);
		}
		protected function baseMana() {
			return $this->target->statToRel(RUSH::STAT_INT, true);
		}
	}