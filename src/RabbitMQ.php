<?php

namespace Ipunkt\LaravelRabbitMQ;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
	protected $data;

	protected $queue = 'default';
	/**
	 * @var RabbitMQExchangeBuilder
	 */
	private $exchangeBuilder;

	/**
	 * RabbitMQ constructor.
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 */
	public function __construct( RabbitMQExchangeBuilder $exchangeBuilder) {
		$this->exchangeBuilder = $exchangeBuilder;
	}

	public function data(array $data) : self
	{
		$this->data = $data;

		return $this;
	}

	public function onQueue(string $queue) : self
	{
		$this->queue = $queue;

		return $this;
	}

	/**
	 * publishes message
	 *
	 * @param string $routingKey
	 * @return RabbitMQ
	 */
	public function publish(string $routingKey) : self
	{
		$queueIdentifier = $this->queue;

		$channel = $this->exchangeBuilder->buildChannel($this->queue);
		$this->exchangeBuilder->build($this->queue);

		$properties = [];
		if( config('laravel-rabbitmq.' . $queueIdentifier . '.durable') )
			$properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

		$msg = new AMQPMessage(json_encode($this->data), $properties);

		$channel->basic_publish(
			$msg,
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange'),
			$routingKey
		);

		$channel->close();

		return $this;
	}
}