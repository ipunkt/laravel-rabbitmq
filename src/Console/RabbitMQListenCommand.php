<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use Illuminate\Log\LogManager;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
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
	 * @var LogManager
	 */
	private $logger;

	/**
	 * RabbitMQListenCommand constructor.
	 * @param EventMapper $eventMapper
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 * @param LogManager $logger
	 */
	public function __construct( EventMapper $eventMapper, RabbitMQExchangeBuilder $exchangeBuilder, LogManager $logger ) {
		parent::__construct();
		$this->eventMapper = $eventMapper;
		$this->exchangeBuilder = $exchangeBuilder;
		$this->logger = $logger;
	}

	public function handle() {
		$queueIdentifier = $this->argument( 'queue' );

		if ( config( 'laravel-rabbitmq.queues.' . $queueIdentifier ) === null ) {
			throw new \InvalidArgumentException( 'No queue ' . $queueIdentifier . ' configured' );
		}

		$channel = $this->exchangeBuilder->buildChannel( $queueIdentifier );
		$exchange = $this->exchangeBuilder->build( $queueIdentifier, $this->option( 'declare-exchange' ) );

		list( $queue_name, , ) = $channel->queue_declare( config( 'laravel-rabbitmq.queues.' . $queueIdentifier . '.name', '' ), false, $this->isDurable($queueIdentifier), false, false );

		$binding_keys = config( 'laravel-rabbitmq.queues.' . $queueIdentifier . '.bindings', [] );
		foreach ( $binding_keys as $binding_key => $event ) {
			$channel->queue_bind( $queue_name, $exchange,
				$binding_key );
		}

		$callback = function ( $msg ) use ( $queueIdentifier ) {
			$routingKey = $msg->delivery_info['routing_key'];
			$eventMatches = $this->eventMapper->map( $queueIdentifier, $routingKey );

			if ( empty( $eventMatches ) && $this->isDurable($queueIdentifier) ) {
				// Reject message
				$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
				return;
			}

			$messageStatus = new MessageStatus();
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

					$messageStatus->addEventReturns( event( $eventObject ) );


				} catch ( \Throwable $e ) {

					$this->handleException($queueIdentifier, $msg, $e);

					return;

				} catch ( \Exception $e ) {
					$this->handleException($queueIdentifier, $msg, $e);

					return;

				}

			}

			if ( $this->isDurable($queueIdentifier) ) {

				if( $messageStatus->takenEncountered() ) {

					// acknowledge message as having been processed
					$msg->delivery_info['channel']->basic_ack( $msg->delivery_info['delivery_tag'] );
					return;

				}

				// Reject message
				$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
				return;
			}

		};

		$channel->basic_consume( $queue_name, '', false, !$this->isDurable($queueIdentifier), false, false, $callback );

		while ( count( $channel->callbacks ) ) {
			try {
				$channel->wait();
			} catch ( \Throwable $e ) {

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

	/**
	 * @param string $queue
	 * @return bool
	 */
	protected function isDurable($queueIdentifier) {
		return (bool)config( 'laravel-rabbitmq.queues.' . $queueIdentifier . '.durable', false );
	}

	/**
	 * @param $queueIdentifier
	 * @param $msg
	 * @param \Exception|\Throwable $e
	 */
	protected function handleException( $queueIdentifier, $msg, $e ) {

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

		// Nack the message if the Exception indicates the event should be dropped
		if( $this->isDurable($queueIdentifier) && $e instanceof DropsEvent) {
			$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
			return;
		}

		// Requeue message
		if( $this->isDurable($queueIdentifier) ) {
			$msg->delivery_info['channel']->basic_nack( $msg->delivery_info['delivery_tag'], false, true );
			return;
		}

	}
}
