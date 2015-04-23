<?php namespace Brave\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Core Auth Group Model
 *
 * @package Brave\Core\Models
 */
class CoreAuthGroup extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'core_auth_groups';

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
		'name',
	);

	/**
	 * @return mixed
	 */
	public function users()
	{
		return $this->belongsToMany('\Brave\Core\Models\CoreAuthUser', 'core_auth_groups_map', 'group_id', 'user_id')->withTimestamps();
	}

}