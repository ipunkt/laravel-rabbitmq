<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class ChannelBuilder
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ\Builder
 */
class ChannelBuilder {

	/**
	 * @param AMQPStreamConnection $connection
	 * @return AMQPChannel
	 */
	public function buildChannel( AMQPStreamConnection $connection ) {
		$channel = $connection->channel();
		$channel->basic_qos(0, 1, false);

		return $channel;
	}

}