<?php namespace Ipunkt\LaravelRabbitMQ\Config;

/**
 * Class ConnectionConfig
 * @package Ipunkt\LaravelRabbitMQ\Config
 */
class ConnectionConfig {

	/**
	 * @var string
	 */
	protected $identifier = '';

	/**
	 * @var string
	 */
	protected $host = '';

	/**
	 * @var int
	 */
	protected $port = 0;

	/**
	 * @var string
	 */
	protected $user = '';

	/**
	 * @var string
	 */
	protected $password = '';

	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->host;
	}

	/**
	 * @param string $host
	 * @return ConnectionConfig
	 */
	public function setHost( string $host ): ConnectionConfig {
		$this->host = $host;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * @param int $port
	 * @return ConnectionConfig
	 */
	public function setPort( int $port ): ConnectionConfig {
		$this->port = $port;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser(): string {
		return $this->user;
	}

	/**
	 * @param string $user
	 * @return ConnectionConfig
	 */
	public function setUser( string $user ): ConnectionConfig {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return ConnectionConfig
	 */
	public function setPassword( string $password ): ConnectionConfig {
		$this->password = $password;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 * @return ConnectionConfig
	 */
	public function setIdentifier( string $identifier ): ConnectionConfig {
		$this->identifier = $identifier;
		return $this;
	}

}