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
	protected $connectionName = '';

	/**
	 * @var string
	 */
	protected $exchangeName = '';

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
	public function getConnectionName(): string {
		return $this->connectionName;
	}

	/**
	 * @param string $connectionName
	 * @return LoggingConfig
	 */
	public function setConnectionName( string $connectionName ): LoggingConfig {
		$this->connectionName = $connectionName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExchangeName(): string {
		return $this->exchangeName;
	}

	/**
	 * @param string $exchangeName
	 * @return LoggingConfig
	 */
	public function setExchangeName( string $exchangeName ): LoggingConfig {
		$this->exchangeName = $exchangeName;
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