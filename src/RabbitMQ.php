<?php

namespace Ipunkt\LaravelRabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
	protected $data;

	protected $queue = 'default';

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

		$connection = new AMQPStreamConnection(
			config('laravel-rabbitmq.' . $queueIdentifier . '.host'),
			config('laravel-rabbitmq.' . $queueIdentifier . '.port', 5672),
			config('laravel-rabbitmq.' . $queueIdentifier . '.user'),
			config('laravel-rabbitmq.' . $queueIdentifier . '.password')
		);
		$channel = $connection->channel();

		$channel->exchange_declare(
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange'),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.type'),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.passive', false),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.durable', false),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.auto_delete', true),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.internal', false),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.nowait', false),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.arguments'),
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.ticket')
		);

		$msg = new AMQPMessage($this->data);

		$channel->basic_publish(
			$msg,
			config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange'),
			$routingKey
		);

		$channel->close();
		$connection->close();
	}
}