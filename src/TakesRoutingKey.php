<?php namespace Ipunkt\LaravelRabbitMQ;

/**
 * Interface TakesRoutingKey
 * @package Ipunkt\LaravelRabbitMQ
 */
interface TakesRoutingKey {

	/**
	 * Receive the routing key which triggered this event
	 *
	 * @param $routingKey
	 */
	function setRoutingKey($routingKey);

}