<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Logging\Log;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
use Ipunkt\LaravelRabbitMQ\Events\ThrowableInRabbitMQEvent;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use Ipunkt\LaravelRabbitMQ\TakesRoutingKey;
use Ipunkt\LaravelRabbitMQ\TakesRoutingMatches;

class RabbitMQListenCommand extends Command {
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
	 * @var Log
	 */
	private $logger;

	/**
	 * RabbitMQListenCommand constructor.
	 * @param EventMapper $eventMapper
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 * @param Log $logger
	 */
	public function __construct( EventMapper $eventMapper, RabbitMQExchangeBuilder $exchangeBuilder, Log $logger ) {
		parent::__construct();
		$this->eventMapper = $eventMapper;
		$this->exchangeBuilder = $exchangeBuilder;
		$this->logger = $logger;
	}

	public function handle() {
		$queueIdentifier = $this->argument( 'queue' );

		if ( config( 'laravel-rabbitmq.' . $queueIdentifier ) === null ) {
			throw new \InvalidArgumentException( 'No queue ' . $queueIdentifier . ' configured' );
		}

		$channel = $this->exchangeBuilder->buildChannel( $queueIdentifier );
		$exchange = $this->exchangeBuilder->build( $queueIdentifier, $this->option( 'declare-exchange' ) );

		list( $queue_name, , ) = $channel->queue_declare( config( 'laravel-rabbitmq.' . $queueIdentifier . '.name', '' ), false, config( 'laravel-rabbitmq.' . $queueIdentifier . '.durable', false ), false, false );

		$binding_keys = config( 'laravel-rabbitmq.' . $queueIdentifier . '.bindings', [] );
		foreach ( $binding_keys as $binding_key => $event ) {
			$channel->queue_bind( $queue_name, $exchange,
				$binding_key );
		}

		$callback = function ( $msg ) use ( $queueIdentifier ) {
			$routingKey = $msg->delivery_info['routing_key'];
			$eventMatches = $this->eventMapper->map( $queueIdentifier, $routingKey );

			if ( empty( $eventObjects ) && config( 'laravel-rabbitmq.' . $queueIdentifier . '.durable', false ) )
				$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'] );

			$successess = [];
			foreach ( $eventMatches as $eventMatch ) {
				$event = $eventMatch->getEventClass();

				try {
					if ( $event[0] !== '\\' )
						$event = '\\' . $event;

					$eventObject = new $event( json_decode( $msg->body, true ) );
					if ( $eventObject instanceof TakesRoutingKey )
						$eventObject->setRoutingKey( $routingKey );
					if ( $eventObject instanceof TakesRoutingMatches )
						$eventObject->setRoutingMatches( $eventMatch->getMatchedPlaceholders() );
					$success = event( $eventObject );
					// No EventHandlers found - message does not concern this event handler

					// An EventHandler has successfully processed the message - mark done
					if ( in_array( true, $success ) )
						$successess[] = true;
					// An EventHandler has marked the message as does not concern us
					else if ( in_array( false, $success ) )
						$successess[] = false;
					else
						$successess[] = 'other';


				} catch ( \Throwable $e ) {
					if ( config( 'laravel-rabbitmq.logging.eventerrors', true ) ) {

						$this->logger->alert( 'Throwable in Rabbitmq eventhandler', [
							'message' => $e->getMessage(),
							'throwable' => $e,
							'trace' => $e->getTrace(),
							'traceString' => $e->getTraceAsString(),
						] );

					}

					$this->error( $e->getFile() . ":" . $e->getLine() . ' ' . $e->getMessage() );
					$this->error( $e->getCode() );
					$this->error( $e->getTraceAsString() );
					event( new ThrowableInRabbitMQEvent( $e ) );

					/**
					 * Do not ack or nack the message - message will only be redelivered after a restart(-> version change)
					 */
				} catch ( \Exception $e ) {
					if ( config( 'laravel-rabbitmq.logging.event-errors', true ) ) {

						$this->logger->alert( 'Exception in Rabbitmq eventhandler', [
							'message' => $e->getMessage(),
							'exception' => $e,
							'trace' => $e->getTraceAsString(),
							'traceString' => $e->getTraceAsString(),
						] );

					}

					$this->error( $e->getFile() . ":" . $e->getLine() . ' ' . $e->getMessage() );
					$this->error( $e->getCode() );
					$this->error( $e->getTraceAsString() );
					event( new ExceptionInRabbitMQEvent( $e ) );

					/**
					 * Do not ack or nack the message - message will only be redelivered after a restart(-> version change)
					 */
				}

			}

			if ( config( 'laravel-rabbitmq.' . $queueIdentifier . '.durable', false ) ) {
				// No EventHandlers took the message - message does not concern us
				if ( empty( $successess ) )
					$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'] );
				// An EventHandler has successfully processed the message - mark done
				else if ( in_array( true, $success ) )
					$msg->delivery_info['channel']->basic_ack( $msg->delivery_info['delivery_tag'] );
				// An EventHandler has marked the message as does not concern us
				else if ( in_array( false, $success ) )
					$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'] );


				/**
				 * No Event Handler marked the emssage as processed or does not concern, but there was an event handler
				 * which took the message
				 *
				 * Message will not be acknowledged. This will cause the message to return to the queue once
				 * this process exits.
				 * This behaviour is choosen here for development purposes - test your code with the same message
				 * over and over by not returning true at the end of the handler.
				 */
			}
		};

		$channel->basic_consume( $queue_name, '', false, !config( 'laravel-rabbitmq.' . $queueIdentifier . '.durable', false ), false, false, $callback );

		while ( count( $channel->callbacks ) ) {
			try {
				$channel->wait();
			} catch ( \ErrorException $e ) {

				if ( config( 'laravel-rabbitmq.logging.event-errors', true ) ) {

					$this->logger->alert( 'Exception in Rabbitmq wait', [
						'message' => $e->getMessage(),
						'exception' => $e,
					] );

				}

				$this->error( $e->getFile() . ":" . $e->getLine() . ' ' . $e->getMessage() );
				$this->error( $e->getCode() );
			}
		}

		$channel->close();
	}
}
