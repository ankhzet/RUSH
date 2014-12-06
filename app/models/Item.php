<?php

	require_once 'rush.php';

	class ItemTemplate extends DataHolder {
		static $cache = array();
		var $table = 'item_template';

		var $relocs = array('slots' => 1, 'bind' => 2, 'effect1' => 3, 'effect2' => 4, 'effect3' => 5);

		static function get($guid) {
			if (!isset(self::$cache[$guid]))
				self::$cache[$guid] = self::find($guid);

			return self::$cache[$guid];
		}
	}

	class Item extends DataHolder {
		public $timestamps = false;
		protected $table = 'items';

		static $cache = array();

		var $relocs = array('binded' => 2, 'effect1' => 3, 'effect2' => 4, 'effect3' => 5);
		var $bag = null;

		public function template() {
			return $this->belongsTo('ItemTemplate');
		}

		static function get($guid) {
			if (!isset(self::$cache[$guid]))
				self::$cache[$guid] = self::find($guid);

			return self::$cache[$guid];
		}

		static function getGUID($id) {
			return intval(DB::table('items')->select('template_id')->find($id)->template_id);
		}

		static function spawn($guid) {
			$item = new Item;
			$item->template_id = $guid;
			$item->save();
			return $item;
		}

		static function fetchEffects($guid) {
			$a = array();

			if ($q = DB::table('item_stats')->where('item_template_id', $guid)->get())
				foreach ($q as $stat)
					$a[$stat->type] = $stat->value;

			return $a;
		}

	}

