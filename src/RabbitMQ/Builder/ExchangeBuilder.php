<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder;

use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class ExchangeBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 */
class ExchangeBuilder {
	/**
	 * @var ConfigManager
	 */
	private $configManager;
	/**
	 * @var ConnectionBuilder
	 */
	private $connectionBuilder;
	/**
	 * @var ChannelBuilder
	 */
	private $channelBuilder;

	/**
	 * ExchangeBuilder constructor.
	 * @param ConfigManager $configManager
	 * @param ConnectionBuilder $connectionBuilder
	 * @param ChannelBuilder $channelBuilder
	 */
	public function __construct( ConfigManager $configManager, ConnectionBuilder $connectionBuilder, ChannelBuilder $channelBuilder ) {
		$this->configManager = $configManager;
		$this->connectionBuilder = $connectionBuilder;
		$this->channelBuilder = $channelBuilder;
	}

	/**
	 * @param string $exchangeIdentifier
	 * @return \PhpAmqpLib\Connection\AMQPStreamConnection
	 */
	public function buildConnection( string $exchangeIdentifier ) {

		$exchangeConfig = $this->configManager->getExchange( $exchangeIdentifier );
		$connection = $this->connectionBuilder->buildConnection( $exchangeConfig->getConnectionIdentifier() );

		return $connection;

	}

	/**
	 * @param string $exchangeIdentifier
	 */
	public function buildChannel( string $exchangeIdentifier ) {
		$connection = $this->buildConnection($exchangeIdentifier);

		return $this->channelBuilder->buildChannel($connection);
	}

	/**
	 * @param string $exchangeIdentifier
	 * @param AMQPChannel $channel
	 * @param bool $forceActive
	 * @param bool $nowait
	 * @return string
	 */
	public function buildExchange( string $exchangeIdentifier, AMQPChannel $channel, $forceActive = false, $nowait = false ) {
		$exchangeConfig = $this->configManager->getExchange( $exchangeIdentifier );

		$exchangeName = $exchangeConfig->getName();

		$passive = $exchangeConfig->isPassive();
		if ( $forceActive )
			$passive = false;

		$channel->exchange_declare(
			$exchangeName,
			$exchangeConfig->getType(),
			$passive,
			$exchangeConfig->isDurable(),
			$exchangeConfig->isAutoDelete(),
			$exchangeConfig->isInternal(),
			$nowait
		);

		return $exchangeName;
	}

}