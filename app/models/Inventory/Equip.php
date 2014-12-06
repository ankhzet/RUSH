<?php

	class Equip {
		static $cache = array();
		var $items = array();

		private function __construct ($charId) {

			$this->char = $charId;
			$items = DB::table('char_equip')
				->where('char_id', intval($charId))
				->where('slot', '<>', RUSH::SLOT_BAG)
				->orderBy('slot')
				->get();

			if ($items)
				foreach ($items as $item)
					$this->items[$item->item_id] = Item::find($item->item_id);
		}

		static function get($charid) {
			if (!isset(self::$cache[$charid]))
				self::$cache[$charid] = new self($charid);

			return self::$cache[$charid];
		}

		function equip(Item $item, $update = true) {
			$type = $item->template->type;

			if (count($this->items))
				foreach ($this->items as $id => &$i)
					if ($type == $i->template->type)
						if (($r = $this->unequip($id)) != 1) {
							echo '[' . $r . ']';
							return -1;
						} else
							break;

			Equip::equipItem($this->char, $item->id, $type);
			Bag::extract($item->id);

			$this->items[$item->id] = $item;

			if ($update)
				Char::updatedEquip($this->char);
			return 1;
		}

		function unequip($itemid) {
			$item = $this->items[$itemid];
			if (!isset($item))
				return -1;

			$i = Inventory::get($this->char);

//			debug($i);
			if (!$i->put($item, 1))
				return -2;

			$i->save();

			unset($this->items[$itemid]);

			Equip::unequipItem($itemid);
			Char::updatedEquip($this->char);
			return 1;
		}

		public static function equipItem($charId, $itemId, $slot = RUSH::SLOT_BAG) {
			return DB::table('char_equip')->insert(['char_id' => $charId, 'slot' => $slot, 'item_id' => $itemId]);
		}

		public static function unequipItem($itemId) {
			return DB::table('char_equip')->where('item_id', $itemId)->take(1)->delete();
		}

		public static function flush($charId) {
			return DB::table('char_equip')->where('char_id', $charId)->delete();
		}
	}
