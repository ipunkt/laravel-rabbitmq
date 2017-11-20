# RabbitMQ for Laravel

We provide a separate package for the use of [RabbitMQ](https://www.rabbitmq.com) because we want to use it for communication between microservices, written in any language. The existing packages are bound to laravel so we have the whole data - with class names and so on - within the message body. Our package sends only the raw data through RabbitMQ.

[![Latest Stable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/stable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Latest Unstable Version](https://poser.pugx.org/ipunkt/laravel-rabbitmq/v/unstable.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![License](https://poser.pugx.org/ipunkt/laravel-rabbitmq/license.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq) [![Total Downloads](https://poser.pugx.org/ipunkt/laravel-rabbitmq/downloads.svg)](https://packagist.org/packages/ipunkt/laravel-rabbitmq)

This package provides the sending part as well as the listener part. The sending part sends synchronously to the message queue. The listener maps routing keys to an event listener configured.

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

We suggest the message sender creates the exchange. If you do it the other way around the listener cann create the exchange too. You need to add the command flag `--declare-exchange` to the `rabbitmq:listen` command.

Within the configuration `bindings` has routing keys configured with a 1:1 mapping to a laravel event (`php artisan make:event ...`). This event gets the message data as constructor parameter.

Then you can have one or more listener (`php artisan make:listener ...`) - defined in your EventListener. And voila everything works fine.

We suggest running the `rabbitmq:listen` command with a [supervisor](https://laravel.com/docs/5.5/queues#supervisor-configuration) backed container like the [queue:work](https://laravel.com/docs/5.5/queues#running-the-queue-worker) command shipped with laravel.