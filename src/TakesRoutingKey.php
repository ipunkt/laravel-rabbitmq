<?php namespace Ipunkt\LaravelRabbitMQ;

/**
 * Interface TakesRoutingKey
 * @package Ipunkt\LaravelRabbitMQ
 */
interface TakesRoutingKey {

	/**
	 * @param $routingKey
	 */
	function setRoutingKey($routingKey);

}