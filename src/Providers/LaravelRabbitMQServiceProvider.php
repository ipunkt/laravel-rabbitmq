<?php

namespace Ipunkt\LaravelRabbitMQ\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\ServiceProvider;
use Ipunkt\LaravelRabbitMQ\Console\RabbitMQListenCommand;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Logging\CreateRabbitmqLogger;
use Ipunkt\LaravelRabbitMQ\Logging\Monolog\HandlerBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;

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

		$this->app->singleton( RabbitMQExchangeBuilder::class, function () {
			return new RabbitMQExchangeBuilder( config( 'laravel-rabbitmq' ) );
		} );

		$this->app->bind( CreateRabbitmqLogger::class, function () {
			$builder = $this->app->make( HandlerBuilder::class );

			$queueIdentifier = config( 'laravel-rabbitmq.logging.queue-identifier' );

			$exchangeName = config( 'laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange' );

			$extraContext = config( 'laravel-rabbitmq.' . $queueIdentifier . '.extra-context', [] );

			return new CreateRabbitmqLogger( $builder, $queueIdentifier, $exchangeName, $extraContext );
		} );

		if ( $this->app->runningInConsole() ) {
			$this->publishes( [
				$this->packagePath( 'config/config.php' ) => config_path( 'laravel-rabbitmq.php' ),
			], 'laravel-rabbitmq-config' );


			$this->app->bind( EventMapper::class, function () {

				$config = config( 'laravel-rabbitmq' );

				return new EventMapper( $config );

			} );

			$this->app->singleton( RabbitMQListenCommand::class, function ( Application $app ) {
				return new RabbitMQListenCommand( $app->make( EventMapper::class ), $app->make( RabbitMQExchangeBuilder::class ), $app->make( 'log' ) );
			} );

			$this->commands( RabbitMQListenCommand::class );
		}
	}

	public function boot() {
		$loggingEnabled = config( 'laravel-rabbitmq.logging.enable', false ) && !$this->app->environment( 'testing' );
		if ( !$loggingEnabled )
			return;

		/**
		 * @var CreateRabbitmqLogger $createRabbitmqLogger
		 */
		$createRabbitmqLogger = $this->app->make( CreateRabbitmqLogger::class );

		/**
		 * @var Log $log
		 */
		$log = $this->app->make( 'log' );

		$tries = 0;
		$success = false;
		do {

			try {
				$handler = $createRabbitmqLogger();
				$success = true;
			} catch ( \Throwable $t ) {
				$log->warning( 'Failed to open rabbitmq for logging', [
					'message' => $t->getMessage(),
					'trace' => $t->getTraceAsString(),
				] );
				++$tries;
				if ( $tries >= 5 ) {
					$log->warning( 'Giving up trying to create rabbitmq for logging', [
						'tries' => $tries,
						'message' => $t->getMessage(),
						'trace' => $t->getTraceAsString(),
					] );

					return;
				}
				sleep( 1 );
			}

		} while ( !$success );

		/**
		 * @var Monolog $monolog
		 */
		$monolog = $log->getMonolog();

		$monolog->pushHandler( $handler );
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