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
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

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
	                             ConfigManager $configManager, LogManager $logger ) {
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
		while(true) {
			$queueIdentifier = $this->argument( 'queue' );

			$queueConfig = $this->configManager->getQueue( $queueIdentifier );

			$connection = $this->connectionBuilder->buildConnection( $queueConfig->getConnectionIdentifier() );
			$channel = $this->channelBuilder->buildChannel( $connection );

			// Build all exchanges
			foreach ( $queueConfig->getBindings() as $exchangeIdentifier => $bindings )
				$this->exchangeBuilder->buildExchange( $exchangeIdentifier, $channel );

			$queueName = $this->queueBuilder->buildQueue( $queueIdentifier, $channel );

			foreach ( $queueConfig->getBindings() as $exchangeIdentifier => $bindings ) {
				$exchangeConfig = $this->configManager->getExchange( $exchangeIdentifier );

				foreach ( $bindings as $binding )
					$channel->queue_bind( $queueName, $exchangeConfig->getName(), $binding->getRoutingKey() );
			}

			$callback = function ( $msg ) use ( $queueIdentifier ) {

				// Crude way of preventing system overload when only erroring messages are left in a queue
				if ( $msg->delivery_info['redelivered'] )
					sleep( 1 );

				/**
				 * @var AMQPMessage $msg
				 * @var AMQPChannel $msgChannel
				 */
				$msgChannel = $msg->delivery_info['channel'];

				$routingKey = $msg->delivery_info['routing_key'];

				/**
				 * Use the header `routing_key` instead if the message was directly delivered to this queue(as happens when
				 * redelivering messages to the back of the queue)
				 */
				try {
					$headers = $msg->get( 'application_headers' )->getNativeData();
					$routingKey = array_get( $headers, 'routing_key', $routingKey );
				} catch ( \OutOfBoundsException $e ) {
					//
				}

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


					} catch ( AQPIOException $e ) {
						throw $e;
					} catch ( \Throwable $e ) {

						$this->handleException( $queueIdentifier, $msg, $e );

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
				} catch ( AMQPIOException $e ) {
					$connection->close();
					usleep(100);
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
	}

	/**
	 * @param $queueIdentifier
	 * @param AMQPMessage $msg
	 * @param \Exception|\Throwable $e
	 */
	protected function handleException( $queueIdentifier, $msg, $e ) {

		$loggingConfig = $this->configManager->getLogging();

		if ( $loggingConfig->isEnabled() ) {

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

		if ( $loggingConfig->isThrowEvents() )
			event( new ExceptionInRabbitMQEvent( $e ) );

		/**
		 * @var AMQPChannel $msgChannel
		 */
		$msgChannel = $msg->delivery_info['channel'];

		/**
		 * Unless the message should be dropped send it again on the same queue, so it will appear at its end
		 * Doing an nack with requeue=true here is sadly not enough as is pushes the message to the front of the queue
		 * and thus one error in the php code will halt the entire queue
		 *
		 * Resending and then nacking possibly leaves us open to having the message twice in the queue but I have
		 * not yet found a way to do this operation atomically
		 */
		if ( !$e instanceof DropsEvent ) {
			$redeliverMessage = new AMQPMessage( $msg->getBody(), $msg->get_properties() );
			$headers = new AMQPTable( [
				'exchange' => $msg->delivery_info['exchange'],
				'routing_key' => $msg->delivery_info['routing_key'],
			] );
			$redeliverMessage->set( 'application_headers', $headers );

			$queueConfig = $this->configManager->getQueue( $queueIdentifier );
			$msgChannel->basic_publish( $redeliverMessage, '', $queueConfig->getName() );
		}

		$msgChannel->basic_reject( $msg->delivery_info['delivery_tag'], false );
		return;
	}
}
