<?php namespace Ipunkt\LaravelRabbitMQ\Config;

use Ipunkt\LaravelRabbitMQ\Config\Exceptions\ConfigNotFoundException;

/**
 * Class ConfigManager
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class ConfigManager {

	/**
	 * @var ConnectionConfig []
	 */
	protected $connections = [];

	/**
	 * @var ExchangeConfig[]
	 */
	protected $exchanges = [];

	/**
	 * @var QueueConfig[]
	 */
	protected $queues = [];

	/**
	 * @var LoggingConfig
	 */
	protected $logging;

	/**
	 * ConfigManager constructor.
	 */
	public function __construct() {
		$this->logging = new LoggingConfig();
	}

	/**
	 * @param array $config
	 */
	public function parse( array $config ) {

		$this->parseConnection( $config );

		$this->parseExchanges( $config );

		$this->parseQueues( $config );

		$this->parseLogging( $config );
	}


	/**
	 * @param array $config
	 */
	protected function parseConnection( array $config ) {
		$connections = array_get( $config, 'connections' );

		$this->connections = [];
		/**
		 *
		 */
		collect( $connections )->each( function ( array $configuration, string $identifier ) {
			$connection = new ConnectionConfig();

			$connection->setIdentifier( $identifier );
			$connection->setHost( array_get( $configuration, 'host', 'rabbitmq' ) );
			$connection->setPort( array_get( $configuration, 'port', 5672 ) );
			$connection->setUser( array_get( $configuration, 'user', 'guest' ) );
			$connection->setPassword( array_get( $configuration, 'password', 'guest' ) );

			$this->connections[$identifier] = $connection;
		} );
	}

	/**
	 * @param array $config
	 */
	private function parseExchanges( array $config ) {
		$exchanges = array_get( $config, 'exchanges' );

		$this->exchanges = [];
		/**
		 *
		 */
		collect( $exchanges )->each( function ( array $configuration, string $identifier ) {
			$exchange = new ExchangeConfig();

			$exchange->setIdentifier( $identifier );
			$exchange->setConnectionIdentifier( array_get( $configuration, 'connection', '' ) );
			$exchange->setName( array_get( $configuration, 'name', '' ) );
			$exchange->setPassive( array_get( $configuration, 'passive', false ) );
			$exchange->setAutoDelete( array_get( $configuration, 'auto_delete', false ) );
			$exchange->setDurable( array_get( $configuration, 'durable', true ) );
			$exchange->setInternal( array_get( $configuration, 'internal', false ) );

			$this->exchanges[$identifier] = $exchange;
		} );
	}

	private function parseQueues( array $config ) {
		$queues = array_get( $config, 'queues' );

		$this->queues = [];
		/**
		 *
		 */
		collect( $queues )->each( function ( array $configuration, string $identifier ) {
			$queue = new QueueConfig();

			$queue->setIdentifier( $identifier );
			$queue->setConnectionIdentifier( array_get( $configuration, 'connection', '' ) );
			$queue->setName( array_get( $configuration, 'name', '' ) );
			$queue->setPassive( array_get( $configuration, 'passive', false ) );
			$queue->setAutoDelete( array_get( $configuration, 'auto_delete', false ) );
			$queue->setDurable( array_get( $configuration, 'durable', true ) );

			$bindings = [];
			collect( array_get( $configuration, 'bindings', [] ) )->each( function ( array $bindingConfigs, $exchangeIdentifier ) use ( $queue, &$bindings ) {

				$bindings[$exchangeIdentifier] = [];

				collect( $bindingConfigs )->each( function ( string $event, string $routingKey ) use ( $exchangeIdentifier, $queue, &$bindings ) {

					$binding = new BindingConfig();

					$binding->setExchangeIdentifier( $exchangeIdentifier );
					$binding->setQueueIdentifier( $queue->getIdentifier() );
					$binding->setRoutingKey( $routingKey );
					$binding->setEventClasspath( $event );

					$bindings[$exchangeIdentifier][] = $binding;
				} );

			} );
			$queue->setBindings( $bindings );

			$this->exchanges[$identifier] = $queue;
		} );
	}

	private function parseLogging( array $fullConfig ) {

		$config = array_get( $fullConfig, 'logging', [] );
		$logging = new LoggingConfig();

		$logging->setEnabled( array_get( $config, 'enable', false ) );
		$logging->setExchangeIdentifier( array_get( $config, 'exchange', '' ) );
		$logging->setThrowEvents( array_get( $config, 'event-errors', false ) );
		$logging->setExtraContext( array_get( $config, 'extra-context', [] ) );

		$this->logging = $logging;

	}

	/**
	 * @param string $connectionIdentifier
	 * @return ConnectionConfig
	 */
	public function getConnection( string $connectionIdentifier ) : ConnectionConfig {
		if ( !array_key_exists( $connectionIdentifier, $this->queues ) )
			throw new ConfigNotFoundException( "connections $connectionIdentifier not found" );

		return $this->connections[$connectionIdentifier];
	}

	/**
	 * @param string $queueIdentifier
	 * @return QueueConfig
	 */
	public function getQueue( string $queueIdentifier ) : QueueConfig {
		if ( !array_key_exists( $queueIdentifier, $this->queues ) )
			throw new ConfigNotFoundException( "queue $queueIdentifier not found" );

		return $this->queues[$queueIdentifier];
	}

	/**
	 * @param string $exchangeIdentifier
	 * @return ExchangeConfig
	 */
	public function getExchange( string $exchangeIdentifier ) : ExchangeConfig {
		if ( !array_key_exists( $exchangeIdentifier, $this->exchanges ) )
			throw new ConfigNotFoundException( "exchange $exchangeIdentifier not found" );

		return $this->exchanges[$exchangeIdentifier];
	}

	/**
	 * @return LoggingConfig
	 */
	public function getLogging() {
		return $this->logging;
	}
}