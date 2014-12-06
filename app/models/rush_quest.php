<?php
	require_once 'rush_charachter.php';
	require_once 'rush_item.php';

	class QBrunch {
		var $id = 0;
		var $nextq = 0;
		var $objs = array();
		var $complete = 0;

		function __construct($id, $next) {
			$this->id = $id;
			$this->nextq = $next;
		}
	}

	class QObjective {
		const QO_KILL    = 1;
		const QO_COLLECT = 2;
		const QO_GOLD    = 3;
//		const QO_CAST    = 4;
//		const QO_REP     = 5;

		var $id     = 0;
		var $type   = 0;
		var $aguid  = 0;
		var $acount = 0;
		var $prog   = 0;
		function __construct($data) {
			$this->id     = intval($data[id]);
			$this->type   = intval($data['type']);
			$this->aguid  = intval($data[aguid]);
			$this->acount = intval($data[acount]);
			$this->prog   = 0;
		}

		function objStr($char) {
			$c = $this->acount > 0 ? ' (' . $this->prog . '/' . $this->acount . ')' : '';
			switch ($this->type) {
			case self::QO_KILL:
				$npc = NPC::get($this->aguid);
				$r = '{$loc:kill} ' . $npc->title . ' ' . $npc->subtitle . $c;
				break;
			case self::QO_COLLECT:
				$item = Item::get($this->aguid);
				$r = '{$loc:collect} <a href="/items/guid/' . $this->aguid . '">' . $item->title . '</a>' . $c;
				break;
			case self::QO_GOLD:
				$item = NPC::get($this->aguid);
				$r = '{loc:gold} (' . $char->gold . '/' . $this->acount . ')';
				break;
			}
			if ($this->prog >= $this->acount) $r = '<s>' . $r . '</s>';
			return $r;
		}
	}


	class QuestNode {
		const QS_NOTON     = 0;
		const QS_ONQUEST   = 1;
		const QS_COMPLETED = 2;
		const QS_FINISHED  = 4;
		const QS_FAILED    = 8;
		const QS_UNAVAIL   = 16;

		const QR_ITEM      = 0;
		const QR_HONOR     = 1;
		const QR_GOLD      = 2;
		const QR_REP       = 3;
		const QR_QUEST     = 4;
		const QR_CUSTOM    = 5;

		var $id      = 0;
		var $title   = '';
		var $content = '';
		var $minlvl  = 1;
		var $baselvl = 1;


		function __construct($id) {
			$this->id   = $id;
			$this->char = Char::get();
			$this->aquire();
		}

		function aquire() {
			$o = msqlDB::o();
			$s = $o->select('_quest', '`id` = ' . $this->id . ' limit 1');
			if ($s && ($r = @mysql_fetch_array($s))) {
				$this->title   = $r[title];
				$this->content = $r[content];
				$this->minlvl  = intval($r[minlvl]);
				$this->baselvl = intval($r[baselvl]);
				$this->rare    = intval($r[rare]);
			}
		}

		function fetchState() {
			$o = msqlDB::o();
			$s = $o->select('_quest_state', '`quest` = ' . $this->id . ' and `char` = ' . $this->char->id, '`state` as `0`');
			$r = $s ? @mysql_fetch_row($s) : 0;
			$this->state = $r ? intval($r[0]) : 0;

			switch ($this->state) {
			case self::QS_NOTON:
				if ($this->minlvl > $this->char->level) {
					$this->state = self::QS_UNAVAIL;
					break;
				}
				$s = $o->select('_quest q, _quest_brunch b', 'b.`next` = ' . $this->id . ' and q.`id` = b.`quest` limit 1', 'q.`id` as `0`');
				if ($s && count($f = $o->fetchrows($s))) {
//					debug($f);
					$this->state = self::QS_UNAVAIL;
					$qidx = array();
					foreach ($f as $row)
						$qidx[] = intval($row[0]);

					$s = $o->select('_quest_state', '`char` = ' . $this->char->id . ' and `quest` in (' . join(',', $qidx) . ') and `state` = ' . self::QS_FINISHED, 'count(`id`) as `0`');
					$r = $s ? @mysql_fetch_row($s) : 0;
//					debug($r, 'finished');
					$ps = $r ? intval($r[0]) : 0;
					if ($ps)
						$this->state = self::QS_NOTON;
				}
				break;
			default:
			}
//			debug($this);
			return $this->state;
		}

		function questStr() {
			switch ($this->state) {
			case self::QS_NOTON:
				return '<span class="new">! <a href="/quest/' . $this->id . '">' . $this->title . '</a></span>';
			case self::QS_ONQUEST:
				return '<span class="taken">? <a href="/quest/' . $this->id . '">' . $this->title . '</a></span>';
			case self::QS_COMPLETED:
				return '<span class="complete">? <a href="/quest/' . $this->id . '">' . $this->title . '</a></span>';
			case self::QS_FAILED:
				return '<span class="failed">X <a href="/quest/' . $this->id . '">' . $this->title . '</a></span>';
			default:
				return '<span class="failed"><s>' . $this->title . '</s></span>';
			}
		}

		function fetchObjectives() {
			if ($this->brunches)
				return $this->brunches;

			$this->brunches = array();
			switch ($this->state) {
			case self::QS_FINISHED:
			case self::QS_UNAVAIL:
				return;
			}
			$o = msqlDB::o();
			$s = $o->select('_quest_brunch', '`quest` = ' . $this->id, '`id` as `0`, `next` as `1`');
			if ($s && count($f = $o->fetchrows($s))) {
				foreach ($f as $brunch)
					$this->brunches[$id = intval($brunch[0])] = new QBrunch($id, intval($brunch[1]));
			}

			foreach ($this->brunches as $id => &$brunch) {
				$s = $o->select('_brunch_objectives', '`brunch` = ' . $id, '`id`, `type`, `aguid`, `acount`');
				if ($s && count($r = $o->fetchrows($s))) {
					foreach ($r as $oj) {
						$brunch->objs[intval($oj['id'])] = new QObjective($oj);
					}
				}
				$oidx = array_keys($brunch->objs);
				$s = $o->select('_objective_progress', '`char` = ' . $this->char->id . ' and `objective` in (' . join(',', $oidx) . ')', '`objective` as `0`, `count` as `1`');
				if ($s && count($r = $o->fetchrows($s))) {
					foreach ($r as $progress) {
						$oid   = intval($progress[0]);
						$count = intval($progress[1]);
						$o = $brunch->objs[$oid];
						$o->prog = $count;

						if ($count >= $o->acount)
							$brunch->complete++;
					}
				}
				if ($brunch->complete >= count($brunch->objs)) {
					$brunch->completed = true;
					if ($this->state != self::QS_COMPLETED)
						$this->markQuest(self::QS_COMPLETED);
				}
			}
			return $this->brunches;
		}

		function fetchItemsProgress() {
			$char = Char::get()->id;

			$b = $this->fetchObjectives();
			$i = array();
			foreach ($b as $bid => &$brunch)
				foreach ($brunch->objs as $oid => &$obj)
					if ($obj->type == QObjective::QO_COLLECT)
						$i[$obj->aguid] = 0;

			if (!count($i)) return;

			require_once 'rush_inventory.php';
			$e = Inventory::get($char);
			if (count($e->bags))
				foreach ($e->bags as &$bag)
					if (count($bag->items))
						foreach ($bag->items as $id => &$item)
							if (isset($i[$guid = $item->tpl->guid]))
								$i[$guid] += $bag->count[$id];

			foreach ($i as $guid => $count)
				if ($count)
					self::onGain($char, $guid, QObjective::QO_COLLECT, $count);
		}

		function render($reward = false) {
			RUSH::addKey('quest', $this);
			$t .= '<h4>{$quest:title}</h4>';
			if (!$reward) {
				$t .= '<p>{$quest:content}</p>';
				$o = $this->fetchObjectives();
				$c = 0;
				$j = '';
				foreach ($o as $brunch) {
					foreach ($brunch->objs as $obj) {
						$c++;
						$j .= '<li>' . $obj->objStr($this->char) . '</li>';
					}
				}
				if ($c) {
					$t .= '<h4>{$loc:objectives}:</h4>';
					$t .= '<ul>' . $j . '</ul>';
				}
			}

			$r = $this->rewards ? $this->rewards : $this->fetchRewards();
			require_once 'rush_item.php';
			$i = &$r[0];
			$c = &$r[1];
			$rewards = count($c);
			if ($rewards) {
				$t .= $reward ? '<h4>{$choose}:</h4>' : '<h4>{$maychoose}:</h4>';
				foreach ($c as $rew)
					switch ($rew[2]) {
					case self::QR_ITEM:
						$item = Item::get($guid = intval($rew[3]));
						$item->count = intval($rew[4]);
						if ($reward)
							$item->link = '/quest/finish/' . $rew[0];
						$t .= '<div class="items">' . $item->renderCell(false, false, $item->count) . '</div>';
					}
			}

			$level = $this->baselvl;
			$exp = Char::levelToExp($level);
			$gold = intval($exp * 0.01);
			if (count($i) || $gold) {
				$e = '';
				if ($gold)
					$e .= '<p class="cstat">{$rewgold}: ' . makeMoneyStr($gold) . '</p>';
				foreach ($i as $rew)
					switch ($rew[2]) {
					case self::QR_ITEM:
						$item = Item::get($guid = intval($rew[3]));
						$item->count = intval($rew[4]);
						$e .= '<div class="items">' . $item->renderCell(false, false, $item->count) . '</div>';
					}
				if ($e)
					$t .= ($rewards ? '<h4>{$alsoreceive}:</h4>' : '<h4>{$youreceive}:</h4>') . $e;
			}

			View::dlgButton($reward ? '/npc/quest' : '/npc', 'back');
//			debug($this);
			switch ($this->state) {
			case self::QS_NOTON:
				View::dlgButton('/quest/accept', 'accept');
				break;
			case self::QS_ONQUEST:
				View::dlgButton('/quest/decline', 'decline');
				break;
			case self::QS_COMPLETED:
				if (!$reward || !$rewards)
				View::dlgButton('/quest/finish', 'finish');
				break;
			case self::QS_FAILED:
				View::dlgButton('/quest/decline', 'decline');
				break;
			}
			return $t;
		}

		function markQuest($state) {
			$o = msqlDB::o();
			$s = $o->delete('_quest_state', '`char` = ' . $this->char->id . ' and `quest` = ' . $this->id);
			$s = $o->insert('_quest_state', array('char' => $this->char->id, 'quest' => $this->id, 'state' => $state));
			$this->state = $state;
			switch ($state) {
			case self::QS_COMPLETED:
			case self::QS_FAILED:
				break;
			case self::QS_NOTON:
			case self::QS_ONQUEST:
			case self::QS_FINISHED:
				if (!$this->brunches)
					$this->fetchObjectives();
				$b = 0;
				$oidx = array();
				foreach ($this->brunches as $bid => &$brunch) {
					if ($brunch->completed)
						$b = $bid;
					$oidx = array_merge($oidx, array_keys($brunch->objs));
				}

				if (count($oidx))
					$s = $o->delete('_objective_progress', '`char` = ' . $this->char->id . ' and `objective` in (' . join(',', $oidx) . ')');

				return $this->brunches[$b]->nextq;
			}
			return 0;
		}

		function fetchRewards() {
			if (!$this->brunches)
				$this->fetchObjectives();

			$bidx = array();
			$b = 0;
			foreach ($this->brunches as $id => &$brunch) {
				if ($brunch->completed)
					$b = $brunch->id;
				$bidx[] = $brunch->id;
			}
			if ($b)
				$bidx = array($b);

			$i = array();
			$c = array();

			if (count($bidx)) {
				$o = msqlDB::o();
				$s = $o->select('_brunch_rewards', '`brunch` in (' . join(',', $bidx) . ')', '`id` as `0`, `instant` as `1`, `type` as `2`, `guid` as `3`, `count` as `4`');
				if ($s && count($r = $o->fetchrows($s)))
					foreach ($r as $row)
						if (intval($row[1]))
							$i[intval($row[0])] = $row;
						else
							$c[intval($row[0])] = $row;

				if (count($c) == 1)
					array_push($i, array_pop($c));
			}
			return $this->rewards = array($i, $c);
		}

		static function charQuests($charid, $state) {
			$o = msqlDB::o();
			$s = $o->select('_quest_state', '`char` = ' . $charid . ($state ? ' and `state` = ' . $state : ''), '`quest` as `0`, `state` as `1`');
			$a = array();
			if ($s && count($r = $o->fetchrows($s)))
				foreach ($r as $row)
					$a[intval($row[0])] = intval($row[1]);
			return $a;
		}

		function reward($char, $choice) {
			if (!$this->rewards)
				$this->fetchRewards();

			$i = &$this->rewards[0];
			$c = &$this->rewards[1];
			$g = array();
			$n = array();
			foreach ($i as $id => $reward) {
				$type = intval($reward[2]);
				$guid = intval($reward[3]);
				$count= intval($reward[4]);
				if ($type == self::QR_ITEM) {
					$g[$id] = $guid;
					$n[$id] = $count;
				}
			}
			if ($reward = $c[$choice]) {
				$type = intval($reward[2]);
				$guid = intval($reward[3]);
				$count= intval($reward[4]);
				if ($type == self::QR_ITEM) {
					$g[$choice] = $guid;
					$n[$choice] = $count;
				}
			}

			if (count($g)) {
				require_once 'rush_inventory.php';
				$i = Inventory::get($char->id);

				foreach ($g as $id => $guid)
					if (!$i->put($guid, $n[$id]))
						return false;
				$i->save();
			}
			$level = $this->baselvl;
			$exp = Char::levelToExp($level);
			$gold = intval($exp * 0.01);
			if ($gold)
				$char->gainGold($gold);

			$mark = true;
			foreach ($i as $id => $reward) {
				$type = intval($reward[2]);
				$guid = intval($reward[3]);
				$count= intval($reward[4]);
				switch ($type) {
				case self::QR_HONOR :
					break;
				case self::QR_GOLD  :
					$char->gainGold($count);
					break;
				case self::QR_REP   :
					break;
				case self::QR_QUEST :
					$q = $guid ? QuestNode::get($guid) : $this;
					if ($q && $q->id)
						$q->markQuest(self::QS_NOTON);
					if (!$guid)
						$mark = false;
					break;
				case self::QR_CUSTOM:
					switch ($guid) {
					case 0:
						$char->resurrect();
						break;
					case 1:
						$char->teleport($count);
						break;
					}
					break;
				}
			}

			if ($mark) {
				$next = $this->markQuest(self::QS_FINISHED);
				if ($next)
					RUSH::redirect('npc/quest/' . $next );
			}

			return true;
		}

		static function onGain($charid, $guid, $objective, $count = 1) {
//			echo "[$charid, $guid, $objective, $count]";
			$progress = 0;
			$q = self::charQuests($charid, self::QS_ONQUEST);
//			debug($q, 'quests');
			if (!count($q)) return false;
			$o = msqlDB::o();
			$s = $o->select('_quest_brunch', '`quest` in (' . join(',', array_keys($q)) . ')', '`id` as `0`');
			if ($s && count($r = $o->fetchrows($s))) {
				$b = array();
				foreach ($r as $row)
					$b[] = intval($row[0]);

//				debug($b, 'brunches');

				$s = $o->select('_brunch_objectives', '`type` = ' . $objective . ' and `aguid` = ' . $guid . ' and `brunch` in (' . join(',', $b) . ')', '`id` as `0`');
				if ($s && count($r = $o->fetchrows($s))) {
					$oj = array();
					foreach ($r as $row)
						$oj[intval($row[0])] = $count;

//					debug($oj, 'objectives');

					$u = array();
					$s = $o->select('_objective_progress', '`char` = ' . $charid . ' and `objective` in (' . join(',', array_keys($oj)) . ')', '`id` as `0`, `objective` as `1`, `count` as `2`');
					if ($s && count($r = $o->fetchrows($s))) {
						foreach ($r as $row) {
							$upd = intval($row[0]);
							$obj = intval($row[1]);
							$cnt = intval($row[2]);

							$u[$obj] = $upd;
							$oj[$obj] += $cnt;
						}
					}

//					debug($oj, 'objectives2');

					$progress += count($oj);
					foreach ($oj as $id => $cnt)
						if ($cnt > $count)
							$o->update('_objective_progress', array('count' => $cnt), '`id` = ' . $u[$id]);
						else
							$o->insert('_objective_progress', array('char' => $charid, 'objective' => $id, 'count' => $cnt));
				}
			}
			return $progress;
		}
	}

?>
