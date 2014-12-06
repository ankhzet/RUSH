<?php

	function getReqInt($name, $default = 0) {
		$value = intval(Input::get($name, $default));
		return $value ? $value : $default;
	}

	function normValue($val) {
		$denom = 0;
		$rest = 0;
		while ($val >= 1000) {
			$denom++;
			$rest = $val % 1000;
			$val  = intval($val / 1000);
		}

		switch ($denom) {
		case 0: $denom = ''; break;
		case 1: $denom = 'K'; break;
		case 2: $denom = 'M'; break;
		case 3: $denom = 'G'; break;
		case 4: $denom = 'T'; break;
		}

		return $rest > 0
			? floor(10 * (($val * 1000 + $rest) / 1000)) / 10 . $denom
			: $val . $denom;
	}

	function normCrit($value) {
		if ($value > 95) $value = 95;
		return floor($value * 100) / 100;
	}

	function makeMoneyStr($copper) {
		$c = intval($copper % 100);
		$silver = intval(($copper - $c) / 100);
		$s = intval($silver % 100);
		$g = intval(($silver - $s) / 100);

		$t = ($s || $g) ? "<span class=\"silver\">$s</span>" : '';
		if ($g) $t = "<span class=\"gold\">$g</span>$t";
		return "<span class=\"money\">$t<span class=\"copper\">$c</span></span>";
	}
