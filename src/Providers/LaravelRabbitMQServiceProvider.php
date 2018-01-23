<?php

namespace Ipunkt\LaravelRabbitMQ\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Ipunkt\LaravelRabbitMQ\Console\RabbitMQListenCommand;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog\HandlerBuilder;
use Monolog\Handler\AmqpHandler;

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


			$this->app->bind( EventMapper::class, function() {

				$config = config('laravel-rabbitmq');

				return new EventMapper($config);

			});

			$this->app->singleton(RabbitMQExchangeBuilder::class, function() {
				return new RabbitMQExchangeBuilder( config('laravel-rabbitmq') );
			});

			$this->app->singleton(RabbitMQListenCommand::class, function (Application $app) {
				return new RabbitMQListenCommand( $app->make(EventMapper::class), $app->make(RabbitMQExchangeBuilder::class) );
			});


			$this->commands(RabbitMQListenCommand::class);
		}
	}

	/**
	 *
	 */
	public function boot() {
		$loggingEnabled = config('laralvel-rabbittmq.logging.enable', false);

		if(!$loggingEnabled)
			return;

		$queueIdentifier = config('laralvel-rabbittmq.logging.queue-identifier');

		$exchangeName = config('laralvel-rabbittmq.'.$queueIdentifier.'.exchange.exchange');
		/**
		 * @var HandlerBuilder $handlerBuilder
		 */
		$handlerBuilder = $this->app->make(HandlerBuilder::class);
		$handler = $handlerBuilder->buildHandler($queueIdentifier);

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