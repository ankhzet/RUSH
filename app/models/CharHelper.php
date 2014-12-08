<?php

	class CharHelper {
		static function teleport(Char $char, Location $location, $cinematics = false) {
			$cinematics ? $char->cinematics_id = $location->id : $char->location_id = $location->id;
			$char->save();
		}

		static function create($userid, $data) {
			$char = new Char(['name' => $data['name']]);
			$char->user_id = $userid;
			$char->save();

			$char->fraction = $frac = intval($data['frac']);
			$char->race = $race = intval($data['race']);
			$char->spec = $spec = intval($data['spec']);


			$location_id = self::startLocation($frac, $race);

			$char->location_id = $location_id;
			$char->home_id = $location_id;
			$char->cinematics_id = $location_id;

			$char->level = 0;
			$char->levelUp();
			$char->exp = 0;
			$char->gold = 100;

			$char->save();

			if ($char->id) {
				self::gainEquipment($char);
				self::gainSpells($char);
				Char::updatedEquip($char->id, true);
			}
			
			return $char;
		}

		static function startLocation($frac, $race) {
			$q = DB::table('start_locations')->select('loc')->where('race', $race)->where('frac', $frac)->first();
			return $q ? intval($q->loc) : 0;
		}

		static function gainSpells($char) {
			$q = DB::table('init_spells')
				->select('spell_id')
				->where('race', $char->race)
				->orWhere('spec', $char->spec)
				->orWhere(function ($query) {
					$query->where('race', 0)->where('spec', 0);
				})->get();

			if ($q) {
				$spells = [];
				foreach ($q as $entry)
					$spells[] = $entry->spell_id;

				self::gainSpell($char->id, $spells); 
			}
		}

		static function gainSpell($charId, $spellId) {
			if (is_array($spellId)) {
				$data = [];
				foreach ($spellId as $id)
					$data[] = ['char_id' => $charId, 'spell_id' => intval($id)];
			} else
				$data = ['char_id' => $charId, 'spell_id' => intval($spellId)];

			DB::table('char_spells')->insert($data);
		}

		static function gainEquipment($char) {
			$q = DB::table('init_equipment')
				->select('item_id', 'count', 'equip')
				->where('fraction', $char->fraction)
				->where('race', $char->race)
				->orWhere('spec', $char->spec)
				->orWhere(function ($query) {
					$query->where('fraction', 0)->where('race', 0)->where('spec', 0);
				})
				->orderBy('order')
				->get();

			if ($q) {
				$equip = Equip::get($char->id);
				$inven = Inventory::get($char->id);

				foreach ($q as $entry) {
					$item = Item::spawn($entry->item_id);

					if ($entry->equip) {
						if ($item->template->type == RUSH::SLOT_BAG)
							$inven->equipBag(Bag::get($item->id));
						else
							$equip->equip($item, false);
					} else
						$inven->put($item, $entry->count);
				}

				$inven->save();
			}
		}

		static function delete($charId) {
			// deletion:
			// items in bags
			// equip
			// spells
			// main record

			$d = array();
			$b = array();
			$e = array();
			$i = Inventory::get($charId);
			if (count($i->bags))
				foreach ($i->bags as $bag) {
					$b[] = $bag->id;
					if (count($bag->items))
						foreach ($bag->items as $id => $item)
							$d[] = $id;
				}

			$q = Equip::get($charId);
			if (count($q->items))
				foreach ($q->items as $id => $item)
					$e[] = $id;

			$equip = array_merge($e, $b);
			$all = array_merge($equip, $d);
			// var_dump(['equip'=>$e, 'items in bags'=>$d, 'bags'=>$b, 'all'=>$all]);

			Equip::flush($charId);
			if ($b) Bag::flush($b);
			if ($all) Item::destroy($all);

			DB::table('char_spells')->where('char_id', $charId)->delete();
			Char::destroy($charId);

			/*

			$o->delete('_battle_caster', '`caster` = ' . $char);
			$o->delete('_objective_progress', '`char` = ' . $char);
			$o->delete('_quest_state', '`char` = ' . $char);

				*/
		}

	}
