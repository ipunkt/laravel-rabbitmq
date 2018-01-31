<?php namespace Ipunkt\LaravelRabbitMQ\Logging;

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
	 * @param \Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog\HandlerBuilder $handlerBuilder
	 */
	public function __construct(\Ipunkt\LaravelRabbitMQ\RabbitMQ\Monolog\HandlerBuilder $handlerBuilder, $queueIdentifier, $exchangeName) {
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