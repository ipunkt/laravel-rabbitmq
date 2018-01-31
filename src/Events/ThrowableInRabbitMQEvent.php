<?php namespace Ipunkt\LaravelRabbitMQ\Events;

/**
 * Class ThrowableInRabbitMQEvent
 */
class ThrowableInRabbitMQEvent {

	use \Illuminate\Foundation\Bus\Dispatchable;

	/**
	 * @var \Exception
	 */
	protected $exception;

	public function __construct(\Throwable $exception) {
		$this->exception = $exception;
	}

	/**
	 * @return \Throwable
	 */
	public function getException() {
		return $this->exception;
	}

}