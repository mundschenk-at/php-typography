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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes;
use PHP_Typography\Settings;

/**
 * Smart_Dashes_Hyphen_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Smart_Dashes_Hyphen_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Smart_Dashes_Hyphen_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Strings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 */
class Smart_Dashes_Hyphen_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Token_Fixes\Smart_Dashes_Hyphen_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$fix = new Token_Fixes\Smart_Dashes_Hyphen_Fix( true );

		$this->assert_attribute_same( Token_Fix::MIXED_WORDS, 'target', $fix, 'The fixer should be targetting MIXED_WORDS tokens.' );
		$this->assert_attribute_same( true, 'feed_compatible', $fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Provide data for testing wrap_hard_hyphens.
	 *
	 * @return array
	 */
	public function provide_wrap_hard_hyphens_data() {
		return [
			[ 'This-is-a-hyphenated-word', 'This&#8208;is&#8208;a&#8208;hyphenated&#8208;word' ],
			[ 'This-is-a-hyphenated-', 'This&#8208;is&#8208;a&#8208;hyphenated&#8208;' ],
			[ '-is-a-hyphenated', '&#8208;is&#8208;a&#8208;hyphenated' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_smart_dashes( true );

		// Need to add new test data for the smart dashes/hard hyphens combo.
		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_smart_dashes( false );

		$this->assertFixResultSame( $input, $input );
	}
}
