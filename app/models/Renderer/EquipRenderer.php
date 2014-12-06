<?php

	class EquipRenderer {

		function render($detail = false) {
			if (count($this->items)) {
				$t = '<h4>{$equipment}:</h4>';
				$a = array();
				foreach ($this->items as &$item) {
					RUSH::$keys[loc]->handler = '<p><a href="/inventory/remove/' . $item->id . '">{$remove}</a></p>';
					$a[] = $item->tpl->renderCell($detail);
				}
					$t .= join('<br />', $a);

				$t .= '<hr /><a href="/inventory?detail=true">{$detail}</a>';
			} else
				return '{$equipempty}';

			return $t;
		}


	}