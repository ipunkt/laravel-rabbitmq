<?php namespace Ipunkt\LaravelRabbitMQ\EventMapper;

/**
 * Class EventMatch
 * @package Ipunkt\LaravelRabbitMQ\EventMapper
 */
class EventMatch {

	/**
	 * @var string
	 */
	protected $eventClass;

	/**
	 * @var array
	 */
	protected $matchedPlaceholders = [];

	/**
	 * @return string
	 */
	public function getEventClass(): string {
		return $this->eventClass;
	}

	/**
	 * @param string $eventClass
	 * @return EventMatch
	 */
	public function setEventClass( string $eventClass ): EventMatch {
		$this->eventClass = $eventClass;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMatchedPlaceholders(): array {
		return $this->matchedPlaceholders;
	}

	/**
	 * @param array $matchedPlaceholders
	 * @return EventMatch
	 */
	public function setMatchedPlaceholders( array $matchedPlaceholders ): EventMatch {
		$this->matchedPlaceholders = $matchedPlaceholders;
		return $this;
	}

}