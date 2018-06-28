<?php namespace Ipunkt\LaravelRabbitMQ\Config;

/**
 * Class QueueConfig
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class QueueConfig {

	/**
	 * @var string
	 */
	protected $identifier = '';

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var string
	 */
	protected $connectionIdentifier = '';

	/**
	 * @var bool
	 */
	protected $durable = true;

	/**
	 * @var bool
	 */
	protected $passive = false;

	/**
	 * @var bool
	 */
	protected $exclusive = false;

	/**
	 * @var bool
	 */
	protected $autoDelete = false;

	/**
	 * @var BindingConfig[][]
	 */
	protected $bindings = [];

	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 */
	public function setIdentifier( string $identifier ) {
		$this->identifier = $identifier;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName( string $name ) {
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function isDurable(): bool {
		return $this->durable;
	}

	/**
	 * @param bool $durable
	 */
	public function setDurable( bool $durable ) {
		$this->durable = $durable;
	}

	/**
	 * @return bool
	 */
	public function isPassive(): bool {
		return $this->passive;
	}

	/**
	 * @param bool $passive
	 */
	public function setPassive( bool $passive ) {
		$this->passive = $passive;
	}

	/**
	 * @return bool
	 */
	public function isExclusive(): bool {
		return $this->exclusive;
	}

	/**
	 * @param bool $exclusive
	 */
	public function setExclusive( bool $exclusive ) {
		$this->exclusive = $exclusive;
	}

	/**
	 * @return bool
	 */
	public function isAutoDelete(): bool {
		return $this->autoDelete;
	}

	/**
	 * @param bool $autoDelete
	 */
	public function setAutoDelete( bool $autoDelete ) {
		$this->autoDelete = $autoDelete;
	}

	/**
	 * @return BindingConfig[][]
	 */
	public function getBindings(): array {
		return $this->bindings;
	}

	/**
	 * @param BindingConfig[][] $bindings
	 */
	public function setBindings( array $bindings ) {
		$this->bindings = $bindings;
	}

	/**
	 * @return string
	 */
	public function getConnectionIdentifier(): string {
		return $this->connectionIdentifier;
	}

	/**
	 * @param string $connectionIdentifier
	 */
	public function setConnectionIdentifier( string $connectionIdentifier ) {
		$this->connectionIdentifier = $connectionIdentifier;
	}

}