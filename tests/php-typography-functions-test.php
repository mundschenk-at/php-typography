<?php

/**
 * Test cases for php-typography/php-typography-functions.php
 */
class PHP_Typography_Functions_Test extends PHPUnit_Framework_TestCase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->parser = new \PHP_Typography\Parse_Text;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	public function provide_arrays_intersect_data() {
		return array(
			array( array(), array(), false ),
			array( array( 1, 2, 3 ), array( 2, 4, 1 ), true ),
			array( array( 1, 2, 3 ), array(), false ),
			array( array(), array( 1, 2, 3 ), false ),
		);
	}

	/**
	 * $a1 and $a2 need to be arrays of object indexes < 10
	 *
	 * @covers \PHP_Typography\arrays_intersect
	 * @dataProvider provide_arrays_intersect_data
	 */
	public function test_arrays_intersect( array $a1, array $a2, $result ) {
		$nodes = array();
		for ( $i = 0; $i < 10; ++$i ) {
			$nodes[] = new \DOMText( "foo $i" );
		}

		$array1 = array();
		foreach ( $a1 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array1[] = $nodes[ $index ];
			}
		}

		$array2 = array();
		foreach ( $a2 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array2[ spl_object_hash( $nodes[ $index ] ) ] = $nodes[ $index ];
			}
		}

		$this->assertSame( $result, \PHP_Typography\arrays_intersect( $array1, $array2 ) );
	}

	/**
	 * @covers \PHP_Typography\nodelist_to_array
	 */
	public function test_nodelist_to_array() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}


	/**
	 * @covers \PHP_Typography\get_ancestors
	 */
	public function test_get_ancestors() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\has_class
	 */
	public function test_has_class() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\uchr
	 */
	public function test_uchr() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\is_odd
	 */
	public function test_is_odd() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\mb_str_split
	 */
	public function test_mb_str_split() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\get_hyphenation_languages
	 */
	public function test_get_hyphenation_languages() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\get_diacritic_languages
	 */
	public function test_get_diacritic_languages() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}

	/**
	 * @covers \PHP_Typography\translate_words
	 */
	public function test_translate_words() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
			);
	}
}
