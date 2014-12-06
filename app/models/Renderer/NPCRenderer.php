<?php

	class NPCRenderer {

		static function render($qid = 0, $statonly = false, $insertion = '{$spawn}') {
			RUSH::addKey('npc', $this);

			$f = RUSH::fraction($this->fraction);
			$this->{'fraction:title'} = $f['title'];

			$t  = '<h4>{$npc:subtitle} {$npc:title}</h4>';
			$t .= $insertion;
			$t .= '<p>{$charlevel}: {$npc:level} {$fraction}</p>';
			View::dlgButton('/location', 'back');


			if ($statonly) return $t;

			$relation = Char::get()->fracRelation($this->fraction);
			$t .= '<hr>';
			$t .= '<p>{$loc:hello' . ($relation) . '}</p>';

			if (!$this->quests)
				$this->fetchQuests($qid > -1);

			if (count($this->quests)) {
				if ($qid && ($quest = $this->quests[$qid])) {
					$t .= $quest->render();
				} else {
					$t .= '<p>{$loc:havejob' . ($relation) . '}</p><ul>';
					foreach ($this->quests as $quest) {
						$t .= '<li>' . $quest->questStr() . '</li>';
					}
					$t .= '</ul>';
				}
			} else
				$t .= '<p>{$loc:hello}</p>';

			return $t;
		}	
	}