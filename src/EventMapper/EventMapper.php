<?php
namespace Ipunkt\LaravelRabbitMQ\EventMapper;

/**
 * Class EventMapper
 *
 * Map RabbitMQ events to Laravel events.
 * Reads from the bindings in the configuration file
 */
class EventMapper {
	/**
	 * @var array
	 */
	private $config;

	/**
	 * EventMapper constructor.
	 * @param array $config
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * @param string $queueIdentifier
	 * @param string $rabbitMQEvent
	 * @return string
	 */
	public function map( string $queueIdentifier, string $rabbitMQEvent ) {

		$events = [];

		foreach($this->config as $eventKey => $event) {
		}

		return $events;

		$bindings = array_get($this->config,$queueIdentifier.'.bindings');
		if( !is_array($bindings) )
			return null;

		if ( !array_key_exists($rabbitMQEvent, $bindings) )
			return null;

		return $bindings[$rabbitMQEvent];
	}

}