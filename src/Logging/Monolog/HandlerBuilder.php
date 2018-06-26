<?php namespace Ipunkt\LaravelRabbitMQ\Logging\Monolog;

use Illuminate\Log\LogManager;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;

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
	public function __construct( RabbitMQExchangeBuilder $exchangeBuilder, LogManager $log) {
		$this->exchangeBuilder = $exchangeBuilder;
		$this->log = $log;
	}

	/**
	 * @param $configurationName
	 * @param $exchangeName
	 * @param array $extraContext
	 * @return AmqpHandlerWithExtraContext
	 */
	public function buildHandler($configurationName, $exchangeName, $extraContext = []) {
		$channel = $this->exchangeBuilder->buildChannel($configurationName);

		$this->exchangeBuilder->build($configurationName, true);

		$handler = new AmqpHandlerWithExtraContext($channel, $exchangeName);
		$handler->setExtraContext($extraContext);

		return $handler;
	}

}