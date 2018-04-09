<?php namespace Ipunkt\LaravelRabbitMQ\Console;

use Ipunkt\LaravelRabbitMQ\RabbitMQ;

/**
 * Class MessageStatus
 * @package Ipunkt\LaravelRabbitMQ\Console
 */
class MessageStatus {


	/**
	 * @var bool[][]
	 */
	protected $returnValues;

	/**
	 * @param array $returns
	 */
	public function addEventReturns( array $returns ) {
		$this->returnValues[] = $returns;
	}

	/**
	 *
	 */
	public function takenEncountered() {

		foreach($this->returnValues as $eventReturns) {

			if( in_array(RabbitMQ::TAKEN, $eventReturns) )
				return true;

		}

		return false;

	}

}