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
 * Style_Initial_Quotes_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Initial_Quotes_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Initial_Quotes_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
 * @uses PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix::__construct
 * @uses PHP_Typography\Arrays
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Style_Initial_Quotes_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->fix = new Node_Fixes\Style_Initial_Quotes_Fix( 'single', 'double' );
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->fix = new Node_Fixes\Style_Initial_Quotes_Fix( 'single', 'double' );

		$this->assertAttributeEquals( 'single', 'single_quote_class', $this->fix );
		$this->assertAttributeEquals( 'double', 'double_quote_class', $this->fix );
	}

	/**
	 * Provide data for testing initial quotes' styling.
	 *
	 * @return array
	 */
	public function provide_style_initial_quotes_data() {
		return [
			[ 'no quote', '', '', 'no quote', false ],
			[ '"double quote"', '', 'x', '<span class="double">"</span>double quote"', false ], // right sibling forces <p> tag.
			[ "'single quote'", '', 'x', "<span class=\"single\">'</span>single quote'", false ], // right sibling forces <p> tag.
			[ '"no title quote"', '', '', '"no title quote"', false ],
			[ '"title quote"', '', '', '<span class="double">"</span>title quote"', true ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $input    HTML input.
	 * @param string $left     Left sibling.
	 * @param string $right    Right sibling.
	 * @param string $result   Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_apply_internal( $input, $left, $right, $result, $is_title ) {
		$this->s->set_style_initial_quotes( true );
		$this->s->set_initial_quote_tags();

		$this->assertFixResultSame( $input, $result, $left, $right, 'p', $is_title );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $input  HTML input.
	 * @param string $left   Left sibling.
	 * @param string $right  Right sibling.
	 * @param string $result Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_apply_internal_off( $input, $left, $right, $result, $is_title ) {
		$this->s->set_style_initial_quotes( false );
		$this->s->set_initial_quote_tags();

		$this->assertFixResultSame( $input, $input, $left, $right, 'p', $is_title );
	}
}
