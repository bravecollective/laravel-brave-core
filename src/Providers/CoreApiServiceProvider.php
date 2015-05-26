<?php namespace Brave\Core\Providers;

use Brave\API;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class CoreApiServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (!extension_loaded('gmp')) {
			die('PHP Extension "GMP" must be installed with PHP to use BRAVE Core Auth.');
		}

		// Specify GMP Requirements
		if (!defined('USE_MATH_EXT')) {
			define('USE_MATH_EXT', 'GMP');
		}

		$this->publishes([
			__DIR__ . '/../Config/bravecore.php' => config_path('bravecore.php'),
		]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Use a singleton here, we only need one instance of the api object
		$this->app->bind('CoreApi', function($app){
			$config = $app['config']->get('bravecore');
			return new API(
				$config['application-endpoint'],
				$config['application-identifier'],
				$config['local-private-key'],
				$config['remote-public-key']
			);
		});

	}

	public function provides(){
		return ['CoreApi'];
	}

}
