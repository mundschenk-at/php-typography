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

	public function provide_array_intersection_data() {
		return array(
			array( array(), array(), array() ),
			array( array( 'a', 'b', 'c' ), array( 'b', 'B', 'a'), array( 'a', 'b' ) ),
			array( array( 1, 2, 3 ), array(), array() ),
			array( array(), array( 1, 2, 3 ), array() ),
			array( array( 'a', 1, array() ), array( array() ), array( array() ) ),
		);
	}

	/**
	 * @covers \PHP_Typography\array_intersection
	 * @dataProvider provide_array_intersection_data
	 */
	public function test_array_intersection( array $a1, array $a2, array $result ) {
		$this->assertSame( $result, \PHP_Typography\array_intersection( $a1, $a2 ) );
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
