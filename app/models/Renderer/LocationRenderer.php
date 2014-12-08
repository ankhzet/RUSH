<?php

	class BasicLocationRenderer {
		const CELL_SIZE = 16;
		const FRAME_MARGIN = 4;

		static function render($location, $detailed = true) {
			$t = "<h4>{$location->title}</h4>\n<p>{$location->description}</p>\n";

			if ($detailed) {
				$ww = $location->w * self::CELL_SIZE + self::FRAME_MARGIN;
				$hh = $location->h * self::CELL_SIZE + self::FRAME_MARGIN;
				$rand = rand();
				$t .= '<hr />';
				$m  = '<img class="location" src="/location/render?r' . $rand . '" usemap="#locmap" style="width: ' . $ww . 'px; height: ' . $hh . 'px;" />';
			} else
				return $t;


			if ($c = $location->creatures) {
				$char = Char::get();
				$frac = intval($char->fraction);
				$a = array(-1 => '{$hostile}', 0 => '{$neutral}', 1 => '{$friendly}');
				$l  = '<h4>Creatures:</h4>';
				$m .= '<map name="locmap">' . PHP_EOL;
				$s2 = intval(self::CELL_SIZE / 2) + self::FRAME_MARGIN / 2;
				foreach ($c as $spawn) {
					$id = $spawn->id;
					$r = RUSH::fracRelation($spawn->npc->fraction, $frac);
					$h = intval($spawn->pget(Creature::DATA_HPCR));
					$n = $spawn->npc->name;
					$o = array($spawn->x * self::CELL_SIZE + $s2, $spawn->y * self::CELL_SIZE + $s2, $s2);
					$m .= '  <area shape="circle" coords="' . join(',', $o) . '" href="/npc/' . $id . '" alt="' . htmlspecialchars($n) . '" title="' . htmlspecialchars($n) . '" />' . PHP_EOL;
					$l .= '<p>' . ($h <= 0 ? '[ x ]' : '[ <a>?</a> ]') . ' <a href="/npc/' . $id . '">' . $n . '</a><span style="float: right;">[' . $a[$r] . ']</span></p>';
				}
				$m .= '</map>' . PHP_EOL;
				$t  = $t . $l;
			}
			return $t . $m;
		}

		static function renderCreature($creature) {
			$s2 = intval(self::CELL_SIZE / 2) + 1;
			$id = $creature->id;
			$r = RUSH::fracRelation($creature->npc->fraction, $frac);
			$h = intval($creature->pget(Creature::DATA_HPCR));
			$n = $creature->npc->name;
			$o = array($creature->x * self::CELL_SIZE + $s2, $creature->y * self::CELL_SIZE + $s2, $s2);
			$m .= '  <area shape="circle" coords="' . join(',', $o) . '" href="/npc/' . $id . '" alt="' . htmlspecialchars($n) . '" title="' . htmlspecialchars($n) . '" />' . PHP_EOL;
			$l .= '<p>' . ($h <= 0 ? '[ x ]' : '[ <a>?</a> ]') . ' <a href="/npc/' . $id . '">' . $n . '</a><span style="float: right;">[' . $a[$r] . ']</span></p>';
		}

		static function renderImage($location) {
			$w = $location->w * self::CELL_SIZE + self::FRAME_MARGIN;
			$h = $location->h * self::CELL_SIZE + self::FRAME_MARGIN;
			$img = ImageCreateTrueColor($w, $h);
			$font  = dirname(dirname(__FILE__)) . '/img/friztrus.ttf';
			$fontSize = 12 + rand(0, 5);
			$filename = 'map';

			$black = ImageColorAllocate($img, 0, 0, 0);
			$back  = ImageColorAllocate($img, 255, 215, 104);
			$bback = ImageColorAllocate($img, 205, 165, 054);
			$white = ImageColorAllocate($img, 255, 255, 255);
			
			$green = ImageColorAllocate($img,   0, 240,   0);
			$maroon= ImageColorAllocate($img, 210,  30, 255);

			$blue  = ImageColorAllocate($img,   0,   0, 255);

			ImageFill($img, 0, 0, $back);
			ImageRectangle($img, 0, 0, $w - 1, $h - 1, $bback);
			ImageRectangle($img, 1, 1, $w - 2, $h - 2, $white);

			if ($c = $location->creatures) {
				$char = Char::get();
				$frac = intval($char->fraction);
				$a = array(-1 => '{$hostile}', 0 => '{$neutral}', 1 => '{$friendly}');

				$offset = self::FRAME_MARGIN / 2 + 1;
				foreach ($c as $id => $spawn) {
					$r = RUSH::fracRelation($spawn->npc->fraction, $frac);
					$x = $spawn->x * self::CELL_SIZE;
					$y = $spawn->y * self::CELL_SIZE;
					$hc = intval($spawn->pget(Creature::DATA_HPCR));
					$hx = intval($spawn->pget(Creature::DATA_HPMX));
					$mc = intval($spawn->pget(Creature::DATA_MPCR));
					$mx = intval($spawn->pget(Creature::DATA_MPMX));
					$denomh = self::CELL_SIZE * $hc / max($hx, 1);
					$denomm = self::CELL_SIZE * $mc / max($mx, 1);

					// white rectangle around creature
					ImageRectangle($img, $offset + $x, $offset + $y, $x + self::CELL_SIZE, $y + self::CELL_SIZE, $white);

					// hp background
					imagefilledrectangle($img, $offset + $x, $offset + $y, $x + self::CELL_SIZE, $offset + $y + 2, $maroon);
					// hp bar
					imagefilledrectangle($img, $offset + $x, $offset + $y, $x + $denomh, $offset + $y + 1, $green);

					// mp bar
					imagefilledrectangle($img, $offset + $x, $offset + $y + 2, $x + $denomm, $offset + $y + 2, $blue);
				}
			}

			header('Content-disposition: inline; filename=' . $filename . '.png');
			header('content-type: image/png');
			header('cache-control: no-cache');
			header('cache-control: max-age=1');

  		imagetruecolortopalette($img, true, 64);

			imagepng($img);
			die();
			return false;
		}
	}

	class LocationRenderer extends BasicLocationRenderer {

	}
