<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout() {
		if ( ! is_null($this->layout)) {
			$this->layout = View::make($this->layout);
		}
	}

	protected static function dialog($link, $title) {
		if ($link) {
			$dialog  = View::shared('dialog', []);
			$dialog[]= ['link' => $link, 'title' => $title];
		} else 
			$dialog = [];
			
		View::share('dialog', $dialog);
	}

	protected static function plain($contents) {
		return View::make('layouts.structure', ['content' => $contents]);
	}
}
