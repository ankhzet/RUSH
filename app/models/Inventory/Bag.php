<?php

	/*
		Bag template (GUID)
		->Bag item (ID)
			->Bag items (ID->id)

		resolve guid to know type of bag
		fetch items by bag id
	*/
	class Bag {
		static $cache = array();
		var $item  = null;
		var $items = array();
		var $slot  = array();
		var $count = array();

		private function __construct ($id) {
			$this->id   = $id;
			$this->item = Item::get($id);
			$this->guid = $this->item->template_id;
			$this->slots= intval($this->item->template->pget('slots'));
			$this->slot = array_fill(0, $this->slots, null);

			$this->fetchItems();
		}

		static function get($itemid) {
			if (!isset(self::$cache[$itemid]))
				self::$cache[$itemid] = new self($itemid);

			return self::$cache[$itemid];
		}

		function fetchItems() {
			$q = DB::table('bag_items')->where('bag_id', $this->id)->get();
			if ($q)
				foreach ($q as $item) {
					$id   = intval($item->item_id);
					$this->items[$id] = Item::get($id);
					// $item->items[$id]->bag = $this;
					$this->count[$id] = intval($item->count);
					$this->slot[$item->slot] = $id;
				}
		}

		function put($item, $count) {
			$instance = is_object($item) ? $item : null;
			$template_id = $instance ? $item->template_id : intval($item);

			$s = $this->slots;
			$slot = 0;
			while ($slot < $s) { // going through slots
				if ($id = $this->slot[$slot]) { // slot is busy
					$item = $this->items[$id];
					if ($item->template_id == $template_id)
						if ($this->count[$id] + $count <= $item->template->stack) {
							$this->count[$id] += $count;

							if ($instance)
								$instance->delete();

							return true;
						}
				} else {
					if (!$instance) $instance = Item::spawn($template_id);

					$stack = $instance->template->stack;
					$tip = $count;
					if ($tip > $stack)
						$tip = $stack;
					$count -= $tip;

					$this->slot[$slot] = $id = $instance->id;
					$this->items[$id] = $instance;
					$this->count[$id] = $tip;

					if ($count <= 0)
						return true;
					else {
						$instance = null;
						$slot = 0;
						continue;
					}
				}
				$slot++;
			}
			return false;
		}

		function pick($slot) {
			$id = $this->slot[$slot];
			if ($id) {
				$this->items[$id]->bag = $this;
				return $this->items[$id];
			}

			return null;
		}

		public static function extract($itemId) {
			return DB::table('bag_items')->where('item_id', $itemId)->take(1)->delete();
		}

		public static function flush($bagIdsOrId) {
			return DB::table('bag_items')->whereIn('bag_id', is_array($bagIdsOrId) ? $bagIdsOrId : array($bagIdsOrId))->delete();
		}

		function throwaway($itemid) {
			if (!(isset($this->items[$itemid]) && $this->items[$itemid]))
				return -1;

			unset($this->items[$itemid]);

			Bag::extract($itemid);
			Item::destroy($itemid);
			return 1;
		}

		function save() {
			if (count($this->items)) {
				$has = array();
				$upd = array();
				$slt = array();

				$table = DB::table('bag_items');

				if ($q = $table->where('bag_id', $this->id)->get())
					foreach ($q as $i)
						$has[$i->item_id] = $i->count;

				$new = $has;

				foreach ($this->items as $id => $i)
					if (!($count = $this->count[$id]))
						unset($new[$id]);
					else {
						if (($h = @$has[$id]) && ($h != $count))
							$upd[$id] = $count;

						$new[$id] = $count;
						foreach ($this->slot as $slot => $inSlotId)
							if ($id == $inSlotId) {
								$slt[$id] = $slot;
								break;
							}

					}

				$h = array_keys($has);
				$n = array_keys($new);

				$delete = array_diff($h, $n);
				$insert = array_diff($n, $h);

				if ($delete) $table->where('bag_id', $this->id)->whereIn('item_id', $delete)->delete();
				if ($insert) {
					$i = [];
					foreach ($insert as $id)
						$i[] = ['bag_id' => $this->id, 'item_id' => $id, 'count' => $new[$id], 'slot' => $slt[$id]];
					$table->insert($i);
				}
				if ($upd) 
					foreach ($upd as $id => $count)
						$table->where('item_id', $id)->update(['count' => $count]);
				
//				var_dump($this->items); echo '<br>';
//				var_dump($delete); echo '<br>';
//				var_dump($insert); echo '<br>';
//				var_dump($upd); echo '<br>';

			} else
				Bag::flush($this->id);
		}
	}

