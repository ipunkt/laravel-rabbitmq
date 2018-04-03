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
		$escapes = [
			'.' => '\\.',
			'(' => '\\(',
			')' => '\\)',
			'$' => '\\$',
			'^' => '\\^',
		];

		$escapedKey = str_replace(array_keys($escapes), array_values($escapes), $key);

		$replacements = [
			'*' => '([^.]+)',
			'#' => '(.*)',
		];

		return '~^'.str_replace(array_keys($replacements), array_values($replacements), $escapedKey).'$~';
	}

}