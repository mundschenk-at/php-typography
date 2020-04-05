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
 * Classes_Dependent_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix
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
class Classes_Dependent_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
	 */
	public function test_array_constructor() {
		$fix = $this->getMockForAbstractClass( Node_Fixes\Classes_Dependent_Fix::class, [ [ 'foo', 'bar' ], false ] );

		$this->assert_attribute_contains( 'foo',        'classes_to_avoid', $fix, 'The fixer should avoid class "foo".' );
		$this->assert_attribute_contains( 'bar',        'classes_to_avoid', $fix, 'The fixer should avoid class "bar".' );
		$this->assert_attribute_not_contains( 'foobar',  'classes_to_avoid', $fix, 'The fixer should not care about class "foobar".' );
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
	 */
	public function test_string_constructor() {
		$fix = $this->getMockForAbstractClass( Node_Fixes\Classes_Dependent_Fix::class, [ 'bar', false ] );

		$this->assert_attribute_contains( 'bar',    'classes_to_avoid', $fix, 'The fixer should avoid class "bar".' );
		$this->assert_attribute_not_contains( 'foo', 'classes_to_avoid', $fix, 'The fixer should not care about class "foobar".' );
	}
}
