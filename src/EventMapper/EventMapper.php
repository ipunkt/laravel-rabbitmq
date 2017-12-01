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
		if ( !array_key_exists($queueIdentifier, $this->config) )
			return null;

		if ( !array_key_exists($rabbitMQEvent, $this->config[$queueIdentifier]) )
			return null;

		return $this->config[$queueIdentifier][$rabbitMQEvent];
	}

}