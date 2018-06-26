<?php namespace Ipunkt\LaravelRabbitMQ;

/**
 * Interface DropsEvent
 * @package Ipunkt\LaravelRabbitMQ
 *
 * This empty interface is intended to be implemented by exceptions.
 * Throwing an exception which implements this interface will cause the message to be `nacked` - acknowledged as not concerning
 * this service.
 *
 * Use case:
 * A validation exception should be logged to the user and stop the handlers execution but it should not requeued on a
 * durable queue. The event data will not become valid in the meantime
 */
interface DropsEvent {

}