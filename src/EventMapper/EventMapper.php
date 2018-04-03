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
	 * @var KeyToRegex
	 */
	private $keyToRegex;

	/**
	 * EventMapper constructor.
	 * @param KeyToRegex $keyToRegex
	 * @param array $config
	 */
	public function __construct( KeyToRegex $keyToRegex, array $config ) {
		$this->config = $config;
		$this->keyToRegex = $keyToRegex;
	}

	/**
	 * @param string $queueIdentifier
	 * @param string $rabbitMQEvent
	 * @return string[]
	 */
	public function map( string $queueIdentifier, string $rabbitMQEvent ) {

		$bindings = array_get($this->config,$queueIdentifier.'.bindings');

		if( !is_array($bindings) )
			return [];

		$events = [];

		foreach($bindings as $eventKey => $event) {
			$regex = $this->keyToRegex->toRegex($eventKey);
			if( preg_match($regex, $rabbitMQEvent) === 1)
				$events[] = $event;
		}

		return $events;
	}

}