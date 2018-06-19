# RabbitMQ for Laravel

We provide a separate package for the use of [RabbitMQ](https://www.rabbitmq.com) because we want to use it for communication between microservices, written in any language. The existing packages are bound to laravel so we have the whole data - with class names and so on - within the message body. Our package sends only the raw data through RabbitMQ.

[![Latest Stable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/stable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/unstable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![License](https://poser.pugx.org/ipunkt/laravel-rabbitmq/license.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Total Downloads](https://poser.pugx.org/ipunkt/laravel-rabbitmq/downloads.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq)

This package provides the sending part as well as the listener part. The sending part sends synchronously to the message queue. The listener maps routing keys to an event listener configured.

## Branch Info
This branch is for use with laravel 5.4 because laravel 5.6 changed the interfaces for logging

## Quickstart

```
composer require ipunkt/laravel-rabbitmq
```

We support package auto-discovery for laravel, so you are ready to use the package.


## Installation

Add to your composer.json following lines

	"require": {
		"ipunkt/laravel-rabbitmq": "*"
	}

You can publish all provided files by typing `php artisan vendor:publish` and select to package provider (or one of the provided tags - but be careful, tags are global).

## Configuration

In `config/laravel-rabbitmq.php` is the configuration for the usable queues on RabbitMQ. We do not use any of the values configured in the laravel-shipped `config/queues.php`.

```php
'YOUR-QUEUE-IDENTIFIER' => [
	'host' => 'YOUR-RABBITMQ-HOST',
	'port' => 5672,
	'user' => 'guest',
	'password' => 'guest',
	'name' => '',
	'durable' => false,
	'exchange' => [
		'exchange' => 'YOUR-EXCHANGE-NAME',
		'type' => 'YOUR-EXCHANGE-TYPE',
		'passive' => false,
		'durable' => false,
		'auto_delete' => false,
		'internal' => false,
		'nowait' => false,
		'arguments' => null,
		'ticket' => null,
	],
	'bindings' => [
		//  key => event name
		// ROUTING-KEY maps to an LARAVEL-EVENT-CLASS-NAME
	],
],
'logging' => [
	'enable' => false,
	/**
	 * Set this to false if you do not wish to log Exceptions or Throwables from `rabbitmq:listen`
	 */
	'event-errors' => true,
	'queue-identifier' => 'default',
	'extra-context' => [],
],
```

## Usage

### Publishing a Message

Within your controller oder console command you need our `RabbitMQ` class instance:

```php
$rabbitMQ = new \Ipunkt\LaravelRabbitMQ\RabbitMQ();// or from DI container
$rabbitMQ->onQueue('YOUR-QUEUE-IDENTIFIER') // Queue Identifier has to be configured within the laravel-rabbitmq.php
	->data(['users' => $users]) // anything, gets json_encoded
	->publish('ROUTING-KEY'); // publish to a routing key, the listener is subscribed to
```

### Subscribing for Messages

For subscribing we provide an artisan command:

```bash
php artisan rabbitmq:listen YOUR-QUEUE-IDENTIFIER
```

We suggest the message sender creates the exchange. If you do it the other way around the listener can create the exchange too. You need to add the command flag `--declare-exchange` to the `rabbitmq:listen` command.

Within the configuration `bindings` has routing keys configured with a 1:1 mapping to a laravel event (`php artisan make:event ...`). This event gets the message data as constructor parameter.

Then you can have one or more listener (`php artisan make:listen ...`) - defined in your EventListener. And voila everything works fine.

We suggest running the `rabbitmq:listen` command with a [supervisor](https://laravel.com/docs/5.5/queues#supervisor-configuration) backed container like the [queue:work](https://laravel.com/docs/5.5/queues#running-the-queue-worker) command shipped with laravel.


#### Receive routing keys in event data
If a binding with placeholder is used it can be necessary to parse the routing key.

To receive the routing key which triggered the Event to be thrown implement `Ipunkt\LaravelRabbitMQ\TakesRoutingKey` 
with your event.

To receive the values of the placeholders in your event binding matching the routing key which triggered the Event to be
thrown implement `Ipunkt\LaravelRabbitMQ\TakesRoutingMatches`

#### Persistent messages
Setting the `durable` for a queue will cause the queue to be created durable. This means it will continue to exist - and
 receive messages - when `rabbitmq:listen` is not running.
To take advantage of this fact `name` should also be set to a string identifying the microservice. This will cause the
queue to be named instead of anonymous so running `rabbitmq:listen` will actually reconnect to the queue and process all
messages received by it.

Setting `durable` also enables consumer confirmation for messages.
This means a message is only deleted from the queue after it is confirmed. This is currently done by returning `true` or
`false` from the EventHandler.
Returning `true` acknowledges the message as done  
Returning `false` acknowledges the message as not processed but does not concern this service so delete anyway
Returning anything else including the default `null` or having an exception escalate outside the handler will not acknowledge
the message and have it return to the queue.

Note the rabbitmq note on this mode of durability:

---

##### Note on message persistence

Marking messages as persistent doesn't fully guarantee that a message won't be lost. Although it tells RabbitMQ to save the message to disk, there is still a short time window when RabbitMQ has accepted a message and hasn't saved it yet. Also, RabbitMQ doesn't do fsync(2) for every message -- it may be just saved to cache and not really written to the disk. The persistence guarantees aren't strong, but it's more than enough for our simple task queue. If you need a stronger guarantee then you can use publisher confirms.

---

### Logging
If logging is set to true then a MessageHandler is added to the laravel monolog instance which sends messages to the
given exchange.
If `extra-context` is set then the content will be added to every messages context. Use case for this is to add
a `'service' => 'currentmicroservice'` info to all messages to identify which service the message is from.
Unless `event-errors` is false Exceptions or Throwables caught in `rabbitmq:listen` are forwarded to the laravel logger.


