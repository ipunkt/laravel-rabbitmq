<?php namespace Ipunkt\LaravelRabbitMQ\Config;

/**
 * Class LoggingConfig
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class LoggingConfig {

	/**
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * @var string
	 */
	protected $exchangeIdentifier = '';

	/**
	 * @var bool
	 */
	protected $throwEvents = false;

	/**
	 * @var array
	 */
	protected $extraContext = [];

	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->enabled;
	}

	/**
	 * @param bool $enabled
	 * @return LoggingConfig
	 */
	public function setEnabled( bool $enabled ): LoggingConfig {
		$this->enabled = $enabled;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExchangeIdentifier(): string {
		return $this->exchangeIdentifier;
	}

	/**
	 * @param string $exchangeIdentifier
	 * @return LoggingConfig
	 */
	public function setExchangeIdentifier( string $exchangeIdentifier ): LoggingConfig {
		$this->exchangeIdentifier = $exchangeIdentifier;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isThrowEvents(): bool {
		return $this->throwEvents;
	}

	/**
	 * @param bool $throwEvents
	 * @return LoggingConfig
	 */
	public function setThrowEvents( bool $throwEvents ): LoggingConfig {
		$this->throwEvents = $throwEvents;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getExtraContext(): array {
		return $this->extraContext;
	}

	/**
	 * @param array $extraContext
	 * @return LoggingConfig
	 */
	public function setExtraContext( array $extraContext ): LoggingConfig {
		$this->extraContext = $extraContext;
		return $this;
	}



}