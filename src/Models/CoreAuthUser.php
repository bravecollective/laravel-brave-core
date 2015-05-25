<?php namespace Brave\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class CoreAuthUser
 *
 * @package Brave\Core\Models
 */
class CoreAuthUser extends Model implements Authenticatable {

	/**
	 * Database table name
	 * @var string
	 */
	protected $table = 'core_auth_users';

	/**
	 * Attribute hidden from the model's JSON form
	 * @var string
	 */
	protected $hidden = ["token", "remember_token"];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'token',
		'remember_token',
		'character_name',
		'corporation_id',
		'corporation_name',
		'alliance_id',
		'alliance_name',
		'status',
	];

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier() {
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword() {
		return $this->token;
	}

	/**
	 * Get the token value for the "remember me" session.
	 *
	 * @return string
	 */
	public function getRememberToken() {
		return $this->remember_token;
	}

	/**
	 * Set the token value for the "remember me" session.
	 *
	 * @param  string $value
	 *
	 * @return void
	 */
	public function setRememberToken( $value ) {
		$this->remember_token = $value;
	}

	/**
	 * Get the column name for the "remember me" token.
	 *
	 * @return string
	 */
	public function getRememberTokenName() {
		return 'remember_token';
	}

	/**
	 * Checks if a user is member of a group
	 * @param string $group
	 *
	 * @return bool
	 */
	public function isMemberOf($group){
		return $this->groups->contains('name', $group);
	}

	/**
	 * Checks if a user has a specific permission
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasPermission($permission){
		return $this->permissions->contains('name', $permission);
	}

	/**
	 * Get User ID by User name
	 * @param $query
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function scopeIdByName($query, $name){
		return $query->select('id')->where('character_name', 'LIKE', "%{$name}%");
	}

	/**
	 * Group List Relationship
	 *
	 * @return mixed
	 */
	public function groups()
	{
		return $this->belongsToMany('\Brave\Core\Models\CoreAuthGroup', 'core_auth_groups_map', 'user_id', 'group_id')->withTimestamps();
	}

	/**
	 * Permission List Relationship
	 *
	 * @return mixed
	 */
	public function permissions()
	{
		return $this->belongsToMany('\Brave\Core\Models\CoreAuthPermission', 'core_auth_permissions_map', 'user_id', 'permission_id')->withTimestamps();
	}

}