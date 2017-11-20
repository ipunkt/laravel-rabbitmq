<?php

namespace Ipunkt\LaravelRabbitMQ\Providers;

use Illuminate\Support\ServiceProvider;
use Ipunkt\LaravelRabbitMQ\Console\RabbitMQListenCommand;

class LaravelRabbitMQServiceProvider extends ServiceProvider
{
	/**
	 * you have to set the package path to the absolute root path of your package like __DIR__ . '/../../' within
	 * your Service Provider;
	 *
	 * @var string
	 */
	protected $packagePath = __DIR__ . '/../../';

	public function register()
	{
		$this->mergeConfigFrom(
			$this->packagePath('config/config.php'), 'laravel-rabbitmq'
		);

		if ($this->app->runningInConsole()) {
			$this->publishes([
				$this->packagePath('config/config.php') => config_path('laravel-rabbitmq.php'),
			], 'laravel-rabbitmq-config');

			$this->app->singleton(RabbitMQListenCommand::class, function () {
				return new RabbitMQListenCommand();
			});

			$this->commands(RabbitMQListenCommand::class);
		}
	}

	/**
	 * give relative path from package root and return absolute path
	 *
	 * @param string $relativePath
	 *
	 * @return string
	 */
	private function packagePath(string $relativePath) : string
	{
		$packagePath = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $this->packagePath), DIRECTORY_SEPARATOR);
		$relativePath = ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
		return realpath($packagePath . DIRECTORY_SEPARATOR . $relativePath);
	}
}