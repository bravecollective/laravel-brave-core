<?php namespace Brave\Core\Providers;

use Brave\API;
use Brave\Core\Models\CoreAuthUser;
use Brave\Core\Models\CoreAuthPermission;
use Brave\Core\Models\CoreAuthGroup;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * Class CoreAuthUserServiceProvider
 *
 * @package Brave\Core\Providers
 */
class CoreAuthUserServiceProvider implements UserProvider {

	/**
	 * @var CoreAuthUser
	 */
	protected $model;

	/**
	 * @param CoreAuthUser $model
	 */
	public function __construct(CoreAuthUser $model) {
		$this->model = $model;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier) {
		return $this->model->find($identifier);
	}

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @param  mixed $identifier
	 * @param  string $token
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token) {

	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  string $token
	 * @return void
	 */
	public function updateRememberToken(Authenticatable $user, $token) {
		// TODO: Implement updateRememberToken() method.
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials) {

		try {

			$user = $this->model->where('token', '=', $credentials['token'])->get();

			if (isset($user[0])) {
				return $user[0];
			}
			else {

				$api = App::make('CoreApi');
				$result = $api->core->info(array('token' => $credentials['token']));

				if (!isset($result->character->name)) {
					//TODO: What should happen if shit hits the fan?
					return false;
				}

				$user = $this->updateUser($credentials['token'], $result);

				return $user;
			}
		}
		catch (Exception $e) {
			//TODO: What should happen if shit hits the fan?
			\Log::error($e->getMessage());
			\Redirect::route('login')->with('flash_error', 'Login Failed, Please Try Again');
		}
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  array $credentials
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials) {

		if (isset($user->token) and $user->token == $credentials['token']) {
			return true;
		}

		try {
			$api = App::make('CoreApi');

			$result = $api->core->info(array('token' => $credentials['token']));

			if (!isset($result->character->name)) {
				//TODO: What should happen if shit hits the fan?
				return false;
			}

			$this->updateUser($credentials['token'], $result);
			return true;

		}
		catch (Exception $e) {
			//TODO: What should happen if shit hits the fan?
			\Log::error($e->getMessage());
			\Redirect::route('login')->with('flash_error', 'Login Failed, Please Try Again');
		}
	}

	/**
	 * @param $token
	 * @param $result
	 * @return mixed
	 */
	public function updateUser($token, $result) {

		// filter permissions and save only the relevant ones
		$namespace = str_finish(Config::get('core.application-group-base'), '.');
		$perms = $result->perms;

		// get relevant permissions
		$relevant_perms = array_filter($perms, function ($var) use ($namespace) {
			return starts_with($var, $namespace);
		});

		// check for existing user data or create a blank model if none exists
		$user = CoreAuthUser::findOrNew($result->character->id);

		// set the base user data
		$user->token = $token;
		$user->status = 1;
		$user->character_name = $result->character->name;
		$user->alliance_id = $result->alliance->id;
		$user->alliance_name = $result->alliance->name;

		// save user Core Groups
		$groups = [];
		foreach ($result->tags as $group) {
			$group = CoreAuthGroup::findOrNew(['name' => $group]);
			$group->save();
			$groups[] = $group->id;
		}
		if (!empty($groups)) {
			$user->groups->sync($groups);
		}

		// Save user Core Permissions
		$permissions = [];
		foreach ($relevant_perms as $permission) {
			$perm = CoreAuthPermission::findOrNew(['name' => $permission]);
			$perm->save();
			$permissions[] = $perm->id;
		}
		if (!empty($permissions)) {
			$user->permissions->sync($permissions);
		}

		// Save full user model
		$user->save();

		return $user;
	}
}