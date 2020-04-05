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
 * Single_Character_Word_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Single_Character_Word_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Single_Character_Word_Spacing_Fix
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
class Single_Character_Word_Spacing_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Single_Character_Word_Spacing_Fix();
	}

	/**
	 * Provide data for testing single character word spacing.
	 *
	 * @return array
	 */
	public function provide_single_character_word_spacing_data() {
		return [
			[ 'A cat in a tree', 'A cat in a&nbsp;tree' ],
			[ 'Works with strange characters like ä too. But not Ä or does it?', 'Works with strange characters like &auml;&nbsp;too. But not &Auml;&nbsp;or does it?' ],
			[ 'B & E', 'B &amp;&nbsp;E' ],
			[ 'Der Mundschenk & Cie.', 'Der Mundschenk &amp;&nbsp;Cie.' ],
		];
	}

	/**
	 * Provide data for testing single character word spacing.
	 *
	 * @return array
	 */
	public function provide_single_character_word_spacing_with_siblings_data() {
		return [
			[ 'A cat in a tree', 'left with space ', '', 'A&nbsp;cat in a&nbsp;tree' ],
			[ 'Right here is a', '', ' hat', 'Right here is a' ], // change would happen in right sibling.
		];
	}


	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_single_character_word_spacing( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
	 *
	 * @dataProvider provide_single_character_word_spacing_with_siblings_data
	 *
	 * @param string $input  HTML input.
	 * @param string $left   Left sibling value.
	 * @param string $right  Right sibling value.
	 * @param string $result Expected result.
	 */
	public function test_apply_with_siblings( $input, $left, $right, $result ) {
		$this->s->set_single_character_word_spacing( true );

		$this->assertFixResultSame( $input, $result, $left, $right );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_single_character_word_spacing( false );

		$this->assertFixResultSame( $input, $input );
	}
}
