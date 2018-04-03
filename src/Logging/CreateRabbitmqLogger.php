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
	 * @var null
	 */
	private $extraContext;

	/**
	 * CreateRabbitmqLogger constructor.
	 * @param HandlerBuilder $handlerBuilder
	 * @param $queueIdentifier
	 * @param $exchangeName
	 * @param null $extraContext
	 */
	public function __construct( HandlerBuilder $handlerBuilder, $queueIdentifier, $exchangeName, $extraContext = null) {
		if($extraContext === null)
			$extraContext = [];

		$this->handlerBuilder = $handlerBuilder;
		$this->queueIdentifier = $queueIdentifier;
		$this->exchangeName = $exchangeName;
		$this->extraContext = $extraContext;
	}

	    /**
     * Create a custom Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function __invoke() {
	    return $this->handlerBuilder->buildHandler($this->queueIdentifier, $this->exchangeName, $this->extraContext);
    }

}