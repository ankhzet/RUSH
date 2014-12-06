<?php

	use Illuminate\Auth\UserTrait;
	use Illuminate\Auth\UserInterface;
	use Illuminate\Auth\Reminders\RemindableTrait;
	use Illuminate\Auth\Reminders\RemindableInterface;
	use Illuminate\Database\Eloquent\SoftDeletingTrait;

	use RUSH\Char;

	class User extends Eloquent implements UserInterface, RemindableInterface {

		use UserTrait, RemindableTrait, SoftDeletingTrait;

		/** For soft deletion. */
	    protected $dates = ['deleted_at'];

		/**
		 * The database table used by the model.
		 *
		 * @var string
		 */
		protected $table = 'users';

		/**
		 * The attributes excluded from the model's JSON form.
		 *
		 * @var array
		 */
		protected $hidden = array('password', 'remember_token');

		protected $fillable = array('name', 'email');

		public function roles() {
      return $this->belongsToMany('Role');
    }

		public function chars() {
      return $this->hasMany('Char');
    }

		static function fetchChars($user) {
			return User::find(intval($user))->chars();
		}

	}
