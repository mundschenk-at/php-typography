<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
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

/**
 * DOM unit test.
 *
 * @coversDefaultClass \PHP_Typography\DOM
 * @usesDefaultClass \PHP_Typography\DOM
 */
class DOM_Test extends \PHPUnit\Framework\TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		$this->typo = new \PHP_Typography\PHP_Typography( false );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
	}


	/**
	 * Test nodelist_to_array.
	 *
	 * @covers ::nodelist_to_array
	 */
	public function test_nodelist_to_array() {
		$parser = new \Masterminds\HTML5( [
			'disable_html_ns' => true,
		] );
		$dom = $parser->loadHTML( '<body><p>blabla</p><ul><li>foo</li><li>bar</li></ul></body>' );
		$xpath = new \DOMXPath( $dom );

		$node_list = $xpath->query( '//*' );
		$node_array = \PHP_Typography\DOM::nodelist_to_array( $node_list );

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
		$ancestor_array = \PHP_Typography\DOM::get_ancestors( $origin );
		$ancestor_array_xpath = \PHP_Typography\DOM::nodelist_to_array( $xpath->query( 'ancestor::*', $origin ) );

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
			$this->assertSame( $result, \PHP_Typography\DOM::has_class( $node, $classnames ) );
		}
	}
}
