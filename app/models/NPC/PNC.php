<?php

	class NPC extends Eloquent {
		protected $table ='creature_templates';

		var $spawns = [];
		static $cache = [];

		public function creatures() {
			return $this->hasMany('Creature');
		}

		static function get($guid = 0) {
			if (!$guid) $guid = RUSH::oGet('npc');
			$guid = intval($guid);

			if (!$guid) return null;

			if (!isset(self::$cache[$guid]))
				self::$cache[$guid] = self::find($guid);

			return self::$cache[$guid];
		}

		public function fetchQuests($onlyAvailable = true) {
			require_once 'rush_quest.php';
			$this->quests = array();
			$o = msqlDB::o();
			$s = $o->select('_npc_quests', '`npc` = ' . $this->guid, '`id` as `0`, `notstate` as `1`');
			if ($s && count($f = $o->fetchrows($s))) {
				foreach ($f as $quest) {
					$id = intval($quest[0]);
					$questNode = new QuestNode($id);

					$add = true;
					if ($onlyAvailable) {
						$not= intval($quest[1]);
						$state = $questNode->fetchState();
						$add = !($state & ($not));
					}
					if ($add)
						$this->quests[$id] = $questNode;
				}
			}
			return $this->quests;
		}

	}
