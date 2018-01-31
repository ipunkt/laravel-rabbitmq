<?php namespace Ipunkt\LaravelRabbitMQ\Logging;

use Ipunkt\LaravelRabbitMQ\Logging\Monolog\HandlerBuilder;

/**
 * Class CreateRabbitmqLogger
 */
class CreateRabbitmqLogger {

	/**
	 * @var \Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog\HandlerBuilder
	 */
	protected $handlerBuilder;

	/**
	 * @var string
	 */
	protected $queueIdentifier = '';

	/**
	 * @var string
	 */
	protected $exchangeName = '';

	/**
	 * CreateRabbitmqLogger constructor.
	 * @param HandlerBuilder $handlerBuilder
	 */
	public function __construct( HandlerBuilder $handlerBuilder, $queueIdentifier, $exchangeName) {
		$this->handlerBuilder = $handlerBuilder;
		$this->queueIdentifier = $queueIdentifier;
		$this->exchangeName = $exchangeName;
	}

	    /**
     * Create a custom Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function __invoke() {
	    return $this->handlerBuilder->buildHandler($this->queueIdentifier, $this->exchangeName);
    }

}