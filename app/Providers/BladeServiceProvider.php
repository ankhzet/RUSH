<?php 
	
	namespace App\Providers;

	use View;
	use Illuminate\Support\ServiceProvider;

	class BladeServiceProvider extends ServiceProvider {

		/**
		 * Register bindings in the container.
		 *
		 * @return void
		 */
		public function boot() {

			// View::swap(RushView::class);
			// \Blade::extend(function($view, $compiler) {
			// 	return preg_replace_callback('/\{\$([^\}]+)\}/', function($whole) {
			// 		$key = $whole[1];
			// 		debug($key);
			// 		return "[!$key!]";
			// 	}, $view);
			// });

		}

		public function register() {
		}

	}
