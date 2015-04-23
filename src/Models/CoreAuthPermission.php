<?php namespace Brave\Core\Models;

/**
 * Core Auth Permission Model
 *
 * @package Brave\Core\Models
 */
class CoreAuthPermission extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'core_auth_permissions';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

	/**
	 * The attributes that can be edited in models.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'user_id',
		'name',
	);



	/**
	 * @return mixed
	 */
	public function users()
	{
		return $this->belongsToMany('\Brave\Core\Models\CoreAuthUser', 'core_auth_permissions_map', 'permission_id', 'user_id');
	}
}