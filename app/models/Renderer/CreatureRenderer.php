<?php

	class CreatureRenderer {

		static function render($qid = 0, $statsonly = false) {
			$t = '<p class="cstat"">{$stat01}: <span>' . normValue($this->pget('curhp')) . '/' . normValue($this->npc->basehp) . '</span></p>';
			$t .= '<p class="cstat">{$stat02}: <span>' . normValue($this->pget('curmp')) . '/' . normValue($this->npc->basemp) . '</span></p>';
			RUSH::$keys['loc']->spawn = $t;
			return $this->npc->render($qid, $statsonly);
		}

	}