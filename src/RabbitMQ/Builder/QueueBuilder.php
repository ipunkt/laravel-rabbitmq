<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder;

use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class QueueBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 */
class QueueBuilder {
	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * QueueBuilder constructor.
	 * @param ConfigManager $configManager
	 */
	public function __construct( ConfigManager $configManager ) {
		$this->configManager = $configManager;
	}

	/**
	 * @param $queueIdentifier
	 * @param AMQPChannel $channel
	 * @return string
	 */
	public function buildQueue( $queueIdentifier, AMQPChannel $channel ) {

		$queueConfig = $this->configManager->getQueue( $queueIdentifier );

		list( $queueName, , ) = $channel->queue_declare(
			$queueConfig->getName(),
			$queueConfig->isPassive(),
			$queueConfig->isDurable(),
			$queueConfig->isExclusive(),
			$queueConfig->isAutoDelete() );

		return $queueName;
	}

}