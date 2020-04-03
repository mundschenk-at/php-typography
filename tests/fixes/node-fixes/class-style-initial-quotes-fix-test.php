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
use PHP_Typography\U;

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
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Style_Initial_Quotes_Fix( 'single', 'double' );
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->fix = new Node_Fixes\Style_Initial_Quotes_Fix( 'single', 'double' );

		$this->assert_attribute_same( 'single', 'single_quote_class', $this->fix );
		$this->assert_attribute_same( 'double', 'double_quote_class', $this->fix );
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
	 * @uses ::is_single_quote
	 * @uses ::is_double_quote
	 * @uses PHP_Typography\RE::escape_tags
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
	 * @uses ::is_single_quote
	 * @uses ::is_double_quote
	 * @uses PHP_Typography\RE::escape_tags
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

	/**
	 * Provides data for testing is_single_quote and is_double_quote.
	 *
	 * @return array
	 */
	public function provide_is_quote_data() {
		return [
			[ '"', false, true ],
			[ U::DOUBLE_QUOTE_OPEN, false, true ],
			[ U::GUILLEMET_OPEN, false, true ],
			[ U::GUILLEMET_CLOSE, false, true ],
			[ U::DOUBLE_LOW_9_QUOTE, false, true ],
			[ "'", true, false ],
			[ U::SINGLE_QUOTE_OPEN, true, false ],
			[ U::SINGLE_LOW_9_QUOTE, true, false ],
			[ U::SINGLE_ANGLE_QUOTE_OPEN, true, false ],
			[ U::SINGLE_ANGLE_QUOTE_CLOSE, true, false ],
			[ ',', true, false ],
			[ '!', false, false ],
			[ 'a', false, false ],
			[ '-', false, false ],
		];
	}

	/**
	 * Test is_single_quote.
	 *
	 * @covers ::is_single_quote
	 *
	 * @dataProvider provide_is_quote_data
	 *
	 * @param string $quote   Quote character.
	 * @param bool   $result  Expected result.
	 * @param bool   $ignored Ignored.
	 */
	public function test_is_single_quote( $quote, $result, $ignored ) {
		$this->assertSame( $result, $this->invoke_static_method( Node_Fixes\Style_Initial_Quotes_Fix::class, 'is_single_quote', [ $quote ] ) );
	}

	/**
	 * Test is_double_quote.
	 *
	 * @covers ::is_double_quote
	 *
	 * @dataProvider provide_is_quote_data
	 *
	 * @param string $quote   Quote character.
	 * @param bool   $ignored Ignored.
	 * @param bool   $result  Expected result.
	 */
	public function test_is_double_quote( $quote, $ignored, $result ) {
		$this->assertSame( $result, $this->invoke_static_method( Node_Fixes\Style_Initial_Quotes_Fix::class, 'is_double_quote', [ $quote ] ) );
	}
}
