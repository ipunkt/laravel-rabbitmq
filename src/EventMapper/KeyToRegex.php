<?php namespace Ipunkt\LaravelRabbitMQ\EventMapper;

/**
 * Class KeyToRegex
 * @package Ipunkt\LaravelRabbitMQ\EventMapper
 */
class KeyToRegex {

	/**
	 * @param $key
	 * @return string
	 */
	public function toRegex( $key ) {
		$escapedKey = str_replace('.', '\\.', $key);

		$replacements = [
			'*' => '[^.]+',
			'#' => '.*',
		];

		return '~^'.str_replace(array_keys($replacements), array_values($replacements), $escapedKey).'$~';
	}

}