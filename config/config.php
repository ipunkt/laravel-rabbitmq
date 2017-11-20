<?php

/**
 * Package configuration
 */
return [
	//  queue identifier to listen on
	'default' => [
		'host' => '',
		'port' => 5672,
		'user' => 'guest',
		'password' => 'guest',
		'exchange' => [
			'exchange' => '',
			'type' => '',
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
		],
	],
];