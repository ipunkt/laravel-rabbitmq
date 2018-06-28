<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ExchangeBuilder;

/**
 * Class ChannelManager
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 *
 * This object manages channels for the RabbitMQ object.
 * It is a shared storage of queueIdentifier -> connection and it manages them by recreating channels once the
 * counter for `messagesPerConnection` is exceeded.
 * This is necessary because message ids are 4 byte unsigned int and thus cannot exceep 65k. RabbitMQ does not automatically
 * wrap around to start again at 0
 */
class ChannelManager {

	/**
	 * Message limit on connections is 65535 (4 byte unsigned int) because ids are unsigned int and are not reused
	 *
	 * @var int
	 */
	protected $messagesPerConnection = 60000;

	/**
	 * @var MessageCounter[]
	 */
	protected $messageCounters = [];
	/**
	 * @var ExchangeBuilder
	 */
	private $exchangeBuilder;

	/**
	 * ChannelManager constructor.
	 * @param ExchangeBuilder $exchangeBuilder
	 */
	public function __construct( ExchangeBuilder $exchangeBuilder ) {
		$this->exchangeBuilder = $exchangeBuilder;
	}

	/**
	 * @param $exchangeIdentifier
	 * @return MessageCounter
	 */
	public function getMessageCounter( string $exchangeIdentifier ): MessageCounter {

		if ( !array_key_exists( $exchangeIdentifier, $this->messageCounters ) ) {

			$messageCounter = new MessageCounter( $exchangeIdentifier );

			$channel = $this->exchangeBuilder->buildChannel( $exchangeIdentifier );
			$this->exchangeBuilder->buildExchange( $exchangeIdentifier, $channel );

			$messageCounter->setChannel( $channel );
			$this->messageCounters[$exchangeIdentifier] = $messageCounter;

		}

		$messageCounter = $this->messageCounters[$exchangeIdentifier];
		if ( $messageCounter->getCounter() > $this->messagesPerConnection ) {

			$messageCounter->getChannel()->close();

			$channel = $this->exchangeBuilder->buildChannel( $exchangeIdentifier );
			$messageCounter->setChannel( $channel );
		}

		return $messageCounter;
	}

	/**
	 * @param int $messagesPerConnection
	 * @return ChannelManager
	 */
	public function setMessagesPerConnection( int $messagesPerConnection ) {
		$this->messagesPerConnection = $messagesPerConnection;
		return $this;
	}
}