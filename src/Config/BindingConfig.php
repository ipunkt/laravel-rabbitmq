<?php namespace Ipunkt\LaravelRabbitMQ\Config;

/**
 * Class BindingConfig
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class BindingConfig {

	/**
	 * @var string
	 */
	protected $queueIdentifier = '';

	/**
	 * @var string
	 */
	protected $exchangeIdentifier = '';

	/**
	 * @var string
	 */
	protected $routingKey = '';

	/**
	 * @var string
	 */
	protected $eventClasspath = '';

	/**
	 * @return string
	 */
	public function getQueueIdentifier(): string {
		return $this->queueIdentifier;
	}

	/**
	 * @param string $queueIdentifier
	 * @return BindingConfig
	 */
	public function setQueueIdentifier( string $queueIdentifier ): BindingConfig {
		$this->queueIdentifier = $queueIdentifier;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExchangeIdentifier(): string {
		return $this->exchangeIdentifier;
	}

	/**
	 * @param string $exchangeIdentifier
	 * @return BindingConfig
	 */
	public function setExchangeIdentifier( string $exchangeIdentifier ): BindingConfig {
		$this->exchangeIdentifier = $exchangeIdentifier;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRoutingKey(): string {
		return $this->routingKey;
	}

	/**
	 * @param string $routingKey
	 * @return BindingConfig
	 */
	public function setRoutingKey( string $routingKey ): BindingConfig {
		$this->routingKey = $routingKey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEventClasspath(): string {
		return $this->eventClasspath;
	}

	/**
	 * @param string $eventClasspath
	 * @return BindingConfig
	 */
	public function setEventClasspath( string $eventClasspath ): BindingConfig {
		$this->eventClasspath = $eventClasspath;
		return $this;
	}


}