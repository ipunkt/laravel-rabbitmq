<?php namespace Ipunkt\LaravelRabbitMQ\Events;

/**
 * Class RabbitMQJobStartedEvent
 * @package Ipunkt\LaravelRabbitMQ\Events
 */
class RabbitMQJobStartedEvent
{
	/**
	 * @var string
	 */
	private $routingKey;

	/**
	 * @var
	 */
	private $data;

	/**
	 * RabbitMQJobStartedEvent constructor.
	 * @param string $routingKey
	 * @param $data
	 */
	public function __construct(string $routingKey, $data) {
		$this->routingKey = $routingKey;
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getRoutingKey(): string
	{
		return $this->routingKey;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

}