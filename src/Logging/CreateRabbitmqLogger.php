<?php namespace Ipunkt\LaravelRabbitMQ\Logging;

use Ipunkt\LaravelRabbitMQ\Logging\Monolog\HandlerBuilder;

/**
 * Class CreateRabbitmqLogger
 */
class CreateRabbitmqLogger {

	/**
	 * @var HandlerBuilder
	 */
	protected $handlerBuilder;

	/**
	 * @var string
	 */
	protected $exchangeIdentifier = '';

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
	 * @param $exchangeIdentfier
	 * @param $exchangeName
	 * @param null $extraContext
	 */
	public function __construct( HandlerBuilder $handlerBuilder, $exchangeIdentfier, $extraContext = null) {
		if($extraContext === null)
			$extraContext = [];

		$this->handlerBuilder = $handlerBuilder;
		$this->exchangeIdentifier = $exchangeIdentfier;
		$this->extraContext = $extraContext;
	}

	/**
     * Create a custom Monolog instance.
     *
	 * @return Monolog\AmqpHandlerWithExtraContext
     */
    public function __invoke() {
	    return $this->handlerBuilder->buildHandler($this->exchangeIdentifier, $this->extraContext);
    }

}