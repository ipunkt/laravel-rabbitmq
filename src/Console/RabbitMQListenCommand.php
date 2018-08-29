<?php

namespace Ipunkt\LaravelRabbitMQ\Console;

use Illuminate\Console\Command;
use Illuminate\Log\LogManager;
use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use Ipunkt\LaravelRabbitMQ\DropsEvent;
use Ipunkt\LaravelRabbitMQ\EventMapper\EventMapper;
use Ipunkt\LaravelRabbitMQ\Events\ExceptionInRabbitMQEvent;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ChannelBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ConnectionBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ExchangeBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\QueueBuilder;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\IsDurableChecker;
use Ipunkt\LaravelRabbitMQ\TakesRoutingKey;
use Ipunkt\LaravelRabbitMQ\TakesRoutingMatches;
use PhpAmqpLib\Channel\AMQPChannel;

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
	 * @var LogManager
	 */
	private $logger;
	/**
	 * @var ConfigManager
	 */
	private $configManager;
	/**
	 * @var ExchangeBuilder
	 */
	private $exchangeBuilder;
	/**
	 * @var ConnectionBuilder
	 */
	private $connectionBuilder;
	/**
	 * @var ChannelBuilder
	 */
	private $channelBuilder;
	/**
	 * @var QueueBuilder
	 */
	private $queueBuilder;

	/**
	 * RabbitMQListenCommand constructor.
	 * @param EventMapper $eventMapper
	 * @param ConnectionBuilder $connectionBuilder
	 * @param ChannelBuilder $channelBuilder
	 * @param ExchangeBuilder $exchangeBuilder
	 * @param QueueBuilder $queueBuilder
	 * @param IsDurableChecker $durableChecker
	 * @param ConfigManager $configManager
	 * @param LogManager $logger
	 */
	public function __construct( EventMapper $eventMapper, ConnectionBuilder $connectionBuilder,
	                             ChannelBuilder $channelBuilder, ExchangeBuilder $exchangeBuilder,
	                             QueueBuilder $queueBuilder,
	                             ConfigManager $configManager, $logger ) {
		parent::__construct();
		$this->eventMapper = $eventMapper;
		$this->logger = $logger;
		$this->configManager = $configManager;
		$this->exchangeBuilder = $exchangeBuilder;
		$this->connectionBuilder = $connectionBuilder;
		$this->channelBuilder = $channelBuilder;
		$this->queueBuilder = $queueBuilder;
	}

	public function handle() {
		$queueIdentifier = $this->argument( 'queue' );

		$queueConfig = $this->configManager->getQueue( $queueIdentifier );

		$connection = $this->connectionBuilder->buildConnection( $queueConfig->getConnectionIdentifier() );
		$channel = $this->channelBuilder->buildChannel( $connection );

		// Build all exchanges
		foreach ( $queueConfig->getBindings() as $exchangeIdentifier => $bindings )
			$this->exchangeBuilder->buildExchange( $exchangeIdentifier, $channel );

		$queueName = $this->queueBuilder->buildQueue( $queueIdentifier, $channel );

		foreach ( $queueConfig->getBindings() as $exchangeIdentifier => $bindings ) {
			$exchangeConfig = $this->configManager->getExchange($exchangeIdentifier);

			foreach($bindings as $binding)
				$channel->queue_bind( $queueName, $exchangeConfig->getName(), $binding->getRoutingKey() );
		}

		$callback = function ( $msg ) use ( $queueIdentifier ) {
			/**
			 * @var AMQPChannel $msgChannel
			 */
			$msgChannel = $msg->delivery_info['channel'];
			$routingKey = $msg->delivery_info['routing_key'];
			$eventMatches = $this->eventMapper->map( $queueIdentifier, $routingKey );

			if ( empty( $eventMatches ) ) {
				// Reject message
				$msgChannel->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
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

					$this->handleException( $msg, $e );

					return;

				}

			}

			if ( $messageStatus->takenEncountered() ) {

				// acknowledge message as having been processed
				$msgChannel->basic_ack( $msg->delivery_info['delivery_tag'] );
				return;

			}

			// Reject message
			$msgChannel->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
			return;

		};

		$channel->basic_consume( $queueName, '', false, false, false, false, $callback );

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
	 * @param $queueIdentifier
	 * @param $msg
	 * @param \Exception|\Throwable $e
	 */
	protected function handleException( $msg, $e ) {

		$loggingConfig = $this->configManager->getLogging();

		if( $loggingConfig->isEnabled() ) {

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

		if( $loggingConfig->isThrowEvents() )
			event( new ExceptionInRabbitMQEvent( $e ) );

		/**
		 * @var AMQPChannel $msgChannel
		 */
		$msgChannel = $msg->delivery_info['channel'];

		// Nack the message if the Exception indicates the event should be dropped
		if ( $e instanceof DropsEvent ) {
			$msgChannel->basic_nack( $msg->delivery_info['delivery_tag'], false, false );
			return;
		}

		/**
		 * TODO: Requeueing always returns the message to the top of the queue. We want it at the back so other messages
		 * are processed while the cause of the exception is is addressed
		 */
		// Requeue message
		$msgChannel->basic_nack( $msg->delivery_info['delivery_tag'], false, true );
		// Prevent error message spam
		sleep(30);
		return;
	}
}
