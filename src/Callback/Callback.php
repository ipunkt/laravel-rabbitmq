<?php namespace Ipunkt\LaravelRabbitMQ\Callback;

use Ipunkt\LaravelRabbitMQ\RabbitMQ;

/**
 * Class Callback
 * @package Ipunkt\LaravelRabbitMQ\Callback
 */
class Callback {
	/**
	 * @var RabbitMQ
	 */
	private $rabbitMQ;

	/**
	 *
	 *
	 * @var string
	 */
	protected $callbackPrefix = 'callback-';

	/**
	 * Time to wait until
	 *
	 * @var int
	 */
	protected $timeout = 30;

	/**
	 * Callback constructor.
	 * @param RabbitMQ $rabbitMQ
	 */
	public function __construct( RabbitMQ $rabbitMQ ) {
		$this->rabbitMQ = $rabbitMQ;
	}

	/**
	 * @param self $other
	 * @return Callback
	 */
	protected static function copy( self $other ): self {
		$new = new self( $other->rabbitMQ );

		$new->callbackPrefix = $other->callbackPrefix;
		$new->timeout = $other->timeout;

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
	 * @param \Closure $callback
	 */
	public function callback( \Closure $callback ) {
		$callbackName = $this->callbackPrefix . md5( getmypid() . random_bytes(10) );

	}
}