<?php

use PHPUnit\Framework\TestCase;

/**
 * Class KeyToRegexTest
 */
class KeyToRegexTest extends TestCase {

	/**
	 * @var \Ipunkt\LaravelRabbitMQ\EventMapper\KeyToRegex
	 */
	private $keyToRegex;

	/******************************************************************************************************************
	 * Helper
	 ******************************************************************************************************************/
	protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */ {
		$this->keyToRegex = new \Ipunkt\LaravelRabbitMQ\EventMapper\KeyToRegex();

		parent::setUp();
	}




	/******************************************************************************************************************
	 * Data
	 ******************************************************************************************************************/


	/**
	 * @return array
	 */
	public function dataMatches() {
		return [
			[ 'erro.#', 'erro.local' ],
			[ 'erro.#', 'erro.local.kekse' ],
			[ 'erro.*', 'erro.local' ],
			[ '#', 'erro.local' ],
			[ '*.local', 'erro.local' ],
		];
	}

	/**
	 * @return array
	 */
	public function dataDoesNotMatch() {
		return [
			[ 'erro.#', 'warn.local' ],
			[ 'erro.*', 'erro.local.kekse' ],
			[ '*.local', 'erro.local.kekse' ],
		];
	}


	/******************************************************************************************************************
	 * Tets
	 ******************************************************************************************************************/


	/**
	 * @test
	 * @dataProvider dataMatches
	 * @param string $binding
	 * @param string $event
	 */
	public function regexMatchesKey( string $binding, string $event ) {
		$regex = $this->keyToRegex->toRegex($binding);

		$this->assertEquals( 1, preg_match($regex, $event), 'Regex from binding '.$binding.' did not match expected event '.$event );
	}


	/**
	 * @test
	 * @dataProvider dataDoesNotMatch
	 * @param string $binding
	 * @param string $event
	 */
	public function regexDoesNotMatchKey( string $binding, string $event ) {
		$regex = $this->keyToRegex->toRegex($binding);

		$this->assertEquals( 0, preg_match($regex, $event), 'Regex from binding '.$binding.' did matched unexpected event '.$event );
	}
}