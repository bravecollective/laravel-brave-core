<?php namespace Brave\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Brave\Core\Models\CoreAuthUser;
use Brave\Core\Providers\CoreAuthUserServiceProvider;
use Illuminate\Auth\Guard;
use Illuminate\Auth\AuthManager;


class CoreAuthServiceProvider extends ServiceProvider {

	protected $defer = false;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		
		$this->publishes([
		    __DIR__.'/../Database/Migrations/' => base_path('/database/migrations')
		], 'migrations');

		$this->app['auth']->extend('coreauth', function($app) {
			$provider = new CoreAuthUserServiceProvider(new CoreAuthUser());

			return new \Illuminate\Auth\Guard($provider, $app['session.store']);
		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	public function provides(){
		return ['coreauth'];
	}

}
