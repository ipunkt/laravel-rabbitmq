<?php namespace Ipunkt\LaravelRabbitMQ\Events;

/**
 * Class RabbitMQJobFinishedEvent
 * @package Ipunkt\LaravelRabbitMQ\Events
 */
class RabbitMQJobFinishedEvent
{
	/**
	 * @var string
	 */
	private $routingKey;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * RabbitMQJobFinishedEvent constructor.
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
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

}