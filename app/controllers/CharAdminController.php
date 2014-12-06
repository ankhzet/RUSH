<?php

	class CharAdminController extends CharController {
		
		public function getExp(Char $char) {
			$char->gainExp(Char::levelToExp($char->level));
			return Redirect::to('char');
		}

		public function getRelevel(Char $char) {
			$char->level = 0;
			$char->exp = 0;
			$char->gainExp(Char::levelToExp($char->level));
			return Redirect::to('char');
		}

	}