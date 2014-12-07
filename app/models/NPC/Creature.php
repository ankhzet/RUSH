<?php

	class Creature extends DataHolder implements Killable {
		protected $table = 'creatures';

		const SPAWN_ALIVE  = 1;
		const SPAWN_CORPSE = 2;
		const SPAWN_PENDING_RESURRECT = 3;

		const CORPSE_ALIVE = 30;
		const CORPSE_RESPAWN = 60;

		const DATA_LEVEL = 10;
		const SLAIN_EXPDENOM = 0.05; // 20 mobs to level =)

		public function npc() {
			return $this->belongsTo('NPC', 'creature_template_id');
		}

		public function location() {
			return $this->belongsTo('Location');
		}

		static function loadSpawn($id) {
			$creature = self::find($id);

			if ($creature) {
				$creature->npc->spawns[$id] = $creature;
				return $creature;
			}
			return null;
		}

		static function spawn($guid, $loc, $x, $y) {
			$template = NPC::find($guid);
			$hp = $template->basehp;
			$mp = $template->basemp;
			$d = array_fill(0, $this->attribs, 0);
			$d[self::DATA_HPMX] = $hp;
			$d[self::DATA_HPCR] = $hp;
			$d[self::DATA_MPMX] = $mp;
			$d[self::DATA_MPCR] = $mp;

			$spawn = new self;
			$spawn->creature_template_id = $guid;
			$spawn->location_id = $loc;
			$spawn->x = $x;
			$spawn->y = $y;
			$spawn->data = $d;
			$spawn->save();

			return $spawn->id;
		}

		function hit($amount) {
			$health = intval($this->pget('curhp')) - $amount;
			if ($health < 0) $health = 0;
			$this->pset('curhp', $health);
			if ($health <= 0) {
				DB::table('spawns')
					->where('spawned_id', $this->id)
					->update(['state' => self::SPAWN_CORPSE, 'time' => time()]);
			}
			return $health;
		}

		function spendMana($amount) {
			$mana = intval($this->pget('curmp')) - $amount;
			$base = $this->npc->basemp;
			$this->pset('curmp', $mana < 0 ? 0 : ($mana > $base ? $base : $mana));
			return $mana;
		}

		function slainExp() {
			return ceil(Char::levelToExp($this->npc->level) * self::SLAIN_EXPDENOM);
		}

	}
