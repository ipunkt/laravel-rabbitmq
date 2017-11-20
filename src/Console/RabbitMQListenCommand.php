<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQListenCommand extends Command
{
	protected $signature = 'rabbitmq:listen
							{ queue : Queue name to listen on }
							';
	protected $description = 'Listens on RabbitMQ queues and maps to laravel events';

	public function handle()
	{
		$queueIdentifier = $this->argument('queue');

		if (config('laravel-rabbitmq.' . $queueIdentifier) === null) {
			throw new \InvalidArgumentException('No queue ' . $queueIdentifier . ' configured');
		}

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

		list($queue_name, ,) = $channel->queue_declare('', false, false, true, false);

		$binding_keys = config('laravel-rabbitmq.' . $queueIdentifier . '.bindings', []);
		foreach ($binding_keys as $binding_key => $event) {
			$channel->queue_bind($queue_name, config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange'),
				$binding_key);
		}

		$callback = function ($msg) {
			$event = config('laravel-rabbitmq.' . $queueIdentifier . '.bindings.' . $msg->delivery_info['routing_key']);

			if ($event !== null) {
				event(new $event($msg->body));
			}
		};

		$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

		while (count($channel->callbacks)) {
			$channel->wait();
		}

		$channel->close();
		$connection->close();
	}
}