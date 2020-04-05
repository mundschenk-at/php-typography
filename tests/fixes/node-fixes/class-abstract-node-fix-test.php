<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2020 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  of the License, or ( at your option ) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use PHP_Typography\Fixes\Node_Fixes;
use PHP_Typography\Settings;

/**
 * Abstract_Node_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Abstract_Node_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = $this->getMockForAbstractClass( Node_Fixes\Abstract_Node_Fix::class );
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$feed_fix     = $this->getMockForAbstractClass( Node_Fixes\Abstract_Node_Fix::class, [ true ] );
		$non_feed_fix = $this->getMockForAbstractClass( Node_Fixes\Abstract_Node_Fix::class, [ false ] );

		$this->assert_attribute_same( true,  'feed_compatible', $feed_fix,     'The fixer should be feed_compatible.' );
		$this->assert_attribute_same( false, 'feed_compatible', $non_feed_fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Tests the method feed_compatible().
	 *
	 * @covers ::feed_compatible
	 *
	 * @uses ::__construct
	 */
	public function test_feed_compatible() {
		$feed_fix     = $this->getMockForAbstractClass( Node_Fixes\Abstract_Node_Fix::class, [ true ] );
		$non_feed_fix = $this->getMockForAbstractClass( Node_Fixes\Abstract_Node_Fix::class, [ false ] );

		$this->assertTrue( $feed_fix->feed_compatible(), 'The fixer should be feed_compatible.' );
		$this->assertFalse( $non_feed_fix->feed_compatible(), 'The fixer should not be feed_compatible.' );
	}


	/**
	 * Provide data for testing remove_adjacent_characters.
	 *
	 * @return array
	 */
	public function provide_remove_adjacent_characters_data() {
		return [
			[ "'A certain kind'", "'", "'", 'A certain kind' ],
			[ "'A certain kind", "'", "'", 'A certain kin' ],
			[ "'A certain kind'", "'", '', "A certain kind'" ],
		];
	}

	/**
	 * Test private method remove_adjacent_characters.
	 *
	 * @covers ::remove_adjacent_characters
	 * @dataProvider provide_remove_adjacent_characters_data
	 *
	 * @param string $string A string.
	 * @param string $prev   The previous character.
	 * @param string $next   The next character.
	 * @param string $result The trimmed string.
	 */
	public function test_remove_adjacent_characters( $string, $prev, $next, $result ) {
		$this->assertSame( $result, $this->invoke_static_method( Node_Fixes\Abstract_Node_Fix::class, 'remove_adjacent_characters', [ $string, 'mb_strlen', 'mb_substr', mb_strlen( $prev ), mb_strlen( $next ) ] ) );
	}
}
