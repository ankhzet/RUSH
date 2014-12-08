<?php

	class DataHolder extends Illuminate\Database\Eloquent\Model  {
		var $attr_updated = false;
		var $_data = null;

		var $attribs = 10;

		const DATA_HPMX = 1;
		const DATA_MPMX = 2;
		const DATA_HPCR = 3;
		const DATA_MPCR = 4;

		public function getDataAttribute($data) {		
			if ($this->_data)
				return $this->_data;

			return $this->_data = ($data ? explode(' ', $data) : array_fill(0, $this->attribs, 0));
		}

		public function setDataAttribute($value) {
			$this->attributes['data'] = join(' ', $this->_data = $value);
		}

		public function getAttrUpdated() {
			return $this->attr_updated;
		}

		function pset($attribute, $value) {
			if ($this->data[$attribute] != $value) {
				$this->_data[$attribute] = $value;
			  $this->attr_updated = true;
			 }

			return $this;
		}

		function pget($attribute) {
			return $this->data[$attribute];
		}

		public function save(array $options = array()) {
			if ($this->attr_updated)
				$this->data = $this->_data;

			parent::save($options);
		}

	}
