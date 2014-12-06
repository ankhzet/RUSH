<?php

	class DataHolder extends Illuminate\Database\Eloquent\Model  {
		var $attr_updated = false;
		var $_data = null;

		var $attribs = 10;

		const DATA_HPMX = 1;
		const DATA_MPMX = 2;
		const DATA_HPCR = 3;
		const DATA_MPCR = 4;

		var $relocs = array(
				'maxhp' => self::DATA_HPMX
			, 'maxmp' => self::DATA_MPMX
			, 'curhp' => self::DATA_HPCR
			, 'curmp' => self::DATA_MPCR
			);

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
			$attribute = $this->relocs[$attribute];
			
			if ($this->data[$attribute] != $value) {
				$this->_data[$attribute] = $value;
			  $this->attr_updated = true;
			 }

			return $this;
		}

		function pget($attribute) {
			$attribute = $this->relocs[$attribute];
			return $this->data[$attribute];
		}

		public function save(array $options = array()) {
			if ($this->attr_updated) {
				$this->data = $this->_data;
			// var_dump($this);
			// die();
		}

			parent::save($options);
		}

	}
