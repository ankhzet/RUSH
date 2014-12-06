<?php

	class BagRenderer {

		function render() {
			$t = '<div class="bag">';
			RUSH::$keys[loc]->handler = '<p><a href="/inventory/remove/' . $this->id . '">{$remove}</a></p>';
			$t .= $this->item->tpl->renderCell(false);
			$s = $this->slots;
			$w = ceil(sqrt($s));
			$z = $w * $w;
			$k = 0;
			while ($k < $z) {
				$t.= '<div class="items">';
				for ($i = 0; $i < $w; $i++) {
					$id = intval($this->slot[$k]);
					if ($id) {
						$item = $this->items[$id];
						$item->tpl->link = '/inventory/item/' . $this->id . '/' . $k;
						$t .= $item->tpl->renderCell(false, true, $this->count[$id]);
						$item->tpl->link = '';
					} else
						$t .= '<div class="item"></div>';
					$k++;
				}
				$t.= '</div>';
				if ($k >= $s) break;
			}
			$t.= '</div>';
			return $t;
		}

	}

