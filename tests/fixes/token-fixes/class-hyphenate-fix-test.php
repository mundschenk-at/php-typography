<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2022 Peter Putzer.
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
 * Hyphenate_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
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
 */
class Hyphenate_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Our test object.
	 *
	 * @var Token_Fixes\Hyphenate_Fix
	 */
	protected $fix;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Token_Fixes\Hyphenate_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$fix = new Token_Fixes\Hyphenate_Fix( null, Token_Fix::COMPOUND_WORDS, true );

		$this->assert_attribute_same( Token_Fix::COMPOUND_WORDS, 'target', $fix, 'The fixer should be targetting COMPOUND_WORDS tokens.' );
		$this->assert_attribute_same( true, 'feed_compatible', $fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Strings
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( true );

		$tokens     = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
		$hyphenated = $this->invoke_method( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assert_tokens_same( $hyphenated, $tokens );

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invoke_method( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assert_tokens_not_same( $hyphenated, $tokens, 'Different encodings should not be equal.' );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Strings
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate_no_title_case() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( false );

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invoke_method( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertEquals( $tokens, $hyphenated );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate_invalid() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( false );

		$this->s[ Settings::HYPHENATION_MIN_BEFORE ] = 0; // invalid value.

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invoke_method( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertEquals( $tokens, $hyphenated );
	}

	/**
	 * Test get_hyphenator.
	 *
	 * @covers ::get_hyphenator()
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 * @uses PHP_Typography\Hyphenator::get_object_hash
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 */
	public function test_get_hyphenator() {
		$this->s[ Settings::HYPHENATION_MIN_LENGTH ]        = 2;
		$this->s[ Settings::HYPHENATION_MIN_BEFORE ]        = 2;
		$this->s[ Settings::HYPHENATION_MIN_AFTER ]         = 2;
		$this->s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] = [ 'foo-bar' ];
		$this->s[ Settings::HYPHENATION_LANGUAGE ]          = 'en-US';

		$h = $this->fix->get_hyphenator( $this->s );
		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );

		$this->s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] = [ 'bar-foo' ];

		$h = $this->fix->get_hyphenator( $this->s );
		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );
	}

	/**
	 * Test set_hyphenator_cache.
	 *
	 * @covers ::set_hyphenator_cache()
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 */
	public function test_set_hyphenator_cache() {

		// Initial set-up.
		$internal_cache = $this->get_value( $this->fix, 'cache' );
		$cache          = new \PHP_Typography\Hyphenator\Cache();

		$this->fix->set_hyphenator_cache( $cache );

		// Retrieve cache and assert results.
		$this->assertNotSame( $cache, $internal_cache );
		$this->assertSame( $cache, $this->get_value( $this->fix, 'cache' ) );
	}


	/**
	 * Provide data for testing hyphenation.
	 *
	 * @return array
	 */
	public function provide_hyphenate_data() {
		return [
			[ 'A few words to hyphenate, like KINGdesk Really, there should be more hyphenation here!', 'A few words to hy&shy;phen&shy;ate, like KING&shy;desk Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!', 'en-US', true, true, true, 'p' ],
			[ 'Sauerstofffeldflasche', 'Sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche', 'de', true, true, true, 'p' ],
			[ 'Geschäftsübernahme', 'Ge&shy;sch&auml;fts&shy;&uuml;ber&shy;nah&shy;me', 'de', true, true, true, 'p' ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', 'de', true, true, true, 'p' ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', 'de', true, true, true, 'h2' ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', 'de', false, true, true, 'p' ],
			[ 'Trinkwasserinstallation', 'Trinkwasserinstallation', 'de', false, true, true, 'h2' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::do_hyphenate
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\DOM
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
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
	 * @param string $parent_tag           Parent tag.
	 */
	public function test_apply( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $parent_tag ) {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$parent = new \DOMElement( $parent_tag, $input );

		/**
		 * Always a \DOMText.
		 *
		 * @var \DOMText
		 */
		$textnode = $parent->firstChild;

		$this->assertFixResultSame( $input, $result, false, $textnode );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::do_hyphenate
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
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
	 */
	public function test_apply_off( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case ) {
		$this->s->set_hyphenation( false );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertFixResultSame( $input, $input );
	}
}
