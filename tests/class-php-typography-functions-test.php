<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2016-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

// Can't autoload functions.
require_once dirname( __DIR__ ) . '/php-typography/php-typography-functions.php';

/**
 * Test cases for php-typography/php-typography-functions.php
 */
class PHP_Typography_Functions_Test extends \PHPUnit\Framework\TestCase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * Provide data for testing arrays_intersect.
	 *
	 * @return array
	 */
	public function provide_arrays_intersect_data() {
		return [
			[ [], [], false ],
			[ [ 1, 2, 3 ], [ 2, 4, 1 ], true ],
			[ [ 1, 2, 3 ], [], false ],
			[ [], [ 1, 2, 3 ], false ],
		];
	}

	/**
	 * $a1 and $a2 need to be arrays of object indexes < 10
	 *
	 * @covers \PHP_Typography\arrays_intersect
	 * @dataProvider provide_arrays_intersect_data
	 *
	 * @param  array $a1     First array.
	 * @param  array $a2     Second array.
	 * @param  bool  $result Expected result.
	 */
	public function test_arrays_intersect( array $a1, array $a2, $result ) {
		$nodes = [];
		for ( $i = 0; $i < 10; ++$i ) {
			$nodes[] = new \DOMText( "foo $i" );
		}

		$array1 = [];
		foreach ( $a1 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array1[] = $nodes[ $index ];
			}
		}

		$array2 = [];
		foreach ( $a2 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array2[ spl_object_hash( $nodes[ $index ] ) ] = $nodes[ $index ];
			}
		}

		$this->assertSame( $result, \PHP_Typography\arrays_intersect( $array1, $array2 ) );
	}

	/**
	 * Test nodelist_to_array.
	 *
	 * @covers \PHP_Typography\nodelist_to_array
	 */
	public function test_nodelist_to_array() {
		$parser = new \Masterminds\HTML5( [
			'disable_html_ns' => true,
		] );
		$dom = $parser->loadHTML( '<body><p>blabla</p><ul><li>foo</li><li>bar</li></ul></body>' );
		$xpath = new \DOMXPath( $dom );

		$node_list = $xpath->query( '//*' );
		$node_array = \PHP_Typography\nodelist_to_array( $node_list );

		$this->assertGreaterThan( 1, $node_list->length );
		$this->assertSame( $node_list->length, count( $node_array ) );
		foreach ( $node_list as $node ) {
			$this->assertArrayHasKey( spl_object_hash( $node ), $node_array );
			$this->assertSame( $node, $node_array[ spl_object_hash( $node ) ] );
		}
	}

	/**
	 * Provide data for testing get_ancestors.
	 *
	 * @return array
	 */
	public function provide_get_ancestors_data() {
		return [
			[ '<div class="ancestor"><p class="ancestor">bar <span id="origin">foo</span></p></div><p>foo <span>bar</span></p>', '//*[@id="origin"]' ],
		];
	}

	/**
	 * Test get_ancestors.
	 *
	 * @covers \PHP_Typography\get_ancestors
	 *
	 * @uses PHP_Typography\nodelist_to_array
	 *
	 * @dataProvider provide_get_ancestors_data
	 *
	 * @param  string $html        HTML input.
	 * @param  string $xpath_query XPath query.
	 */
	public function test_get_ancestors( $html, $xpath_query ) {
		$parser = new \Masterminds\HTML5( [
			'disable_html_ns' => true,
		] );
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

	/**
	 * Provide data for testing has_class.
	 *
	 * @return array
	 */
	public function provide_has_class_data() {
		return [
			[ '<span class="foo bar"></span>', '//span', 'bar', true ],
			[ '<span class="foo bar"></span>', '//span', 'foo', true ],
			[ '<span class="foo bar"></span>', '//span', 'foobar', false ],
			[ '<span class="foo bar"></span>', '//span', [ 'foo' ], true ],
			[ '<span class="foo bar"></span>', '//span', [ 'foo', 'bar' ], true ],
			[ '<span class="foo bar"></span>', '//span', '', false ],
			[ '<span class="foo bar"></span>', '//span', [], false ],
			[ '<span class="foo bar"></span>', '//span', [ '' ], false ],
			[ '<span class="foo bar">something</span>', '//span/text()', 'bar', true ],
			[ '<span>something</span>', '//span', 'foo', false ],
		];
	}

	/**
	 * Test has_class.
	 *
	 * @covers \PHP_Typography\has_class
	 * @dataProvider provide_has_class_data
	 *
	 * @param  string $html        HTML input.
	 * @param  string $xpath_query XPath query.
	 * @param  array  $classnames  Array of classnames.
	 * @param  bool   $result      Expected result.
	 */
	public function test_has_class( $html, $xpath_query, $classnames, $result ) {
		$parser = new \Masterminds\HTML5( [
			'disable_html_ns' => true,
		] );
		$dom = $parser->loadHTML( '<body>' . $html . '</body>' );
		$xpath = new \DOMXPath( $dom );

		$nodes = $xpath->query( $xpath_query );
		foreach ( $nodes as $node ) {
			$this->assertSame( $result, \PHP_Typography\has_class( $node, $classnames ) );
		}
	}

	/**
	 * Provide data for testing is_odd.
	 *
	 * @return array
	 */
	public function provide_is_odd_data() {
		return [
			[ 0, false ],
			[ 1, true ],
			[ 2, false ],
			[ 5, true ],
			[ 68, false ],
			[ 781, true ],
		];
	}

	/**
	 * Test is_odd.
	 *
	 * @covers \PHP_Typography\is_odd
	 * @dataProvider provide_is_odd_data
	 *
	 * @param  int  $number A number.
	 * @param  bool $result Expected result.
	 */
	public function test_is_odd( $number, $result ) {
		if ( $result ) {
			$this->assertTrue( \PHP_Typography\is_odd( $number ) );
		} else {
			$this->assertFalse( \PHP_Typography\is_odd( $number ) );
		}
	}

	/**
	 * Test get_object_hash function.
	 *
	 * @covers \PHP_Typography\get_object_hash
	 */
	public function test_get_object_hash() {
		$hash1 = \PHP_Typography\get_object_hash( 666 );
		$this->assertInternalType( 'string', $hash1 );
		$this->assertGreaterThan( 0, strlen( $hash1 ) );

		$hash2 = \PHP_Typography\get_object_hash( new stdClass() );
		$this->assertInternalType( 'string', $hash2 );
		$this->assertGreaterThan( 0, strlen( $hash2 ) );

		$this->assertNotEquals( $hash1, $hash2 );
	}
}
