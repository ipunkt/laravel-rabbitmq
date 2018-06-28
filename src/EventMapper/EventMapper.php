<?php namespace Ipunkt\LaravelRabbitMQ\EventMapper;

use Ipunkt\LaravelRabbitMQ\Config\BindingConfig;
use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;

/**
 * Class EventMapper
 *
 * Map RabbitMQ events to Laravel events.
 * Reads from the bindings in the configuration file
 */
class EventMapper {
	/**
	 * @var KeyToRegex
	 */
	private $keyToRegex;
	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * EventMapper constructor.
	 * @param KeyToRegex $keyToRegex
	 * @param ConfigManager $configManager
	 */
	public function __construct( KeyToRegex $keyToRegex, ConfigManager $configManager ) {
		$this->keyToRegex = $keyToRegex;
		$this->configManager = $configManager;
	}

	/**
	 * @param string $queueIdentifier
	 * @param string $rabbitMQEvent
	 * @return EventMatch[]
	 */
	public function map( string $queueIdentifier, string $rabbitMQEvent ) {

		$queue = $this->configManager->getQueue( $queueIdentifier );
		$boundExchanges = $queue->getBindings();

		if ( !is_array( $boundExchanges ) )
			return [];

		$events = [];

		foreach ( $boundExchanges as $exchangeIdentifier => $bindings ) {

			/**
			 * @var BindingConfig[] $bindings
			 */
			foreach ( $bindings as $binding ) {
				$regex = $this->keyToRegex->toRegex( $binding->getRoutingKey() );
				$matches = [];
				if ( preg_match( $regex, $rabbitMQEvent, $matches ) === 1 ) {
					$match = new EventMatch();
					$match->setEventClass( $binding->getEventClasspath() );
					$match->setMatchedPlaceholders( $matches );
					$events[] = $match;
				}
			}
		}

		return $events;
	}

}