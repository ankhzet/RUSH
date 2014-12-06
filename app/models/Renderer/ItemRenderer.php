<?php

	class ItemRenderer {

		static function fetchDetails(Item $item) {
			$guid = $item->guid;
			if ($item->bind > 1)
				$t .= '<p class="detail">{$bind{$item:bind}}</p>';

			if ($item->type)
				$t  = '<p class="detail">{$type}: <span>{$itype' . $item->type . '}</span></p>';
			switch ($item->type) {
			case RUSH::SLOT_BAG:
				$t .= '<p class="detail">{$slots}: <span>' . $item->pget('slots') . '</span></p>';
			}

			if ($item->stack > 1)
				$t .= '<p class="detail">{$stacksize}:<span>{$item:stack}</span></p>';

			$s = self::fetchEffects($guid);
			if (count($s)) {
				$e = array();
				foreach ($s as $stat => $value)
					switch ($stat) {
					case RUSH::STAT_STAM :
					case RUSH::STAT_INT  :
					case RUSH::STAT_STR  :
					case RUSH::STAT_AGI  :
					case RUSH::STAT_SPD  :
					case RUSH::STAT_AP   :
					case RUSH::STAT_MCRIT:
					case RUSH::STAT_SCRIT:
						$t .= '<p class="stat">{$loc:stat' . $stat . '}: <span>' . ($value > 0 ? '+' : '') . $value . '</span></p>';
						break;
					default:
						$e[$stat] = $value;
					}

				if (count($e)) {
					$o = msqlDB::o();
					$s = $o->select('_spell_effect', '`id` in (' . join(',', $e) . ')', '`id` as `0`, `desc` as `1`, `cd` as `2`');
					$f = $o->fetchrows($s);
					$u = array();
					if (count($f))
						foreach ($f as $effect)
							$u[intval($effect[0])] = array($effect[1], $effect [2]);

					foreach ($e as $stat => $value)
						$t .= '<p class="effect">{$loc:stat' . $stat . '}: <span>'
						. (($eff = $u[$value][0]) ? $eff : '{$unknowneffect}')
						. (($cd = $u[$value][1]) ? ' ({$restoring} ' . maketime($cd) . ')' : '')
						. '</span></p>';
				}
			}
			return $t;
		}

		static function itemCell($guid, $detail = false, $item = null, $cellonly = false, $count = 0) {
			if (!$item) $item = Item::get($guid);
			$t ='';

			$t .= '<div class="item"><a' . ($item->link ? ' href="' . $item->link . '"' : '') . '><img src="/theme/img/icons/ui/{$item:icon}.png">';
			if ($count > 1)
				$t .= '<span>' . $count . '</span>';
			$t .= '</a></div>';
			if (!$cellonly) {
				$t .= '<div class="desc">';
				$t .= '<h4 class="rare{$item:rare}">{$item:title}</h4><h5>';
				if (!$detail)
					$t .= '<p>&nbsp;<a style="font-size: 50%;" href="/items/guid/{$item:guid}">{$loc:detail}</a></p>';
				else
					$t .= self::fetchDetails($item);
				$t .= '{$item:descr}{$handler}</h5></div>';
			}

			RUSH::addKey('item', $item);
			return RUSH::process($t);
		}

		function renderCell($detail = false, $cellonly = false, $count = 0) {
			return self::itemCell($this->guid, $detail, $this, $cellonly, $count);
		}
	
	}