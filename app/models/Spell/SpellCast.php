<?php

	class SpellCast {
		const CAST_OK       = 0;
		const CAST_COOLDOWN = 1;
		const CAST_NOMANA   = 2;
		const CAST_NOTARGET = 3;
		const CAST_UNKNOWN  = 4;

		protected $spell = null;
		protected $cooldown = null;
		
		public $hasCriticalEffect = false;
		public $effect = 0;

		public static function binded($spell) {
			$cast = new self;
			$cast->spell = is_object($spell) ? $spell : Spell::get($spell);
			return $cast;
		}

		protected static function casterId($caster) {
			return is_a($caster, Char) ? $caster->id : -$caster->id;
		}

		public function castedBy($caster) {
			return DB::table('spell_cast')
				->where('spell_id', $this->spell->id)
				->where('caster_id', self::casterId($caster))
				->take(1)
				->get();
		}

		public function cooldown($caster) {
			if ($this->cooldown === null) {
				if ($q = $this->castedBy($caster))
					$this->cooldown = $this->spell->cooldown - ($q->casted_at - time()) / 1000;
				else
					$this->cooldown = 0;
			}

			return $this->cooldown;
		}

		public function castBy($caster, $target = null) {
			if ($this->cooldown($caster) > 0)
				return self::CAST_COOLDOWN;

			if (!($error = SpellCaster::cast($caster, $this->spell, $target)))
				return $error;

			$q = $this->castedBy($caster);
			if ($q)
				$q->casted_at = time();
			else
				DB::table('spell_cast')->insert(['caster_id' => self::casterId($caster), 'spell_id' => $this->spell->id, 'casted_at' => time()]);

			return self::CAST_OK;
		}

	}