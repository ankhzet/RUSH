<?php

	namespace App\Http\Controllers;

	class HomeController extends Controller {

		public function anyIndex() {
			return view('home');
		}

	}
