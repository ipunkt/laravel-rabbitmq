<?php namespace Ipunkt\LaravelRabbitMQ\Logging\Monolog;

use Monolog\Handler\AmqpHandler;

/**
 * Class AmqpHandlerWithExtraContext
 * @package Ipunkt\LaravelRabbitMQ\Logging\Monolog
 */
class AmqpHandlerWithExtraContext extends AmqpHandler {

	/**
	 * @var array
	 */
	protected $extraContext = [];

	/**
	 *
	 */
	public function addGeneralContext($fieldName, $value) {
		$this->extraContext[$fieldName] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(array $record) {
		$context = array_get($record, 'context');

		if( is_array($context) ) {
			$context = array_merge($context, $this->extraContext);
			array_set($record, 'context', $context);
		}

		return parent::handle($record);
	}

	/**
	 * @return array
	 */
	public function getExtraContext(): array {
		return $this->extraContext;
	}

	/**
	 * @param array $extraContext
	 */
	public function setExtraContext( array $extraContext ) {
		$this->extraContext = $extraContext;
	}

}