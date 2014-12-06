<?php

	require_once dirname(__FILE__) . '/../models/rush.php';
	
	class LocationController extends BaseController {

		public function anyIndex(Location $location = null) {
			if (!$location) {
				$char = Char::get();
				if (!$char)
					return Redirect::to('char/select');
					
				$locationId = $char->locatedAt();
				$location = Location::get($locationId);
			}
			return View::make('location.show', ['location' => $location]);
		}

		public function anyRender() {
			if (!($locationId = Input::get('id'))) {
				$char = Char::get();
				if (!$char)
					return App::abort(404, 'Pick user first');

				$locationId = $char->locatedAt();
			}

			return LocationRenderer::renderImage(Location::get($locationId));
		}

	}
