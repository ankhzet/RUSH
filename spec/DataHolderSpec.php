<?php

namespace spec;

use PhpSpec\Laravel\EloquentModelBehavior;

class DataHolderSpec extends EloquentModelBehavior {
	var $attrs = ['maxhp', 'maxmp', 'curhp'];


	function it_is_initializable() {
		$this->shouldHaveType('DataHolder');
	}


	function fillData() {
		foreach ($this->attrs as $value)
			$this->pset($value, "$value:value");

	}

	function it_should_set () {
		$this->getAttrUpdated()->shouldBe(false);
		$this->fillData();
		$this->getAttrUpdated()->shouldBe(true);
	}

	function it_should_get () {
		$this->fillData();

		foreach ($this->attrs as $value)
			$this->pget($value)->shouldBe("$value:value");
	}
}
