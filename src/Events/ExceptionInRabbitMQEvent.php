<?php namespace Ipunkt\LaravelRabbitMQ\Events;

/**
 * Class ExceptionInRabbitMQEvent
 */
class ExceptionInRabbitMQEvent {

	use \Illuminate\Foundation\Bus\Dispatchable;

	/**
	 * @var \Exception
	 */
	protected $exception;

	public function __construct(\Exception $exception) {
		$this->exception = $exception;
	}

	/**
	 * @return Exception
	 */
	public function getException() {
		return $this->exception;
	}

}