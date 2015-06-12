<?php


	class Role extends Eloquent {

		const ADMIN      = 1;
		const MODERATOR  = 2;
		const GAMEMASTER = 3;
		const USER       = 4;
		const BANNED     = 5;
		const MUTED      = 6;


		/**
		 * The database table used by the model.
		 *
		 * @var string
		 */
		protected $table = 'roles';
		public $timestamps = false;

		public function users() {
			return $this->belongsToMany('User');
		}

	}
