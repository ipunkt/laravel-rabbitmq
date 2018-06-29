<?php

/**
 * Package configuration
 */
return [
	'connections' => [
		'default-connection' => [
			'host' => '',
			'port' => 5672,
			'user' => 'guest',
			'password' => 'guest',
		],
	],

	'exchanges' => [

		//  queue identifier to listen on
		'default-exchange' => [
			/**
			 * The name opf the exchange
			 */
			'name' => 'NAME_HERE',

			/**
			 * Connection to use when sending messages to this exchange
			 */
			'connection' => 'default-connection',

			/**
			 *
			 */
			'type' => 'topics',

			/**
			 * If set to true then the service will not create the exchange. It will only connect to an existing exchange.
			 * Note that setting this to true will not prevent the service from connecting to an existing exchange
			 */
			'passive' => false,

			/**
			 * If set to true then the exchange will be deleted once all services disconnect from it.
			 * In most use cases this is undesirable as all bindings from existing queues are also lost and so recreating
			 * the exchange will require recreating all queues, most likely be restarting the services behind the queues
			 *
			 * Recommended setting: false
			 */
			'auto_delete' => false,

			/**
			 * If set to true then the exchange will persist across server restarts
			 * Much like auto_delete losing the exchange due to a server restart also means all queue bindings would have
			 * to be recreated, currently only possible by restarting the matching services(if using this package)
			 *
			 * Recommended setting: true
			 */
			'durable' => true,

			/**
			 * If set to true then it won't be possible to publish messages to this exchange.
			 * The use case for this is for the exchange to bind to other exchanges.
			 * It is not yet possible to create these bindings using this package
			 */
			'internal' => false,
		],
	],

	'queues' => [

		'default-queue' => [

			/**
			 *
			 */
			'name' => '',

			/**
			 * Setting this to true will cause the queue to continue existing even on rabbitmq restarts
			 * A queue that exists will receive events, so the service receives all events sent to bidings even if the service itself
			 * was down at the time
			 *
			 * Recommended setting: true
			 */
			'durable' => true,

			/**
			 * Connection to use when listening to this queue
			 */
			'connection' => 'default-connection',

			/**
			 * Setting this to true will prevent the service from creating the queue. It will only connect if the queue
			 * already exists.
			 * This is mainly useful to make 'queue missing, possible data loss' an error case in which the user has to
			 * manually intervene.
			 *
			 * Recommended setting: false
			 */
			'passive' => false,

			/**
			 * Setting this to true will prevent other services from connecting to this queue as long as we are connected
			 * Use-case: force concurrency of message processing
			 *
			 * Recommended setting: false
			 */
			'exclusive' => false,

			/**
			 * Setting this to true will cause the queue to be deleted once all consumers(this service) quit using it.
			 * It is the polar opposite of durable, deleting a queue when no longer in use.
			 *
			 * Recommended setting: false
			 */
			'auto_delete' => false,

			/**
			 * no_wait is not set here. It is not a parameter of the queue as such but depends on the context of the
			 * queue creation.
			 */

			/**
			 * arguments is not set here. It sends server implementation specific arguments and until a use-case for those
			 * arise they are disregarded for now.
			 */

			'bindings' => [
				'default-exchange' => [
					'routing.key' => 'Event::class',
				]
			],

		],
	],

	'logging' => [
		/**
		 * Set this to false if you do not wish to log Exceptions or Throwables from `rabbitmq:listen`
		 */
		'enable' => false,

		'connection' => 'default-connection',

		'exchange' => 'default-exchange',

		/**
		 * Setting this to true will cause the rabbitmq:listen command to throw `ExceptionInRabbitMQEvent`s when an exception
		 * happens in an Event handler.
		 */
		'event-errors' => false,
		'extra-context' => [
			'service' => 'Service name',
		],
	],
];