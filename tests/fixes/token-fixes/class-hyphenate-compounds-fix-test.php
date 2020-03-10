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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes;
use PHP_Typography\Settings;

/**
 * Hyphenate_Compounds_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Compounds_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Compounds_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Strings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Hyphenator\Cache
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 */
class Hyphenate_Compounds_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->fix = new Token_Fixes\Hyphenate_Compounds_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$fix = new Token_Fixes\Hyphenate_Compounds_Fix( null, true );

		$this->assertAttributeEquals( Token_Fix::COMPOUND_WORDS, 'target', $fix, 'The fixer should be targetting COMPOUND_WORDS tokens.' );
		$this->assertAttributeEquals( true, 'feed_compatible', $fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Provide data for testing hyphenation.
	 *
	 * @return array
	 */
	public function provide_hyphenate_data() {
		return [
			// Not working with new de pattern file: [ 'Sauerstoff-Feldflasche', 'Sau&shy;er&shy;stoff-Feld&shy;fla&shy;sche', 'de', true, true, true, true ],.
			[ 'Sauerstoff-Feldflasche', 'Sauer&shy;stoff-Feld&shy;fla&shy;sche', 'de', true, true, true, true ],
			[ 'Sauerstoff-Feldflasche', 'Sauerstoff-Feldflasche', 'de', true, true, true, false ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 * @param bool   $hyphenate_compunds   Hyphenate compound-words.
	 */
	public function test_apply( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds ) {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenate_compounds( $hyphenate_compunds );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertFixResultSame( $input, $result, false, $this->getTextnode( 'foo', $input ) );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 * @param bool   $hyphenate_compunds   Hyphenate compound-words.
	 */
	public function test_apply_off( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds ) {
		$this->s->set_hyphenation( false );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenate_compounds( $hyphenate_compunds );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertFixResultSame( $input, $input, false, $this->getTextnode( 'foo', $input ) );
	}
}
