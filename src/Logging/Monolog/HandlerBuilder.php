<?php namespace Ipunkt\LaravelRabbitMQ\Logging\Monolog;

use Illuminate\Contracts\Logging\Log;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use Monolog\Handler\AmqpHandler;

/**
 * Class HandlerBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog
 */
class HandlerBuilder {
	/**
	 * @var RabbitMQExchangeBuilder
	 */
	private $exchangeBuilder;
	/**
	 * @var Log
	 */
	private $log;

	/**
	 * HandlerBuilder constructor.
	 * @param RabbitMQExchangeBuilder $exchangeBuilder
	 * @param Log $log
	 */
	public function __construct( RabbitMQExchangeBuilder $exchangeBuilder, Log $log) {
		$this->exchangeBuilder = $exchangeBuilder;
		$this->log = $log;
	}

	/**
	 * @param $configurationName
	 */
	public function buildHandler($configurationName, $exchangeName) {
		$channel = $this->exchangeBuilder->buildChannel($configurationName);

		$this->exchangeBuilder->build($configurationName, true);

		$handler = new AmqpHandler($channel, $exchangeName);

		return $handler;
	}

}