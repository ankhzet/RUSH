<?php

	class CharRenderer {
	
		static function render(Char $char) {
			$t  = '<h4>' . $char->name . '</h4>';
			$t .= '<p>' . $char->specStr() . '</p>';
			$t .= '<p class="cstat">{$level}: <span>' . $char->level . '</span></p>';
			$t .= '<p class="cstat">{$exp}:<span>' . normValue($char->exp) . ' / ' . normValue(Char::levelToExp($char->level)) . '</span></p>';
			$t .= '<p class="cstat">{$gold}: <span>' . makeMoneyStr($char->gold) . '</span></p>';
			$s = array(RUSH::STAT_STAM, RUSH::STAT_INT);
			foreach ($s as $stat)
				$t .= '<p class="cstat">{$stat0' . $stat . '}: <span>' . normValue($char->statToRel($stat)) . '</span></p>';
			$t .= '<p>&nbsp;</p>';
			$s = array(RUSH::STAT_STAM, RUSH::STAT_INT, RUSH::STAT_STR, RUSH::STAT_AGI);
			foreach ($s as $stat)
				$t .= '<p class="cstat">{$stat' . $stat . '}: <span>' . normValue($char->calcStat($stat)) . '</span></p>';
			$t .= '<p>&nbsp;</p>';
			$s = array(RUSH::STAT_AP, RUSH::STAT_SPD);
			foreach ($s as $stat)
				$t .= '<p class="cstat">{$stat' . $stat . '}: <span>' . normValue($char->statToRel($stat)) . '</span></p>';
			$s = array(RUSH::STAT_MCRIT, RUSH::STAT_SCRIT);
			foreach ($s as $stat)
				$t .= '<p class="cstat">{$stat' . $stat . '}: <span>' . normCrit($char->statToRel($stat)) . '%</span></p>';

			$t .= '<p>&nbsp;</p>';
			$t .= '<p class="cstat">{$char.location}: <span>' . $char->location->makeClickable() . '</span></p>';
			$t .= '<p class="cstat">{$char.home}: <span>' . $char->home->makeClickable() . '</span></p>';

			if ($char->cinematics)
				$t .= '<p class="cstat">{$char.cinematics}: <span>' . $char->cinematics->makeClickable() . '</span></p>';

			return $t;
		}


	}