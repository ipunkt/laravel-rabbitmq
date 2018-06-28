<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ;

use Ipunkt\LaravelRabbitMQ\MessageCounter;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;

/**
 * Class ChannelManager
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
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
	 * @var RabbitMQExchangeBuilder
	 */
	private $exchangeBuilder;

	/**
	 * ChannelManager constructor.
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 */
	public function __construct( RabbitMQExchangeBuilder $exchangeBuilder ) {
		$this->exchangeBuilder = $exchangeBuilder;
	}

	/**
	 * @param $queueIdentifier
	 * @return MessageCounter
	 */
	public function getMessageCounter( $queueIdentifier ): MessageCounter {

		if ( !array_key_exists( $queueIdentifier, $this->messageCounters ) ) {

			$messageCounter = new MessageCounter( $queueIdentifier );

			$channel = $this->exchangeBuilder->buildChannel( $queueIdentifier );
			$this->exchangeBuilder->build( $queueIdentifier );

			$messageCounter->setChannel( $channel );
			$this->messageCounters[$queueIdentifier] = $messageCounter;

		}

		$messageCounter = $this->messageCounters[$queueIdentifier];
		if ( $messageCounter->getCounter() > $this->messagesPerConnection ) {

			$messageCounter->getChannel()->close();

			$channel = $this->exchangeBuilder->buildChannel( $queueIdentifier );
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