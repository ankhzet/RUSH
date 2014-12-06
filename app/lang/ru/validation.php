<?php

return array(

	'custom' => array(
		'char' => array(
		'race' => array('required_if' => 'Выбери расу', 'not_in' => 'Выбери расу'),
		'frac' => array('required_if' => 'Выбери фракцию', 'not_in' => 'Выбери расу'),
		'spec' => array('required_if' => 'Выбери специализацию', 'not_in' => 'Выбери расу'),
		'name' => array('required' => 'Выбери ник', 'unique' => 'Ник занят'),
		)
	),

	'attributes' => array(
		'race' => 'раса',
		'frac' => 'фракция',
		'spec' => 'специализация',
		'name' => 'ник'
	),

);
