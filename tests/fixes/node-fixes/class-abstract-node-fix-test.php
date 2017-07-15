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

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use \PHP_Typography\Fixes\Node_Fixes;
use \PHP_Typography\Settings;

/**
 * Abstract_Node_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Arrays
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
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->fix = new Node_Fixes\Style_Caps_Fix( 'caps' ); // Does not matter.
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
	 * @uses PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix::__construct
	 * @uses PHP_Typography\Fixes\Node_Fixes\HTML_Class_Node_Fix::__construct
	 * @param string $string A string.
	 * @param string $prev   The previous character.
	 * @param string $next   The next character.
	 * @param string $result The trimmed string.
	 */
	public function test_remove_adjacent_characters( $string, $prev, $next, $result ) {
		$this->assertSame( $result, $this->invokeStaticMethod( \PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::class, 'remove_adjacent_characters', [ $string, $prev, $next ] ) );
	}
}
