Laravel 5 Brave Collective Core Services Integration
===================
[![Laravel](https://img.shields.io/badge/Laravel-5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](https://img.shields.io/badge/source-laravel--brave--core-blue.svg?style=flat-square)](https://github.com/necrotex/laravel-brave-core)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)


Documentation
-------------
soon

Quick Installation
------------------
Begin by installing the package through Composer. The best way to do this is through your terminal via Composer itself:

```composer require necrotex/laravel-brave-core```

Once this operation is complete, simply add the service provider classes to your project's `config/app.php` file.

After that please ensure to run `php artisan vendor:publish` and edit the config/bravecore.php file to your needs.

#### Service Providers

```
'Brave\Core\Providers\CoreApiServiceProvider',
'Brave\Core\Providers\CoreAuthServiceProvider',
```

#### Core Auth
Edit the config/auth.php file:

* Set the driver to `coreauth`
* Set the model to `Brave\Core\Model\CoreAuthUser`
* Set table to `core_auth_users`
* Run the database migration 

Usage
------------------
Create an API Object with `App::make('CoreApi')`.