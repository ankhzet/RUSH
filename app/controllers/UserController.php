<?php

class UserController extends BaseController {

	var $log_validation = array(
        'email' => 'required|email',
	      'password' => 'required|min:8'
	    );
	var $reg_validation = array(
	      'name' => 'required|unique:users|min:3',
        'email' => 'required|email|unique:users',
	      'password' => 'required|min:8',
	      'password2' => 'required|same:password',
        'agreed' => 'accepted'
	    );

	public function anyIndex() {
		return Auth::check() ? $this->showProfile(Auth::user()) : Redirect::to("user/login");
	}

	public function getLogin() {
		return View::make('user.login');
	}

	public function postLogin() {
		$validator = Validator::make(Input::all(), $this->log_validation);

		if ($validator->fails())
			return Redirect::to('user/login')->withErrors($validator)->withInput(Input::all());
		else {
			if (Auth::attempt(Input::except('remember'), Input::has('remember')))
				return Redirect::intended('/');

			return Redirect::to("user/login")->withErrors((array('user' => 'failed to login')))->withInput(Input::all());
		}
	}

	public function anyLogout() {
		Auth::logout();
		return Redirect::to('/');
	}

	public function getRegister() {
		return View::make('user.register');
	}

	public function postRegister() {
		$validator = Validator::make(Input::all(), $this->reg_validation);

		if ($validator->fails())
			return Redirect::to('user/register')->withErrors($validator)->withInput(Input::all());
		else {
			$user = new User(Input::except('password', 'password2', 'agreed'));
			$user->password = Hash::make(Input::get('password'));

			$user->roles()->attach(4);

			$user->save();
			return Redirect::to("user/{$user->id}");
		}
	}

	public function showProfile(User $user) {
		return View::make('user.profile', array('user' => $user));
	}

}
