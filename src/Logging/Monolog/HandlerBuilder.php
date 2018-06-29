<?php namespace Ipunkt\LaravelRabbitMQ\Logging\Monolog;

use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ExchangeBuilder;

/**
 * Class HandlerBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog
 */
class HandlerBuilder {
	/**
	 * @var ExchangeBuilder
	 */
	private $exchangeBuilder;

	/**
	 * HandlerBuilder constructor.
	 * @param ExchangeBuilder $exchangeBuilder
	 */
	public function __construct( ExchangeBuilder $exchangeBuilder) {
		$this->exchangeBuilder = $exchangeBuilder;
	}

	/**
	 * @param $exchangeIdentifier
	 * @param $exchangeName
	 * @param array $extraContext
	 * @return AmqpHandlerWithExtraContext
	 */
	public function buildHandler( $exchangeIdentifier, $extraContext = []) {
		$channel = $this->exchangeBuilder->buildChannel($exchangeIdentifier);

		$exchangeName = $this->exchangeBuilder->buildExchange($exchangeIdentifier, $channel);

		$handler = new AmqpHandlerWithExtraContext($channel, $exchangeName);
		$handler->setExtraContext($extraContext);

		return $handler;
	}

}