<?php

	class InventoryRenderer {

		function render() {
			if (count($this->bags)) {
				$t = '<h4>{$inventory}:</h4>';
				$a = array();
				foreach ($this->bags as $bag)
					$a[] = $bag->render();
					$t .= join('<hr />', $a);
			} else {
				$this->equipBag(2);
				RUSH::redirect('inventory');
			}

			return $t;
		}


	}