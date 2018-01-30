<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;

class RabbitMQListenCommand extends Command
{
	protected $signature = 'rabbitmq:listen
							{ queue : Queue name to listen on }
							{ --declare-exchange : Declare exchange when missing }';
	protected $description = 'Listens on RabbitMQ queues and maps to laravel events';
	/**
	 * @var EventMapper
	 */
	private $eventMapper;
	/**
	 * @var RabbitMQExchangeBuilder
	 */
	private $exchangeBuilder;

	/**
	 * RabbitMQListenCommand constructor.
	 * @param EventMapper $eventMapper
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 */
	public function __construct( EventMapper $eventMapper, RabbitMQExchangeBuilder $exchangeBuilder) {
		parent::__construct();
		$this->eventMapper = $eventMapper;
		$this->exchangeBuilder = $exchangeBuilder;
	}

	public function handle()
	{
		$queueIdentifier = $this->argument('queue');

		if (config('laravel-rabbitmq.' . $queueIdentifier) === null) {
			throw new \InvalidArgumentException('No queue ' . $queueIdentifier . ' configured');
		}

		$channel = $this->exchangeBuilder->buildChannel($queueIdentifier);
		$exchange = $this->exchangeBuilder->build($queueIdentifier, $this->option('declare-exchange'));

		list($queue_name, ,) = $channel->queue_declare(config('laravel-rabbitmq.' . $queueIdentifier . '.name', ''), false, config('laravel-rabbitmq.' . $queueIdentifier . '.durable', false), false, false);

		$binding_keys = config('laravel-rabbitmq.' . $queueIdentifier . '.bindings', []);
		foreach ($binding_keys as $binding_key => $event) {
			$channel->queue_bind($queue_name, $exchange,
				$binding_key);
		}

		$callback = function ($msg) use ($queueIdentifier) {
			$event = $this->eventMapper->map( $queueIdentifier, $msg->delivery_info['routing_key'] );

			if($event === null && config('laravel-rabbitmq.' . $queueIdentifier . '.durable', false) )
				$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);

			if ($event !== null) {
				try {
					$success = event(new $event(json_decode($msg->body, true)));

					if( config('laravel-rabbitmq.' . $queueIdentifier . '.durable', false) ) {
						if($success === true)
							$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
						else if($success === false)
							$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
					}

				} catch(\Throwable $e) {
					$this->error( $e->getFile().":".$e->getLine().' '. $e->getMessage() );
					$this->error( $e->getCode() );
					event( new ExceptionInRabbitMQEvent($e) );

					/**
					 * Do not ack or nack the message - message will only be redelivered after a restart(-> version change)
					 */
				} catch(\Exception $e) {
					$this->error( $e->getFile().":".$e->getLine().' '. $e->getMessage() );
					$this->error( $e->getCode() );
					event( new ExceptionInRabbitMQEvent($e) );

					/**
					 * Do not ack or nack the message - message will only be redelivered after a restart(-> version change)
					 */
				}
			}
		};

		$channel->basic_consume($queue_name, '', false, config('laravel-rabbitmq.' . $queueIdentifier . '.durable', false), false, false, $callback);

		while (count($channel->callbacks)) {
			$channel->wait();
		}

		$channel->close();
		$this->exchangeBuilder->closeConnection($queueIdentifier);
	}
}
