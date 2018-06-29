<?php namespace Ipunkt\LaravelRabbitMQ;

use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder\ExchangeBuilder;

/**
 * Class Rpc
 * @package Ipunkt\LaravelRabbitMQ\Callback\Rpc
 *
 * Uses RabbitMQ to make a pseudo synchronous call
 */
class Rpc {
	/**
	 * @var ExchangeBuilder
	 */
	private $exchangeBuilder;
	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * RabbitMQRpc constructor.
	 * @param ExchangeBuilder $exchangeBuilder
	 * @param ConfigManager $configManager
	 */
	public function __construct( ExchangeBuilder $exchangeBuilder, ConfigManager $configManager ) {
		$this->exchangeBuilder = $exchangeBuilder;
		$this->configManager = $configManager;
	}

	/**
	 *
	 *
	 * @var string
	 */
	protected $callbackPrefix = 'callback.';

	/**
	 * Time to wait until
	 *
	 * @var int
	 */
	protected $timeout = 30;

	/**
	 * Extra data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @param self $base
	 * @return self
	 */
	protected static function copy( self $base ): self {
		$new = new self( $base->exchangeBuilder, $base->configManager );

		$new->callbackPrefix = $base->callbackPrefix;
		$new->timeout = $base->timeout;

		return $new;
	}

	/**
	 * @param int $timeout timeout in TODO insert timeout format
	 * @return self
	 */
	public function setTimeout( int $timeout ) {
		$new = self::copy( $this );

		$new->timeout = $timeout;

		return $new;
	}

	/**
	 * Set a unique prefix to ensure no overlap with any other service
	 *
	 * @param string $prefix
	 * @return self
	 */
	public function setPrefix( string $prefix ) {
		$new = self::copy( $this );

		$new->callbackPrefix = $prefix;
		return $new;
	}

	/**
	 * @param RabbitMQ $rabbitMQ
	 * @param string $routingKey
	 * @param \Closure $callback function(array $data) { ... }
	 * @return bool
	 */
	public function call( RabbitMQ $rabbitMQ, string $routingKey, \Closure $callback ) {
		$callbackName = $this->callbackPrefix . md5( getmypid() . random_bytes( 10 ) );

		$exchangeConfig = $this->configManager->getExchange( $rabbitMQ->getExchange() );

		// TODO: Building a queue with bindings should probably be abstracted away from here.
		$channel = $this->exchangeBuilder->buildChannel( $exchangeConfig->getName() );
		list( $queueName, , ) = $channel->queue_declare( '', false, false, true, true );
		$channel->queue_bind( $queueName, $exchangeConfig->getName(), $callbackName );

		$rabbitMQ->append( [
			'callback' => $callbackName,
		] )->publish( $routingKey );

		$wasCalled = false;
		$channel->basic_consume($queueName, '', false, false, false, false, function( $msg ) use (&$wasCalled, $callback) {
			$wasCalled = true;

			$data = json_decode( $msg->body, true );

			$callback($data);
		});


		$channel->wait(null, false, $this->timeout);

		return $wasCalled;
	}
}