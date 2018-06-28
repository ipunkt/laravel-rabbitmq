<?php namespace Ipunkt\LaravelRabbitMQ\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class IsDurableChecker
 * @package Ipunkt\LaravelRabbitMQ\RabbitMQ
 */
class IsDurableChecker {

	/**
	 * @param AMQPMessage $message
	 * @return bool
	 */
	public function isDurable( AMQPMessage $message ) {

		if ( $message->delivery_info & AMQPMessage::DELIVERY_MODE_PERSISTENT )
			return true;

		return false;

	}

}