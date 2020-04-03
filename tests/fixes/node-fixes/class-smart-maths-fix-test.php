<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2019 Peter Putzer.
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
 * Smart_Maths_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Maths_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Maths_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Smart_Maths_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Smart_Maths_Fix();
	}

	/**
	 * Data provider for smarth_math test.
	 *
	 * @return array
	 */
	public function provide_smart_maths_data() {
		return [
			[ 'xx 7-3=4 xx',      'xx 7&minus;3=4 xx' ],
			[ 'xx 3*3=5/2 xx',    'xx 3&times;3=5&divide;2 xx' ],
			[ 'xx 3.5-1.5=2 xx',  'xx 3.5&minus;1.5=2 xx' ],
			[ 'xx 3,5-1,5=2 xx',  'xx 3,5&minus;1,5=2 xx' ],
			[ 'xx 3-1.5=2 xx',    'xx 3&minus;1.5=2 xx' ],
			[ 'xx 3-1,5=2 xx',    'xx 3&minus;1,5=2 xx' ],
			[ '(i.e. pp. 46-50)', '(i.e. pp. 46-50)' ],
			[ 'xx 0815-4711 xx',  'xx 0815-4711 xx' ],
			[ 'xx 1/2 xx',        'xx 1/2 xx' ],
			[ 'xx 2001-13-12 xx', 'xx 2001&minus;13&minus;12 xx' ],   // not a valid date.
			[ 'xx 2001-12-13 xx', 'xx 2001-12-13 xx' ],
			[ 'xx 2001-12-3 xx',  'xx 2001-12-3 xx' ],
			[ 'xx 2001-13-13 xx', 'xx 2001&minus;13&minus;13 xx' ],   // not a valid date.
			[ 'xx 13-12-2002 xx', 'xx 13-12-2002 xx' ],
			[ 'xx 13-13-2002 xx', 'xx 13&minus;13&minus;2002 xx' ],   // not a valid date.
			[ 'xx 2001-12 xx',    'xx 2001-12 xx' ],
			[ 'xx 2001-13 xx',    'xx 2001-13 xx' ],                  // Parsed as interval.
			[ 'xx 2001-100 xx',   'xx 2001-100 xx' ],
			[ 'xx 12/13/2010 xx', 'xx 12/13/2010 xx' ],
			[ 'xx 13/12/2010 xx', 'xx 13/12/2010 xx' ],
			[ 'xx 13/13/2010 xx', 'xx 13&divide;13&divide;2010 xx' ], // not a valid date.
			[ 'xx 12/10/89 xx',      'xx 12/10/89 xx' ],
			[ 'xx&nbsp;12/10/89 xx', 'xx&nbsp;12/10/89 xx' ],
			[ 'xx 12/10/89&nbsp;xx', 'xx 12/10/89&nbsp;xx' ],
			[ 'xx 13/12/89 xx',      'xx 13/12/89 xx' ],
			[ 'xx 13/12/89 xx',      'xx 13/12/89 xx' ],
			[ 'xx 13/13/89 xx',      'xx 13&divide;13&divide;89 xx' ], // not a valid date.
			[ 'xx.13/13/89 xx',      'xx.13/13/89 xx' ],
			[ 'xx 1/1/89 xx',        'xx 1/1/89 xx' ],
			[ 'xx 18/12/1 xx',       'xx 18/12/1 xx' ],
			[ 'xx 1/99/89 xx',       'xx 1&divide;99&divide;89 xx' ],  // not a valid date.
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_maths_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_smart_math( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_maths_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_smart_math( false );

		$this->assertFixResultSame( $input, $input );
	}
}
