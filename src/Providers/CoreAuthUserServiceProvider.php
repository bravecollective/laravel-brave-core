<?php namespace Brave\Core\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Brave\Core\Models\CoreAuthUser;
use Brave\API;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class CoreAuthUserServiceProvider implements UserProvider {

	protected $model;

	public function __construct(CoreAuthUser $model){
		$this->model = $model;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier) {
		return $this->model->find($identifier);
	}

	/**
	 * Retrieve a user by by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string $token
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token) {

	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  string                                     $token
	 *
	 * @return void
	 */
	public function updateRememberToken(Authenticatable $user, $token) {
		// TODO: Implement updateRememberToken() method.
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials) {

		try{

			$user = $this->model->where('token', '=', $credentials['token'])->get();

			if(isset($user[0])){
				return $user[0];
			} else {
				$api = App::make('CoreApi');

				try {
					$result = $api->core->info(array('token' => $credentials['token']));
				} catch(Exception $e){
					//TODO: What should happen if shit hits the fan?
					dd($e->getMessage());
				}

				if(!isset($result->character->name)){
					//TODO: What should happen if shit hits the fan?
					dd($e->getMessage());
				}

				$user = $this->updateUser($credentials['token'], $result);

				return $user;
			}
		}
		catch(Exception $e) {
			//TODO: What should happen if shit hits the fan?
			dd($e->getMessage());
		}
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  array                                      $credentials
	 *
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials) {

		if(isset($user->token) and $user->token == $credentials['token']){
			return true;
		}

		try {
			$api = App::make('CoreApi');

			$result = $api->core->info(array('token' => $credentials['token']));

			if(!isset($result->character->name)) {
				//TODO: What should happen if shit hits the fan?
				dd($e->getMessage());
			}

			$this->updateUser($credentials['token'], $result);
			return true;

		} catch(Exception $e) {
			//TODO: What should happen if shit hits the fan?
			dd($e->getMessage());
		}
	}

	public function updateUser($token, $result){

		// filter permissions and save only the relevant ones
		$namespace = Config::get('core.application-group-base');
		$perms = $result->perms;

		$relevant_perms = array_filter($perms, function($var) use ($namespace) {
			return starts_with($var, $namespace);
		});

		$relevant_perms = serialize($relevant_perms);

		// check for existing user
		$user = CoreAuthUser::find($result->character->id);

		if($user == false)
		{
			// no user found, create it
			$user = CoreAuthUser::create(
				array(
					'id' => $result->character->id,
					'token' => $token,
					'remember_token' => '',
					'character_name' => $result->character->name,
					'alliance_id' => $result->alliance->id,
					'alliance_name' => $result->alliance->name,
					'permission' => $relevant_perms,
					'tags' => serialize($result->tags),
				)
			);
		}
		else
		{
			// update the existing user
			$user->token = $token;
			$user->character_name = $result->character->name;
			$user->alliance_id = $result->alliance->id;
			$user->alliance_name = $result->alliance->name;
			$user->permission = $relevant_perms;
			$user->tags = serialize($result->tags);

			$user->save();
		}

		return $user;
	}
}