<?php namespace Ipunkt\LaravelRabbitMQ\Config;

/**
 * Class ExchangeConfig
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class ExchangeConfig {

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

	const TYPE_TOPIC = 'topic';

	/**
	 * @var string
	 */
	protected $type = self::TYPE_TOPIC;

	/**
	 * @var bool
	 */
	protected $passive = false;

	/**
	 * @var bool
	 */
	protected $durable = false;

	/**
	 * @var bool
	 */
	protected $autoDelete = false;

	/**
	 * @var bool
	 */
	protected $internal = false;

	/**
	 * @var bool
	 */
	protected $nowait = false;

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
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType( string $type ) {
		$this->type = $type;
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
	 * @return bool
	 */
	public function isInternal(): bool {
		return $this->internal;
	}

	/**
	 * @param bool $internal
	 */
	public function setInternal( bool $internal ) {
		$this->internal = $internal;
	}

	/**
	 * @return bool
	 */
	public function isNowait(): bool {
		return $this->nowait;
	}

	/**
	 * @param bool $nowait
	 */
	public function setNowait( bool $nowait ) {
		$this->nowait = $nowait;
	}

	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 * @return ExchangeConfig
	 */
	public function setIdentifier( string $identifier ) {
		$this->identifier = $identifier;
		return $this;
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