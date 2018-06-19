<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\Exceptions\ExchangeNotDefinedException;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class RabbitMQExchangeBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 */
class RabbitMQExchangeBuilder {
	/**
	 * @var array
	 */
	private $configuration;


	/**
	 * RabbitMQExchangeBuilder constructor.
	 * @param array $configuration
	 */
	private $configPrefix = 'queues.';

	public function __construct( array $configuration ) {
		$this->configuration = $configuration;
	}

	/**
	 * @param $configurationName
	 * @return \PhpAmqpLib\Channel\AMQPChannel
	 */
	public function buildChannel($configurationName) {

		if( !array_key_exists($configurationName, $this->configuration) || !is_array($this->configuration[$configurationName]) )
			throw new ExchangeNotDefinedException("$configurationName not defined in the configuration.");

		$connection = $this->getConnection( $configurationName );

		$channel = $connection->channel();
		$channel->basic_qos(0, 1, false);

		return $channel;
	}

	/**
	 * @param $configurationName
	 * @return string
	 */
	public function build( $configurationName, $forceActive = false ) {
		$channel = $this->buildChannel($configurationName);

		$exchangeName = array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.exchange');
		$passive = array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.passive', false );
		if ($forceActive)
			$passive = false;

		$channel->exchange_declare(
			$exchangeName,
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.type'),
			$passive,
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.durable', false),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.auto_delete', true),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.internal', false),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.nowait', false),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.arguments'),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.exchange.ticket')
		);

		return $exchangeName;
	}

	/**
	 * @param $configurationName
	 * @return AMQPStreamConnection
	 */
	protected function getConnection($configurationName) {
		return new AMQPStreamConnection(
			array_get($this->configuration, $this->configPrefix .$configurationName . '.host'),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.port', 5672),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.user'),
			array_get($this->configuration, $this->configPrefix .$configurationName . '.password')
		);
	}
}