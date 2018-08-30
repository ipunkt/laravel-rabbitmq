<?php

namespace Ipunkt\LaravelRabbitMQ\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use Ipunkt\LaravelRabbitMQ\Console\RabbitMQListenCommand;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\EventMapper\KeyToRegex;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
use Ipunkt\LaravelRabbitMQ\Logging\CreateRabbitmqLogger;
use Ipunkt\LaravelRabbitMQ\Logging\Monolog\HandlerBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ChannelBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ConnectionBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ExchangeBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\QueueBuilder;
use Symfony\Component\EventDispatcher\Event;

class LaravelRabbitMQServiceProvider extends ServiceProvider {
	/**
	 * you have to set the package path to the absolute root path of your package like __DIR__ . '/../../' within
	 * your Service Provider;
	 *
	 * @var string
	 */
	protected $packagePath = __DIR__ . '/../../';

	public function register() {
		$this->mergeConfigFrom(
			$this->packagePath( 'config/config.php' ), 'laravel-rabbitmq'
		);

		$this->app->bind( ConfigManager::class, function () {
			$configManager = new ConfigManager();

			$configManager->parse( config( 'laravel-rabbitmq' ) );

			return $configManager;
		} );

		$this->app->bind( CreateRabbitmqLogger::class, function () {
			$builder = $this->app->make( HandlerBuilder::class );

			/**
			 * @var ConfigManager $configManager
			 */
			$configManager = $this->app->make(ConfigManager::class);

			$loggingConfig = $configManager->getLogging();

			return new CreateRabbitmqLogger( $builder, $loggingConfig->getExchangeIdentifier(), $loggingConfig->getExtraContext() );
		} );

		if ( $this->app->runningInConsole() ) {
			$this->publishes( [
				$this->packagePath( 'config/config.php' ) => config_path( 'laravel-rabbitmq.php' ),
			], 'laravel-rabbitmq-config' );


			$this->app->bind( EventMapper::class, function () {

				return new EventMapper( app( KeyToRegex::class ), $this->app->make(ConfigManager::class) );

			} );

			$this->app->singleton( RabbitMQListenCommand::class, function ( Application $app ) {
				return new RabbitMQListenCommand(
					$app->make( EventMapper::class ), $app->make( ConnectionBuilder::class ),
					$app->make(ChannelBuilder::class), $app->make( ExchangeBuilder::class ),
					$app->make(QueueBuilder::class),
					$app->make(ConfigManager::class), $app->make( 'log' ) );
			} );

			$this->commands( RabbitMQListenCommand::class );
		}
	}

	public function boot() {
		/**
		 * Logging is no longer enabled here.
		 * To enable logging use the following config:
		 *
		 * 'rabbitmq' => [
		 *   'driver' => 'custom',
         *   'via' => 'Ipunkt\LaravelRabbitMQ\Logging\CreateRabbitmqLogger',
		 * ]
		 */

		$this->registerSentryLogger();
	}

	private function registerSentryLogger(  ) {

		if ( !config('laravel-rabbitmq.logging.sentry', false) )
			return;

		/**
		 * @var Dispatcher $event
		 */
		$event = app(\Illuminate\Contracts\Events\Dispatcher::class;
		$event->listen(ExceptionInRabbitMQEvent::class, function(ExceptionInRabbitMQEvent $e) {
			if ( !app()->bound( 'sentry' ) )
				return;

			app( 'sentry' )->captureException( $e->getException() );
		});
	}

	/**
	 * give relative path from package root and return absolute path
	 *
	 * @param string $relativePath
	 *
	 * @return string
	 */
	private function packagePath( string $relativePath ): string {
		$packagePath = rtrim( str_replace( '/', DIRECTORY_SEPARATOR, $this->packagePath ), DIRECTORY_SEPARATOR );
		$relativePath = ltrim( str_replace( '/', DIRECTORY_SEPARATOR, $relativePath ), DIRECTORY_SEPARATOR );
		return realpath( $packagePath . DIRECTORY_SEPARATOR . $relativePath );
	}
}