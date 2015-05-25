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
		// Specify GMP Requirements
		if (!defined('GMP')) {
			define('USE_EXT', 'GMP');
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
