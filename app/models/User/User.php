<?php

	use Illuminate\Auth\Authenticatable;
	use Illuminate\Auth\Passwords\CanResetPassword;
	use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
	use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class User extends Eloquent implements AuthenticatableContract, CanResetPasswordContract {

		use Authenticatable, CanResetPassword, SoftDeletes;

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

		protected $fillable = array('name', 'email', 'password');

		public function roles() {
			return $this->belongsToMany('Role');
		}

	}
