<?php

namespace Ipunkt\LaravelRabbitMQ;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
	protected $data;

	protected $queue = 'default';

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
	 * Return from event handler to indicate the message was successfully processed
	 */
	const TAKEN = true;

	/**
	 * Return from an event handler to indicate the message was not processed by this handler
	 */
	const IGNORED = false;

	/**
	 * RabbitMQ constructor.
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 */
	public function __construct( RabbitMQExchangeBuilder $exchangeBuilder) {
		$this->exchangeBuilder = $exchangeBuilder;
	}

	public function data(array $data) : self
	{
		$this->data = $data;

		return $this;
	}

	public function onQueue(string $queue) : self
	{
		$this->queue = $queue;

		return $this;
	}

	/**
	 * publishes message
	 *
	 * @param string $routingKey
	 * @return RabbitMQ
	 */
	public function publish(string $routingKey) : self
	{
		$queueIdentifier = $this->queue;

		$messageCounter = $this->getMessageCounter( $queueIdentifier );

		$properties = [];
		if( config('laravel-rabbitmq.queues' . $queueIdentifier . '.durable') )
			$properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

		$msg = new AMQPMessage(json_encode($this->data), $properties);

		$messageCounter->getChannel()->basic_publish(
			$msg,
			config('laravel-rabbitmq.queues.' . $queueIdentifier . '.exchange.exchange'),
			$routingKey
		);

		$messageCounter->increaseCounter();

		return $this;
	}

	/**
	 * @param $queueIdentifier
	 * @return MessageCounter
	 */
	protected function getMessageCounter( $queueIdentifier ) : MessageCounter {

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
}