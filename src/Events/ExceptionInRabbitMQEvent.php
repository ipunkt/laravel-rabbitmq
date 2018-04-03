<?php namespace Ipunkt\LaravelRabbitMQ\Events;

use Exception;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class ExceptionInRabbitMQEvent
 */
class ExceptionInRabbitMQEvent {

	use Dispatchable;

	/**
	 * @var Exception
	 */
	protected $exception;

	public function __construct( Exception $exception) {
		$this->exception = $exception;
	}

	/**
	 * @return Exception
	 */
	public function getException() {
		return $this->exception;
	}

}