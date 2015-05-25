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
	protected $auth_user_model;

	/**
	 * @var CoreAuthPermission
	 */
	protected $auth_permission_model;

	/**
	 * @var CoreAuthGroup
	 */
	protected $auth_group_model;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var CoreAuthGroup
	 */
	protected $debug;

	/**
	 * @param CoreAuthUser       $auth_user_model
	 * @param CoreAuthPermission $auth_permission_model
	 * @param CoreAuthGroup      $auth_group_model
	 * @param \Config             $config
	 */
	public function __construct(\Config $config, CoreAuthUser $auth_user_model, CoreAuthPermission $auth_permission_model, CoreAuthGroup $auth_group_model) {
		$this->config = $config;
		$this->debug = $this->config->get('app.debug');

		$this->auth_user_model = $auth_user_model;
		$this->auth_permission_model = $auth_permission_model;
		$this->auth_group_model = $auth_group_model;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier) {
		return $this->auth_user_model->find($identifier);
	}

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @param  mixed $identifier
	 * @param  string $token
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token) {
		$user = $this->auth_user_model->where('id', '=', $identifier)->where('remember_token', '=', $token)->first();
		if (isset($user[0])) {
			return $user[0];
		}
		else {
			return null;
		}
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  string $token
	 * @return void
	 */
	public function updateRememberToken(Authenticatable $user, $token) {
		$user->remember_token = $token;
		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials) {

		try {

			$user = $this->auth_user_model->where('token', '=', $credentials['token'])->get();

			if (isset($user[0])) {
				return $user[0];
			}
			else {

				$api = App::make('CoreApi');
				$result = $api->core->info(['token' => $credentials['token']]);

				if (!isset($result->character->name)) {
					\Log::error('CORE Lookup for token('.$credentials['token'].') failed.');
					\Redirect::route('login')->with('flash_error', 'Core Lookup Failed, Please Try Logging in Again');
					return false;
				}

				$user = $this->updateUser($credentials['token'], $result);

				return $user;
			}
		}
		catch (Exception $e) {
			//TODO: What should happen if shit hits the fan?
			\Log::error($e->getMessage());
			\Redirect::route('login')->with('flash_error', 'Login Check Failed, Please Try Again...');
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

		//
		if (isset($user->token) and $user->token === $credentials['token']) {
			return true;
		}

		try {
			$api = App::make('CoreApi');

			$result = $api->core->info(['token' => $credentials['token']]);

			if (!isset($result->character->name)) {
				\Log::error('CORE Lookup for token('.$credentials['token'].') failed.');
				\Redirect::route('login')->with('flash_error', 'Core Lookup Failed, Please Try Logging in Again');
				return false;
			}

			$this->updateUser($credentials['token'], $result);
			return true;

		}
		catch (Exception $e) {
			\Log::error($e->getMessage());
			\Redirect::route('login')->with('flash_error', 'Login Validation Failed, Please Try Again');
		}
	}

	/**
	 * @param $token
	 * @param $result
	 * @return mixed
	 */
	public function updateUser($token, $result) {

		if ($this->debug) {
			\Log::info('Processing API Update for Character "'.$result->character->name.'('.$result->character->id.')"');
		}

		// filter permissions and save only the relevant ones
		$namespace = str_finish($this->config->get('bravecore.application-group-base'), '.');
		$permission_list = $result->perms;

		// get core group memberships
		$groups = $result->tags;

		// get granted permissions, reduce to app specific permissions
		$granted_permissions = array_filter($permission_list, function ($var) use ($namespace) {
			return starts_with($var, $namespace);
		});

		// check for existing user data or create a new model if none exists
		$user = $this->auth_user_model->firstOrCreate(['id' => $result->character->id]);

		$alliance_id = '';
		$alliance_name = '';
		try {
			$alliance_id = $result->alliance->id;
			$alliance_name = $result->alliance->name;
		}
		catch(\Exception $e) {}

		// set the base user data
		$user->token = $token;
		$user->status = 1;
		$user->character_name = $result->character->name;
		$user->corporation_id = $result->corporation->id;
		$user->corporation_name = $result->corporation->name;
		$user->alliance_id = $alliance_id;
		$user->alliance_name = $alliance_name;

		// save basic char details, so we have a model ID if this is a new model
		$user->save();

		// save user Core Groups
		$user_groups = [];
		foreach ($groups as $group) {
			if ($this->debug) {
				\Log::info('Processing Group "'.$group.'" for user "'.$result->character->name.'"');
			}
			$groupObj = $this->auth_group_model->firstOrCreate(['name' => $group]);
			$user_groups[] = $groupObj->id;
		}
		// Sync group pivot table
		$user->groups()->sync($user_groups);

		// Save user Core Permissions
		$user_permissions = [];
		foreach ($granted_permissions as $perm) {
			if ($this->debug) {
				\Log::info('Processing Permission "'.$perm.'" for user "'.$result->character->name.'"');
			}
			$permObj = $this->auth_permission_model->firstOrCreate(['name' => $perm]);
			$user_permissions[] = $permObj->id;
		}
		// Sync permission pivot table
		$user->permissions()->sync($user_permissions);

		// Save full user model
		$user->save();

		// finished updating user
		return $user;
	}
}