<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog;

use Illuminate\Contracts\Logging\Log;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\RabbitMQExchangeBuilder;
use Monolog\Handler\AmqpHandler;
use Monolog\Logger;

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
		/**
		 * @var Logger $monolog
		 *
		 */
		$monolog = $this->log->getMonolog();
		$monolog->pushHandler($handler);
	}

}