<?php

	class CharAdminController extends CharController {
		
		public function anySummary(Char $char) {
			self::dialog("char/exp/{$char->name}", 'char.exp');
			self::dialog("char/relevel/{$char->name}", 'char.firstlevel');
			return parent::anySummary($char);
		}

		public function anyExp(Char $char) {
			$char->gainExp(Char::levelToExp($char->level));
			return Redirect::to("char/{$char->name}");
		}

		public function anyRelevel(Char $char) {
			$char->level = 0;
			$char->exp = 0;
			$char->gainExp(Char::levelToExp($char->level));
			return Redirect::to("char/{$char->name}");
		}

	}