<?php

require_once dirname( __DIR__ ) . '/php-typography/php-typography-functions.php';

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
		$parser = new \Masterminds\HTML5( array( 'disable_html_ns' => true ) );
		$dom = $parser->loadHTML( '<body><p>blabla</p><ul><li>foo</li><li>bar</li></ul></body>' );
		$xpath = new \DOMXPath( $dom );

		$node_list = $xpath->query( '//*' );
		$node_array = \PHP_Typography\nodelist_to_array( $node_list );

		$this->assertGreaterThan( 1, $node_list->length );
		$this->assertSame( $node_list->length, count( $node_array ) );
		foreach( $node_list as $node ) {
			$this->assertArrayHasKey( spl_object_hash( $node ), $node_array );
			$this->assertSame( $node, $node_array[ spl_object_hash( $node ) ] );
		}
	}

	public function provide_get_ancestors_data() {
		return array(
			array( '<div class="ancestor"><p class="ancestor">bar <span id="origin">foo</span></p></div><p>foo <span>bar</span></p>', '//*[@id="origin"]'  ),
		);
	}

	/**
	 * @covers \PHP_Typography\get_ancestors
	 *
	 * @uses PHP_Typography\nodelist_to_array
	 *
	 * @dataProvider provide_get_ancestors_data
	 */
	public function test_get_ancestors( $html, $xpath_query ) {
		$parser = new \Masterminds\HTML5( array( 'disable_html_ns' => true ) );
		$dom = $parser->loadHTML( '<body>' . $html . '</body>' );
		$xpath = new \DOMXPath( $dom );

		$origin = $xpath->query( $xpath_query )->item( 0 );
		$ancestor_array = \PHP_Typography\get_ancestors( $origin );
		$ancestor_array_xpath = \PHP_Typography\nodelist_to_array( $xpath->query( 'ancestor::*', $origin ) );

		$this->assertSame( count( $ancestor_array ), count( $ancestor_array_xpath ) );
		foreach ( $ancestor_array as $ancestor ) {
			$this->assertContains( $ancestor, $ancestor_array_xpath );
		}
	}

	public function provide_has_class_data() {
		return array(
			array( '<span class="foo bar"></span>', '//span', 'bar', true ),
			array( '<span class="foo bar"></span>', '//span', 'foo', true ),
			array( '<span class="foo bar"></span>', '//span', 'foobar', false ),
			array( '<span class="foo bar"></span>', '//span', array( 'foo' ), true ),
			array( '<span class="foo bar"></span>', '//span', array( 'foo', 'bar' ), true ),
			array( '<span class="foo bar"></span>', '//span', '', false ),
			array( '<span class="foo bar"></span>', '//span', array(), false ),
			array( '<span class="foo bar"></span>', '//span', array( '' ), false ),
			array( '<span class="foo bar">something</span>', '//span/text()', 'bar', true ),
			array( '<span>something</span>', '//span', 'foo', false ),
		);
	}

	/**
	 * @covers \PHP_Typography\has_class
	 * @dataProvider provide_has_class_data
	 */
	public function test_has_class( $html, $xpath_query, $classnames, $result ) {
		$parser = new \Masterminds\HTML5( array( 'disable_html_ns' => true ) );
		$dom = $parser->loadHTML( '<body>' . $html . '</body>' );
		$xpath = new \DOMXPath( $dom );

		$nodes = $xpath->query( $xpath_query );
		foreach ( $nodes as $node ) {
			$this->assertSame( $result, \PHP_Typography\has_class( $node, $classnames ) );
		}
	}

	public function provide_uchr_data() {
		return array(
			array( 33,   '!'  ),
			array( 9,    "\t" ),
			array( 10,   "\n" ),
			array( 35,   '#'  ),
			array( 103,  'g'  ),
			array( 336,  'Ő'  ),
			array( 497,  'Ǳ'  ),
			array( 1137, 'ѱ'  ),
			array( 2000, 'ߐ'  ),
		);
	}

	/**
	 * @covers \PHP_Typography\uchr
	 * @dataProvider provide_uchr_data
	 */
	public function test_uchr( $code, $result ) {
		$this->assertSame( $result, \PHP_Typography\uchr( $code ) );
	}

	public function provide_is_odd_data() {
		return array(
			array( 0, false ),
			array( 1, true ),
			array( 2, false ),
			array( 5, true ),
			array( 68, false ),
			array( 781, true ),
		);
	}

	/**
	 * @covers \PHP_Typography\is_odd
	 * @dataProvider provide_is_odd_data
	 */
	public function test_is_odd( $number, $result ) {
		if ( $result ) {
 			$this->assertTrue( \PHP_Typography\is_odd( $number ) );
		} else {
			$this->assertFalse( \PHP_Typography\is_odd( $number ) );
		}
	}

	public function provide_mb_str_split_data() {
		return array(
			array( '', 1, 'UTF-8', array() ),
			array( 'A ship', 1, 'UTF-8', array( 'A', ' ', 's', 'h', 'i', 'p' ) ),
			array( 'Äöüß', 1, 'UTF-8', array( 'Ä', 'ö', 'ü', 'ß') ),
			array( 'Äöüß', 2, 'UTF-8', array( 'Äö', 'üß') ),
			array( 'Äöüß', 0, 'UTF-8', false ),
		);
	}

	/**
	 * @covers \PHP_Typography\mb_str_split
	 * @dataProvider provide_mb_str_split_data
	 */
	public function test_mb_str_split( $string, $length, $encoding, $result ) {
		$this->assertSame( $result, \PHP_Typography\mb_str_split( $string, $length, $encoding ) );
	}

	/**
	 * @covers \PHP_Typography\get_language_plugin_list
	 */
	public function test_get_language_plugin_list() {
		$path = dirname( __DIR__ ) . '/php-typography/diacritics/';
		$languages = \PHP_Typography\get_language_plugin_list( $path, 'diacriticLanguage' );

		$this->assertCount( 2, $languages );
		$this->assertArrayHasKey( 'en-US', $languages );
		$this->assertContains( 'English (United States)', $languages );
		$this->assertArrayHasKey( 'de-DE', $languages );
		$this->assertContains( 'German', $languages );
	}
}
