<?php

	namespace App\Http\Controllers;

	use Illuminate\Foundation\Bus\DispatchesJobs;
	use Illuminate\Routing\Controller as BaseController;
	use Illuminate\Foundation\Validation\ValidatesRequests;
	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

	use View;

	abstract class Controller extends BaseController {
		use DispatchesJobs, ValidatesRequests;

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

		protected static function dialog($link = false, $title = null) {
			$dialog  = ($link !== false) ? View::shared('dialog', []) : [];

			if (!$dialog)
				$dialog = [['link' => 'javascript:window.history.back();', 'title' => 'back']];

			if ($link) {
				if (!$title) $title = $link;
				$dialog[]= ['link' => $link, 'title' => $title];
			}

			View::share('dialog', $dialog);
		}

		protected static function plain($contents) {
			self::dialog(null);
			return View::make('layouts.structure', ['content' => $contents]);
		}

	}
