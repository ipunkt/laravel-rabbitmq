<?php

namespace Ipunkt\LaravelRabbitMQ;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ChannelManager;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 * @package Ipunkt\LaravelRabbitMQ
 */
class RabbitMQ {
	protected $data;

	protected $queue = 'default';

	/**
	 * Return from event handler to indicate the message was successfully processed
	 */
	const TAKEN = true;

	/**
	 * Return from an event handler to indicate the message was not processed by this handler
	 */
	const IGNORED = false;

	/**
	 * @var ChannelManager
	 */
	private $channelManager;

	/**
	 * RabbitMQ constructor.
	 * @property ChannelManager
	 * @param ChannelManager $channelManager
	 */
	public function __construct( ChannelManager $channelManager ) {
		$this->channelManager = $channelManager;
	}

	/**
	 * @param self $base
	 */
	protected static function copy( self $base ) {
		$new = new self( $base->channelManager );

		$new->data = $base->data;
		$new->queue = $base->queue;

		return $new;
	}

	public function data( array $data ): self {
		$new = self::copy($this);

		$new->data = $data;

		return $new;
	}

	public function onQueue( string $queue ): self {
		$new = self::copy($this);

		$new->queue = $queue;

		return $new;
	}

	/**
	 * publishes message
	 *
	 * @param string $routingKey
	 * @return RabbitMQ
	 */
	public function publish( string $routingKey ): self {
		$queueIdentifier = $this->queue;

		$messageCounter = $this->channelManager->getMessageCounter( $queueIdentifier );

		$properties = [];
		if ( config( 'laravel-rabbitmq.queues' . $queueIdentifier . '.durable' ) )
			$properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

		$msg = new AMQPMessage( json_encode( $this->data ), $properties );

		$messageCounter->getChannel()->basic_publish(
			$msg,
			config( 'laravel-rabbitmq.queues.' . $queueIdentifier . '.exchange.exchange' ),
			$routingKey
		);

		$messageCounter->increaseCounter();

		return $this;
	}


}