<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder;

use Ipunkt\LaravelRabbitMQ\Config\ConfigManager;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class ConnectionBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 */
class ConnectionBuilder {
	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * ConnectionBuilder constructor.
	 * @param ConfigManager $configManager
	 */
	public function __construct( ConfigManager $configManager ) {
		$this->configManager = $configManager;
	}

	/**
	 * @param string $connectionIdentifier
	 * @return AMQPStreamConnection
	 */
	public function buildConnection( string $connectionIdentifier ) {

		$connectionConfig = $this->configManager->getConnection( $connectionIdentifier );

		return new AMQPStreamConnection(
			$connectionConfig->getHost(),
			$connectionConfig->getPort(),
			$connectionConfig->getUser(),
			$connectionConfig->getPassword()
		);

	}

}