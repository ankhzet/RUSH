<?php

	class SpellCast {
		const CAST_OK       = 0;
		const CAST_COOLDOWN = 1;
		const CAST_NOMANA   = 2;
		const CAST_NOTARGET = 3;
		const CAST_UNKNOWN  = 4;

		protected $cooldown = null;

		public $spell = null;
		public $hasCriticalEffect = false;
		public $effect = 0;

		public static function bind($spell) {
			$cast = new self;
			$cast->spell = is_object($spell) ? $spell : Spell::get($spell);
			return $cast;
		}

		protected static function casterId($caster) {
			return is_a($caster, 'Char') ? $caster->id : -$caster->id;
		}

		protected function castedBy($caster) {
			return DB::table('spell_cast')
				->where('spell_id', $this->spell->id)
				->where('caster_id', self::casterId($caster))
				->take(1);
		}

		public function cooldown($caster = null) {
			if ($this->cooldown === null && $caster) {
				if ($q = (($r = $this->castedBy($caster)->get()) ? $r[0] : null))
					$this->cooldown = $this->spell->cooldown - (time() - $q->casted_at);
				else
					$this->cooldown = 0;
			}

			return $this->cooldown;
		}

		public function castBy($caster, $target = null) {
			if ($this->cooldown($caster) > 0)
				return self::CAST_COOLDOWN;

			if (self::CAST_OK != ($error = SpellCaster::cast($this, $caster, $target)))
				return $error;

			$q = $this->castedBy($caster);
			if ($q->get()) {
				$q->update(['casted_at' => time()]);
			} else
				DB::table('spell_cast')->insert(['caster_id' => self::casterId($caster), 'spell_id' => $this->spell->id, 'casted_at' => time()]);

			return self::CAST_OK;
		}

	}