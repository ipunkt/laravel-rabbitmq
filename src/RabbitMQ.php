<?php

namespace Ipunkt\LaravelRabbitMQ;

use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\ChannelManager;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 * @package Ipunkt\LaravelRabbitMQ
 */
class RabbitMQ {

	/**
	 * Event Data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * @var string
	 */
	protected $exchange = 'default-exchange';

	/**
	 * Whether or not this message is durable
	 *
	 * @var bool
	 */
	private $durable = true;

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
	 * @var ConfigManager
	 */
	private $configManager;


	/**
	 * RabbitMQ constructor.
	 * @property ChannelManager
	 * @param ChannelManager $channelManager
	 * @param ConfigManager $configManager
	 */
	public function __construct( ChannelManager $channelManager, ConfigManager $configManager ) {
		$this->channelManager = $channelManager;
		$this->configManager = $configManager;
	}

	/**
	 * @param self $base
	 */
	protected static function copy( self $base ) {
		$new = new self( $base->channelManager, $base->configManager );

		$new->data = $base->data;
		$new->exchange = $base->exchange;

		return $new;
	}

	public function data( array $data ): self {
		$new = self::copy( $this );

		$new->data = $data;

		return $new;
	}

	/**
	 * @param string $queue
	 * @return RabbitMQ
	 * @deprecated
	 */
	public function onQueue( string $queue ): self {
		$new = self::copy( $this );

		$new->exchange = $queue;

		return $new;
	}

	/**
	 * @param string $exchangeIdentifier
	 * @return RabbitMQ
	 */
	public function onExchange( string $exchangeIdentifier ): self {
		$new = self::copy( $this );

		$new->exchange = $exchangeIdentifier;

		return $new;
	}

	/**
	 * publishes message
	 *
	 * @param string $routingKey
	 * @return RabbitMQ
	 */
	public function publish( string $routingKey ): self {
		$exchangeIdentifier = $this->exchange;

		$config = $this->configManager->getExchange($exchangeIdentifier);

		$messageCounter = $this->channelManager->getMessageCounter( $config->getIdentifier() );

		$properties = [];
		if ( $this->durable )
			$properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

		$msg = new AMQPMessage( json_encode( $this->data ), $properties );

		$messageCounter->getChannel()->basic_publish(
			$msg,
			$config->getName(),
			$routingKey
		);

		$messageCounter->increaseCounter();

		return $this;
	}


	/**
	 * @param $extraData
	 * @return RabbitMQ
	 */
	public function append( array $extraData ) {
		$new = self::copy($this);

		$new->data = array_merge( $extraData, $this->data );

		return $new;
	}

	/**
	 * @return string
	 */
	public function getExchange() {
		return $this->exchange;
	}

	/**
	 * @return bool
	 */
	public function isDurable(): bool {
		return $this->durable;
	}

	/**
	 * @param bool $durable
	 * @return RabbitMQ
	 */
	public function durable( bool $durable ) {
		$new = self::copy($this);

		$new->durable = $durable;

		return $new;
	}
}