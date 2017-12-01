<?php
namespace Ipunkt\LaravelRabbitMQ\EventMapper;

/**
 * Class EventMapper
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
	 * @param string $rabbitMQEvent
	 * @return string
	 */
	public function map( string $queueIdentifier, string $rabbitMQEvent ) {
		$bindings = array_get($this->config,$queueIdentifier.'.bindings');
		if( !is_array($bindings) )
			return null;

		if ( !array_key_exists($rabbitMQEvent, $bindings) )
			return null;

		return $bindings[$rabbitMQEvent];
	}

}