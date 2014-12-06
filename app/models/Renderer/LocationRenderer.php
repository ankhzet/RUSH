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
				$s2 = intval(self::CELL_SIZE / 2) + 1;
				foreach ($c as $spawn) {
					$id = $spawn->id;
					$r = RUSH::fracRelation($spawn->npc->fraction, $frac);
					$h = intval($spawn->pget('curhp'));
					$n = $spawn->npc->name;
					$o = array($spawn->x * self::CELL_SIZE + $s2, $spawn->y * self::CELL_SIZE + $s2, $s2);
					$m .= '  <area shape="circle" coords="' . join(',', $o) . '" href="/npc/' . $id . '" alt="' . htmlspecialchars($n) . '" title="' . htmlspecialchars($n) . '" />' . PHP_EOL;
					$l .= '<p>' . ($h <= 0 ? '[ x ]' : '[ <a>?</a> ]') . ' <a href="/npc/' . $id . '">' . $n . '</a><span style="float: right;">[' . $a[$r] . ']</span></p>';
				}
				$m .= '</map>' . PHP_EOL;
				$t  = $t . $l . $m;
			}
			return $t;
		}

		static function renderCreature($creature) {
			$s2 = intval(self::CELL_SIZE / 2) + 1;
			$id = $creature->id;
			$r = RUSH::fracRelation($creature->npc->fraction, $frac);
			$h = intval($creature->pget('curhp'));
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
			$green = ImageColorAllocate($img,   0, 255,   0);
			$blue  = ImageColorAllocate($img,   0,   0, 255);
			$maroon= ImageColorAllocate($img, 200,   0, 255);

			ImageFill($img, 0, 0, $back);
			ImageRectangle($img, 0, 0, $w - 1, $h - 1, $bback);
			ImageRectangle($img, 1, 1, $w - 2, $h - 2, $white);

			if ($c = $location->creatures) {
				$char = Char::get();
				$frac = intval($char->fraction);
				$a = array(-1 => '{$hostile}', 0 => '{$neutral}', 1 => '{$friendly}');

				foreach ($c as $id => $spawn) {
					$r = RUSH::fracRelation($spawn->npc->fraction, $frac);
					$x = $spawn->x * self::CELL_SIZE;
					$y = $spawn->y * self::CELL_SIZE;
					$hc = intval($spawn->pget('curhp'));
					$hx = intval($spawn->pget('maxhp'));
					$mc = intval($spawn->pget('curmp'));
					$mx = intval($spawn->pget('maxmp'));
					$denomh = (self::CELL_SIZE - 5) * $hc / max($hx, 1);
					$denomm = (self::CELL_SIZE - 5) * $mc / max($mx, 1);

					ImageRectangle($img, 3 + $x, 3 + $y, $x + self::CELL_SIZE, $y + self::CELL_SIZE, $white);

					imagefilledrectangle($img, 4 + $x, 2 + $y, $x + self::CELL_SIZE - 2, 5 + $y, $maroon);
					ImageRectangle($img, 4 + $x, 4 + $y, 4 + $x + $denomm, 5 + $y, $blue);
					ImageRectangle($img, 4 + $x, 2 + $y, 4 + $x + $denomh, 3 + $y, $green);
				}
			}

			header('Content-disposition: inline; filename=' . $filename . '.png');
			header('content-type: image/png');
			header('cache-control: no-cache');
			header('cache-control: max-age=1');

			imageinterlace($img, 1);
  		imagetruecolortopalette($img, true, 32);

			imagepng($img);//imagejpeg($img, null, 20);
			die();
			return false;
		}
	}

	class LocationRenderer extends BasicLocationRenderer {

	}
