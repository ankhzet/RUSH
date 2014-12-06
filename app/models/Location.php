<?php

	class Location extends Eloquent {
		var $table = 'locations';

		static $cache = array();

		static function get($id = null) {
			if (!$id) $id = intval(RUSH::oGet('location'));

			if (!isset(self::$cache[$id]))
				self::$cache[$id] = self::find($id);

			return self::$cache[$id];
		}

		public function chars() {
			return $this->hasMany('Char');
		}

		public function creatures() {
			return $this->hasMany('Creature');
		}

		public function cementaries() {
			return $this->belongsToMany('Location', 'locations_cementary', 'location_id', 'cementary_id');
		}

		public function makeClickable($sub = null, $title = null) {
			if ($sub) $sub = "$sub/";
			return '<a href="' . URL::to(strtolower(get_class($this)) . "/$sub" . ($this->title)) . '">' . ($title ?: $this->title) . '</a>';
		}

	}
