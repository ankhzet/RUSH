<?php

	class Inventory {
		static $cache = array();
		var $bags = array();

		private function __construct ($charId) {
			$this->char = $charId;
			$items = DB::table('char_equip')
				->where('char_id', intval($charId))
				->where('slot', RUSH::SLOT_BAG)
				->get();

			if ($items)
				foreach ($items as $item) {
					var_dump($item);
					$this->bags[$item->item_id] = Bag::get($item->item_id);
				}
		}

		static function get($charid) {
			if (!isset(self::$cache[$charid]))
				self::$cache[$charid] = new self($charid);

			return self::$cache[$charid];
		}

		function save() {
			foreach ($this->bags as $bag)
				$bag->save();
		}

		function put($item, $count) {
			foreach ($this->bags as $bag)
				if ($bag->put($item, $count))
					return true;

			return false;
		}
		function pick($bagid, $slot) {
			if ($bag = $this->bags[$bagid])
				return $bag->pick($slot);

			return null;
		}

		function throwaway($id) {
			foreach ($this->bags as $bag)
				if ($bag->throwaway($id) == 1)
					return 1;

			return -1;
		}

		function equipBag($bag) {
			$isInstance = is_object($bag);

			if (!$isInstance)
				$bag = Item::spawn(intval($bag));

			$id = Equip::equipItem($this->char, $bag->id);

			if ($isInstance) // created previously, so it contains in some bag already
				Bag::extract($bag->id);

			$this->bags[$bag->id] = $bag;
			return $id;
		}

		function unequipBag($bagId) {
			$bag = $this->bags[$bagId];

			if (!isset($bag))
				return -1;

			if (count($bag->items))
				return -2;

			unset($this->bags[$bagId]);

			if (!$this->put($bag, 1)) {
				$this->bags[$bagId] = $bag;
				return -3;
			}

			$this->save();

			Equip::unequipItem($bagId);
			return 1;
		}
	}
