<?php namespace Ipunkt\LaravelRabbitMQ\Events;

use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class ExceptionInRabbitMQEvent
 */
class ExceptionInRabbitMQEvent {

	use Dispatchable;

	/**
	 * @var \Throwable
	 */
	protected $exception;

	public function __construct( \Throwable $exception) {
		$this->exception = $exception;
	}

	/**
	 * @return \Throwable
	 */
	public function getException() {
		return $this->exception;
	}

}