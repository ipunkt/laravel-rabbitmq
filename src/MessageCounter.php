<?php namespace Ipunkt\LaravelRabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class MessageCounter
 * @package Ipunkt\LaravelRabbitMQ
 */
class MessageCounter {

	/**
	 * @var string
	 */
	protected $queueName;

	/**
	 * @var AMQPChannel
	 */
	protected $channel;

	/**
	 * @var int
	 */
	protected $counter = 0;

	/**
	 * MessageCounter constructor.
	 * @param string $queueName
	 */
	public function __construct(string $queueName) {
		$this->queueName = $queueName;
	}

	/**
	 * @return string
	 */
	public function getQueueName(): string {
		return $this->queueName;
	}

	/**
	 * @param string $queueName
	 * @return MessageCounter
	 */
	public function setQueueName( string $queueName ): MessageCounter {
		$this->queueName = $queueName;
		return $this;
	}

	/**
	 * @return AMQPChannel
	 */
	public function getChannel() {
		return $this->channel;
	}

	/**
	 * @param AMQPChannel $channel
	 * @return MessageCounter
	 */
	public function setChannel( AMQPChannel $channel ): MessageCounter {
		$this->channel = $channel;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCounter(): int {
		return $this->counter;
	}

	/**
	 * @param int $counter
	 * @return MessageCounter
	 */
	public function setCounter( int $counter ): MessageCounter {
		$this->counter = $counter;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function increaseCounter() {
		++$this->counter;
		return $this;
	}


}