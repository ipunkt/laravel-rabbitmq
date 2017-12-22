<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQListenCommand extends Command
{
	protected $signature = 'rabbitmq:listen
							{ queue : Queue name to listen on }
							{ --declare-exchange : Declare exchange when missing }
							';
	protected $description = 'Listens on RabbitMQ queues and maps to laravel events';
	/**
	 * @var EventMapper
	 */
	private $eventMapper;

	/**
	 * RabbitMQListenCommand constructor.
	 * @param EventMapper $eventMapper
	 */
	public function __construct( EventMapper $eventMapper) {
		parent::__construct();
		$this->eventMapper = $eventMapper;
	}

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

		$exchange = config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.exchange');
		if ($this->option('declare-exchange')) {
			$channel->exchange_declare(
				$exchange,
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.type'),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.passive', false),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.durable', false),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.auto_delete', true),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.internal', false),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.nowait', false),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.arguments'),
				config('laravel-rabbitmq.' . $queueIdentifier . '.exchange.ticket')
			);
		}

		list($queue_name, ,) = $channel->queue_declare('', false, false, true, false);

		$binding_keys = config('laravel-rabbitmq.' . $queueIdentifier . '.bindings', []);
		foreach ($binding_keys as $binding_key => $event) {
			$channel->queue_bind($queue_name, $exchange,
				$binding_key);
		}

		$callback = function ($msg) use ($queueIdentifier) {
			$event = $this->eventMapper->map( $queueIdentifier, $msg->delivery_info['routing_key'] );

			if ($event !== null) {
				try {
					event(new $event(json_decode($msg->body, true)));
				} catch(\Exception $e) {
					$this->error( $e->getFile().":".$e->getLine().' '. $e->getMessage() );
					$this->error( $e->getCode() );
					event( new ExceptionInRabbitMQEvent($e) );
				}
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