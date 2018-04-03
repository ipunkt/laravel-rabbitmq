<?php namespace Ipunkt\LaravelRabbitMQ;

/**
 * Interface TakesRoutingMatches
 * @package Ipunkt\LaravelRabbitMQ
 */
interface TakesRoutingMatches {

	/**
	 * Receive the
	 *
	 * @param array $matches
	 */
	public function setRoutingMatches( array $matches );

}