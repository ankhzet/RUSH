<?php

	require_once dirname(__FILE__) . '/../models/rush.php';
	
	class CharController extends BaseController {

		public function anyIndex() {
			$char = Char::get();
			if (!$char)
				return Redirect::to('char/select');

			return $this->anySummary($char);
		}

		public function anySelect() {
			self::dialog('char/create', 'char.create');
			return View::make('char.list', array('chars' => Auth::user()->chars()));
		}

		public function anyPick(Char $char) {
			RUSH::oSet('char', $char->id);
			RUSH::save();
			return Redirect::to('/location');
		}

		public function anySummary(Char $char) {
			self::dialog("char/pick/{$char->name}", 'char.pick');
			return self::plain(CharRenderer::render($char));
		}

		public function anyHeartstone() {
			$char = Char::get();
			$h = $char->home;
			$c = $char->cinematics;
			$loc = $c ? $c : $char->location;
			if ($h == $loc)
				return self::plain('{$err_c2}');
			else {
				if ($c) {
					return self::plain('{$err_c3}');
				} else {
					if (Input::get('action') != 'go') {
						self::dialog('/char/heartstone?action=go', 'yes');
						return self::plain('{$gohome}');
					} else {
						$cast = SpellCast::bind(Spell::get(KnownSpells::HEARTSTONE));

						$error = $cast->castBy($char);
						if ($error != SpellCast::CAST_OK) {
							$detail = ($error == SpellCast::CAST_COOLDOWN) ? ' ({$restoring} ' . maketime($cast->cooldown()) . ')' : '';
							return self::plain("{\$err_s{$error}}$detail");
						} else
							return Redirect::to('location');
					}
				}
			}
		}
		public function getCreate() {
			return View::make('char.create');
		}

		public function postCreate() {
			$validator = Validator::make(Input::all(), [
				'char.name' => 'required|unique:chars,name',
				'char.frac' => 'required_if:step,2,3,4|integer|not_in:0',
				'char.race' => 'required_if:step,3,4|integer|not_in:0',
				'char.spec' => 'required_if:step,4|integer|not_in:0'
				]);

			$step = intval(Input::get('step'));

			$view = View::make('char.create');

			if ($validator->fails()) {
				$s = Input::except('step') + ['step' => $step - 1];
				$view->withInput($s)->withErrors($validator);
			} else {
				$view->withInput(Input::all());
				if ($step >= 5) {
					$char = CharHelper::create(Auth::id(), Input::get('char'));
					if ($char)
						return Redirect::to('char/select');
					else
						$view->withErrors(['char' => '{$char.creation.err}']);
				}	
			}

			return $view;
		}

		public function anyDelete(Char $char) {
			if (Input::get('action') == 'delete') {
				CharHelper::delete($char->id);
				return Redirect::to('/char/select');
			}

			self::dialog("/char/delete/{$char->name}?action=delete", 'yes');

			return self::plain('{$char.dodelete} [' . $char->makeClickable() . '] ?');
		}

		public function actionSpells($r) {
			$spellid = intval($r[0]);
			require_once 'rush_battle.php';
			$spells = array();

			$o = msqlDB::o();
			$s = $o->select('_spell', '1', '`id` as `0`');
			if ($s && count($f = $o->fetchrows($s))) {
				foreach ($f as $row)
					$spells[$id = intval($row[0])] = Spell::get($id);
			}

			$type = array(1 => 'Healing', '2' => 'Damage', 3 => 'Drain', 4 => 'Absorb', 5 => 'Immunity');
			foreach ($spells as $id => &$spell)
				if ($spellid == $id)
					echo '<p class="cstat"><b>' . $type[$spell->type] . '<span>[<a href="/char/spells/' . $id . '">' . $spell->title . '</a>]</span></b></p>';
				else
					echo '<p class="cstat">' . $type[$spell->type] . '<span>[<a href="/char/spells/' . $id . '">' . $spell->title . '</a>]</span></p>';

			echo '<hr />';

			$step = intval($_REQUEST[step]);
			$step = $step ? $step : 1;

			$spec = $_REQUEST[inspec] ? $_REQUEST[spec] : 0;
			$race = $_REQUEST[inrace] ? $_REQUEST[race] : 0;

			$sa = array();
			$sr = array();
			$ss = array();
			if ($race) {
				$s = $o->select('_init_spells', '`race` = ' . $race, '`spell` as `0`');
				if ($s && count($f = $o->fetchrows($s))) {
					foreach ($f as $row)
						$sr[$id = intval($row[0])] = Spell::get($id);
				}
			}
			if ($spec) {
				$s = $o->select('_init_spells', '`spec` = ' . $spec, '`spell` as `0`');
				if ($s && count($f = $o->fetchrows($s))) {
					foreach ($f as $row)
						$ss[$id = intval($row[0])] = Spell::get($id);
				}
			}

			$s = $o->select('_init_spells', '`race` = 0 and `spec` = 0', '`spell` as `0`');
			if ($s && count($f = $o->fetchrows($s))) {
				foreach ($f as $row)
					$sa[$id = intval($row[0])] = Spell::get($id);
			}

			if (count($sa)) {
				echo '<h4>Global spells:</h4>';
				foreach ($sa as $id => &$spell)
					if ($spellid == $id)
						echo '<p class="cstat"><b>' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></b></p>';
					else
						echo '<p class="cstat">' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></p>';
			}

			if (count($sr)) {
				echo '<h4>Race specific:</h4>';
				foreach ($sr as $id => &$spell)
					if ($spellid == $id)
						echo '<p class="cstat"><b>' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></b></p>';
					else
						echo '<p class="cstat">' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></p>';
			}

			if (count($ss)) {
				echo '<h4>Spec specific:</h4>';
				foreach ($ss as $id => &$spell)
					if ($spellid == $id)
						echo '<p class="cstat"><b>' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></b></p>';
					else
						echo '<p class="cstat">' . $type[$spell->type] . '<span>[<a>' . $spell->title . '</a>]</span></p>';
			}

			if (!$spellid)
				return;

			if ($_REQUEST[action] == 'add') {
				$s = $o->insert('_init_spells', array(
					'spec' => $spec
				, 'race' => $race
				, 'spell'=> $spellid
				));
//				RUSH::redirect('char/spells');
			}
?>
<br />
<hr />
<form action="/char/spells/<?echo $spellid?>" method="get" />
	<input type=hidden id=iaction name=action value=add />

	<p><input type=checkbox name="inspec" <?echo $spec ? ' checked' : ''?> /> Specialization:
	<span style="min-width: 160px; float: right">
		<input type=radio id=idwarrior name="spec" value="1" <?echo $spec == '1' ? 'checked' : ''?>/>
		<label for=idwarrior>Warrior</label>
		<input type=radio id=idmage name="spec" value="2" <?echo $spec == '2' ? 'checked' : ''?>/>
		<label for=idmage>Mage</label>
	</span></p>

	<p><input type=checkbox name="inrace" <?echo $race ? ' checked' : ''?>/> Race:
	<span style="min-width: 160px; float: right">
		<select id=idrace name="race">
<?php
	$fr = RUSH::fracRaces();
	$f  = RUSH::fracs();
	$r  = RUSH::races();
	$s  = RUSH::specs();
	$frac = $data[frac] ? $data[frac] : 1;
	foreach ($fr as $frac => $races) {
		echo '<option disabled>-- ' . $f[$frac] . '</option>';
		foreach ($races as $ra)
			echo '<option value="' . $ra . '" ' . ($race == $ra ? 'selected' : '') . '>' . $r[$ra] . '</option>';
	}
?>

		</select>
	</span></p>
	<br />
	<input type=submit value=" Add spell " />
	<input type=submit value=" Show spells " onclick="$I('iaction').value = ''; return true;" />
</form>

<?php

		}

	}
