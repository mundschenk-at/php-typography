<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2022 Peter Putzer.
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

namespace PHP_Typography\Tests;

/**
 * Test Hyphenator class.
 *
 * @coversDefaultClass \PHP_Typography\Hyphenator
 * @usesDefaultClass \PHP_Typography\Hyphenator
 *
 * @uses PHP_Typography\Hyphenator
 */
class Hyphenator_Test extends Testcase {
	/**
	 * Hyphenator fixture.
	 *
	 * @var \PHP_Typography\Hyphenator
	 */
	protected $h;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->h = new \PHP_Typography\Hyphenator();
	}

	/**
	 * Tests __construct.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 * @uses PHP_Typography\Hyphenator\Trie_Node::__construct
	 * @uses PHP_Typography\Hyphenator\Trie_Node::build_trie
	 * @uses PHP_Typography\Hyphenator\Trie_Node::get_node
	 */
	public function test_constructor() {
		$h = $this->h;

		$this->assertNotNull( $h );
		$this->assertInstanceOf( '\PHP_Typography\Hyphenator', $h );

		$h2 = new \PHP_Typography\Hyphenator( 'en-US', [ 'foo-bar' ] );
		$this->assertNotNull( $h2 );
		$this->assertInstanceOf( '\PHP_Typography\Hyphenator', $h2 );
		$this->assert_attribute_same( 'en-US', 'language', $h2 );
		$this->assert_attribute_count( 1, 'custom_exceptions', $h2 );
	}

	/**
	 * Tests set_language.
	 *
	 * @covers ::set_language
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node::__construct
	 * @uses PHP_Typography\Hyphenator\Trie_Node::build_trie
	 * @uses PHP_Typography\Hyphenator\Trie_Node::get_node
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_set_language() {
		$h = $this->h;
		$h->set_language( 'en-US' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty English-US pattern array' );
		$this->assert_attribute_not_empty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

		$h->set_language( 'foobar' );
		$this->assert_attribute_empty( 'pattern_trie', $h );
		$this->assert_attribute_empty( 'pattern_exceptions', $h );

		$h->set_language( 'no' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty Norwegian pattern array' );
		$this->assert_attribute_not_empty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' ); // Norwegian has exceptions.

		$h->set_language( 'de' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty German pattern array' );
		$this->assert_attribute_empty( 'pattern_exceptions', $h, 'Unexpected pattern exceptions found' ); // no exceptions in the German pattern file.
	}

	/**
	 * Tests set_language.
	 *
	 * @covers ::set_language
	 *
	 * @uses ::set_custom_exceptions
	 * @uses ::merge_hyphenation_exceptions
	 * @uses PHP_Typography\Hyphenator\Trie_Node::__construct
	 * @uses PHP_Typography\Hyphenator\Trie_Node::build_trie
	 * @uses PHP_Typography\Hyphenator\Trie_Node::get_node
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_set_language_with_custom_exceptions() {
		$h = $this->h;

		$h->set_custom_exceptions(
			[
				'KINGdesk' => 'KING-desk',
			]
		);
		$h->set_language( 'en-US' );
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty pattern array' );
		$this->assert_attribute_not_empty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

		$h->set_language( 'de' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty pattern array' );
		$this->assert_attribute_empty( 'pattern_exceptions', $h, 'Unexpected pattern exceptions found' ); // no exceptions in the German pattern file.
	}

	/**
	 * Tests set_language.
	 *
	 * @covers ::set_language
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node::__construct
	 * @uses PHP_Typography\Hyphenator\Trie_Node::build_trie
	 * @uses PHP_Typography\Hyphenator\Trie_Node::get_node
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_set_same_hyphenation_language() {
		$h = $this->h;

		$h->set_language( 'en-US' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty pattern array' );
		$this->assert_attribute_not_empty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

		$h->set_language( 'en-US' );
		$this->assert_attribute_not_empty( 'pattern_trie', $h, 'Empty pattern array' );
		$this->assert_attribute_not_empty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );
	}

	/**
	 * Provides data for testing set_custom_exceptions.
	 *
	 * @return array
	 */
	public function provide_set_custom_exceptions_data() {
		return [
			[ [ 'Hu-go', 'Fö-ba-ß' ], 2, 2 ],
			[ [],                     0, 2 ],
		];
	}

	/**
	 * Tests set_custom_exceptions.
	 *
	 * @covers ::set_custom_exceptions
	 *
	 * @uses PHP_Typography\Strings::functions
	 *
	 * @dataProvider provide_set_custom_exceptions_data
	 *
	 * @param array $exceptions Custom exceptions.
	 * @param int   $count      Number of exceptions to expect.
	 * @param int   $times      Number of iterations.
	 */
	public function test_set_custom_exceptions( $exceptions, $count, $times ) {
		$h = $this->h;

		for ( $i = 0; $i < $times; ++$i ) {
			$h->set_custom_exceptions( $exceptions );

			if ( ! empty( $exceptions ) ) {

				// Exceptions have to be strings.
				$this->assert_attribute_contains_only( 'string', 'custom_exceptions', $h );

				// Assert count.
				$this->assert_attribute_count( $count, 'custom_exceptions', $h );
			} else {
				$this->assert_attribute_empty( 'custom_exceptions', $h );
			}

			// Assert existence of individual exceptions.
			foreach ( $exceptions as $exception ) {
				$exception = mb_strtolower( $exception ); // Exceptions are stored all lowercase.
				$this->assert_attribute_contains( $exception, 'custom_exceptions', $h, "Exception $exception not found in round $i" );
			}
		}
	}

	/**
	 * Tests set_custom_exceptions.
	 *
	 * @covers ::set_custom_exceptions
	 *
	 * @uses ::merge_hyphenation_exceptions
	 * @uses PHP_Typography\Hyphenator\Trie_Node::__construct
	 * @uses PHP_Typography\Hyphenator\Trie_Node::build_trie
	 * @uses PHP_Typography\Hyphenator\Trie_Node::get_node
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_set_custom_exceptions_again() {
		$h          = $this->h;
		$exceptions = [ 'Hu-go', 'Fö-ba-ß' ];
		$h->set_custom_exceptions( $exceptions );
		$h->set_language( 'de' ); // German has no pattern exceptions.
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_not_empty( 'merged_exception_patterns', $h );

		$exceptions = [ 'Hu-go' ];
		$h->set_custom_exceptions( $exceptions );
		$this->assert_attribute_empty( 'merged_exception_patterns', $h );

		$this->assert_attribute_contains_only( 'string', 'custom_exceptions', $h );
		$this->assert_attribute_contains( 'hu-go', 'custom_exceptions', $h );
		$this->assert_attribute_count( 1, 'custom_exceptions', $h );
	}

	/**
	 * Tests set_custom_exceptions.
	 *
	 * @covers ::set_custom_exceptions
	 *
	 * @uses PHP_Typography\Strings::functions
	 */
	public function test_set_custom_exceptions_unknown_encoding() {
		$h          = $this->h;
		$exceptions = [ 'Hu-go', mb_convert_encoding( 'Fö-ba-ß', 'ISO-8859-2' ) ];
		$h->set_custom_exceptions( $exceptions );

		$this->assert_attribute_contains_only( 'string', 'custom_exceptions', $h );
		$this->assert_attribute_contains( 'hu-go', 'custom_exceptions', $h );
		$this->assert_attribute_not_contains( 'fö-ba-ß', 'custom_exceptions', $h );
		$this->assert_attribute_count( 1, 'custom_exceptions', $h );
	}

	/**
	 * Provide data for testing hyphenation.
	 *
	 * @return array
	 */
	public function provide_hyphenate_data() {
		return [
			[ 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here', 'A few words to hy|phen|ate like KING|desk Re|al|ly there should be more hy|phen|ation here', 'en-US', true ], // fake tokenizer doesn't split off punctuation.
			[ 'Sauerstofffeldflasche', 'Sau|er|stoff|feld|fla|sche', 'de', true ],
			[ 'Sauerstoff Feldflasche', 'Sau|er|stoff Feld|fla|sche', 'de', true ], // Compound words would not be hyphenated separately.
			[ 'Sauerstoff-Feldflasche', 'Sauerstoff-Feldflasche', 'de', false ],
			[ 'A', 'A', 'de', true ],
			[ 'table', 'ta|ble', 'en-US', false ],
			[ 'KINGdesk', 'KINGdesk', 'en-US', false ],
			[ 'italienisch', 'ita|lie|nisch', 'de', false ],
		];
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::mb_str_split
	 * @uses PHP_Typography\Strings::functions
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param  string $html                 HTML input.
	 * @param  string $result               Expected result.
	 * @param  string $lang                 Language code.
	 * @param  bool   $hyphenate_title_case Hyphenate words in Title Case.
	 */
	public function test_hyphenate( $html, $result, $lang, $hyphenate_title_case ) {
		$h = $this->h;
		$h->set_language( $lang );
		$h->set_custom_exceptions( [ 'KING-desk' ] );

		$this->assert_tokens_same( $result, $h->hyphenate( $this->tokenize_sentence( $html ), '|', $hyphenate_title_case, 2, 2, 2 ) );
	}

	/**
	 * Provide data for tessting hyphenation with custom exceptions.
	 *
	 * @return array
	 */
	public function provide_hyphenate_with_exceptions_data() {
		return [
			[ 'KINGdesk', 'KING|desk', [ 'KING-desk' ], 'en-US', true ],
			[ 'Geschäftsübernahme', 'Ge|sch&auml;fts|&uuml;ber|nah|me', [], 'de', true ],
			[ 'Geschäftsübernahme', 'Ge|sch&auml;fts|&uuml;ber|nah|me', [ 'Ge-schäfts-über-nah-me' ], 'de', true ],
			[ 'Trinkwasserinstallation', 'Trink|was|ser|in|stal|la|ti|on', [], 'de', true, true, true, false ],
			[ 'Trinkwasserinstallation', 'Trink|wasser|in|stal|la|tion', [ 'Trink-wasser-in-stal-la-tion' ], 'de', true ],
			[ 'Trinkwasserinstallation', 'Trink|wasser|in|stal|la|tion', [ 'Trink-wasser-in-stal-la-tion' ], 'en-US', true ],
		];
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::mb_str_split
	 * @uses PHP_Typography\Strings::functions
	 *
	 * @dataProvider provide_hyphenate_with_exceptions_data
	 *
	 * @param  string $html                 HTML input.
	 * @param  string $result               Expected result.
	 * @param  array  $exceptions           Custom hyphenation exceptions.
	 * @param  string $lang                 Language code.
	 * @param  bool   $hyphenate_title_case Hyphenate words in Title Case.
	 */
	public function test_hyphenate_with_exceptions( $html, $result, $exceptions, $lang, $hyphenate_title_case ) {
		$h = $this->h;
		$h->set_language( $lang );
		$h->set_custom_exceptions( $exceptions );

		$this->assert_tokens_same( $result, $h->hyphenate( $this->tokenize_sentence( $html ), '|', $hyphenate_title_case, 2, 2, 2 ) );
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_hyphenate_wrong_encoding() {
		$this->h->set_language( 'de' );

		$tokens     = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
		$hyphenated = $this->h->hyphenate( $tokens, '|', true, 2, 2, 2 );
		$this->assert_tokens_same( $hyphenated, $tokens, 'Wrong encoding, value should be unchanged.' );

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->h->hyphenate( $tokens, '|', true, 2, 2, 2 );
		$this->assert_tokens_not_same( $hyphenated, $tokens, 'Correct encoding, string should have been hyphenated.' );
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_hyphenate_no_title_case() {
		$this->h->set_language( 'de' );

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->h->hyphenate( $tokens, '|', false, 2, 2, 2 );
		$this->assertEquals( $tokens, $hyphenated );
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_hyphenate_invalid() {
		$this->h->set_language( 'de' );

		$tokens     = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->h->hyphenate( $tokens,  '|', true, 2, 0, 2 );
		$this->assertEquals( $tokens, $hyphenated );
	}

	/**
	 * Tests lookup_word_pattern.
	 *
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_lookup_word_pattern_invalid_pattern_trie() {
		$string = 'unknown';

		// Make pattern trie invalid.
		$this->set_value( $this->h, 'pattern_trie', null );

		$this->assertSame( [], $this->invoke_method( $this->h, 'lookup_word_pattern', [ $string, 'strlen', 'str_split' ] ) );
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_hyphenate_no_custom_exceptions() {
		$this->h->set_language( 'en-US' );

		// Again, no punctuation due to the fake tokenization.
		$this->assert_tokens_same(
			'A few words to hy|phen|ate like KINGdesk Re|al|ly there should be more hy|phen|ation here',
			$this->h->hyphenate( $this->tokenize_sentence( 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here' ), '|', true, 2, 2, 2 )
		);
	}

	/**
	 * Tests hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::hyphenate_word
	 * @covers ::lookup_word_pattern
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Strings::functions
	 * @uses PHP_Typography\Strings::mb_str_split
	 */
	public function test_hyphenate_no_exceptions_at_all() {
		$this->h->set_language( 'en-US' );

		// Unset some internal stuff.
		$ref  = new \ReflectionClass( '\PHP_Typography\Hyphenator' );
		$prop = $ref->getProperty( 'pattern_exceptions' );
		$prop->setAccessible( true );
		$prop->setValue( $this->h, [] );
		$prop = $ref->getProperty( 'merged_exception_patterns' );
		$prop->setAccessible( true );
		$prop->setValue( $this->h, null );

		// Again, no punctuation due to the fake tokenization.
		$this->assert_tokens_same(
			'A few words to hy|phen|ate like KINGdesk Re|al|ly there should be more hy|phen|ation here',
			$this->h->hyphenate( $this->tokenize_sentence( 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here' ), '|', true, 2, 2, 2 )
		);
	}

	/**
	 * Tests convert_hyphenation_exception_to_pattern.
	 *
	 * @covers ::convert_hyphenation_exception_to_pattern
	 *
	 * @uses PHP_Typography\Strings::functions
	 */
	public function test_convert_hyphenation_exception_to_pattern() {
		$h = $this->h;
		$this->assertSame( [ 4 => 9 ], $this->invoke_method( $h, 'convert_hyphenation_exception_to_pattern', [ 'KING-desk' ] ) );
		$this->assertSame( [ 2 => 9 ], $this->invoke_method( $h, 'convert_hyphenation_exception_to_pattern', [ 'ta-ble' ] ) );
	}

	/**
	 * Tests convert_hyphenation_exception_to_pattern.
	 *
	 * @covers ::convert_hyphenation_exception_to_pattern
	 *
	 * @uses PHP_Typography\Strings::functions
	 */
	public function test_convert_hyphenation_exception_to_pattern_unknown_encoding() {
		$h         = $this->h;
		$exception = mb_convert_encoding( 'Fö-ba-ß' , 'ISO-8859-2' );

		$this->assertNull( $this->invoke_method( $h, 'convert_hyphenation_exception_to_pattern', [ $exception ] ) );
	}

	/**
	 * Tests merge_hyphenation_exceptions.
	 *
	 * @covers ::merge_hyphenation_exceptions
	 *
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Strings::mb_str_split
	 * @uses PHP_Typography\Strings::functions
	 */
	public function test_merge_hyphenation_exceptions() {
		$h = $this->h;
		$h->set_custom_exceptions( [ 'Hu-go', 'Fä-vi-ken' ] );

		$h->set_language( 'en-US' ); // w/ pattern exceptions.
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_not_count( 0, 'merged_exception_patterns', $h );
		$this->assert_attribute_not_count( 1, 'merged_exception_patterns', $h );
		$this->assert_attribute_not_count( 2, 'merged_exception_patterns', $h );
		$this->assert_attribute_array_has_key( 'hugo', 'merged_exception_patterns', $h );
		$this->assert_attribute_array_has_key( 'fäviken', 'merged_exception_patterns', $h );

		$h->set_language( 'de' ); // w/o pattern exceptions.
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_count( 2, 'merged_exception_patterns', $h );
		$this->assert_attribute_array_has_key( 'hugo', 'merged_exception_patterns', $h );
		$this->assert_attribute_array_has_key( 'fäviken', 'merged_exception_patterns', $h );

		$h->set_language( 'en-US' ); // w/ pattern exceptions.
		$h->set_custom_exceptions( [] );
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_not_count( 0, 'merged_exception_patterns', $h );
		$this->assert_attribute_array_not_has_key( 'hugo', 'merged_exception_patterns', $h );
		$this->assert_attribute_array_not_has_key( 'fäviken', 'merged_exception_patterns', $h );

		$h->set_language( 'de' ); // w/o pattern exceptions.
		$this->invoke_method( $h, 'merge_hyphenation_exceptions', [] );
		$this->assert_attribute_count( 0, 'merged_exception_patterns', $h );
		$this->assert_attribute_array_not_has_key( 'hugo', 'merged_exception_patterns', $h );
		$this->assert_attribute_array_not_has_key( 'fäviken', 'merged_exception_patterns', $h );
	}

	/**
	 * Provide data for testing is_odd.
	 *
	 * @return array
	 */
	public function provide_is_odd_data() {
		return [
			[ 0, false ],
			[ 1, true ],
			[ 2, false ],
			[ 5, true ],
			[ 68, false ],
			[ 781, true ],
		];
	}

	/**
	 * Test is_odd.
	 *
	 * @covers ::is_odd
	 * @dataProvider provide_is_odd_data
	 *
	 * @param  int  $number A number.
	 * @param  bool $result Expected result.
	 */
	public function test_is_odd( $number, $result ) {
		if ( $result ) {
			$this->assertTrue( $this->invoke_static_method( \PHP_Typography\Hyphenator::class, 'is_odd', [ $number ] ) );
		} else {
			$this->assertFalse( $this->invoke_static_method( \PHP_Typography\Hyphenator::class, 'is_odd', [ $number ] ) );
		}
	}

	/**
	 * Test get_object_hash function.
	 *
	 * @covers ::get_object_hash
	 */
	public function test_get_object_hash() {
		$hash1 = $this->invoke_static_method( \PHP_Typography\Hyphenator::class, 'get_object_hash', [ 666 ] );
		$this->assert_is_string( $hash1 );
		$this->assertGreaterThan( 0, strlen( $hash1 ) );

		$hash2 = $this->invoke_static_method( \PHP_Typography\Hyphenator::class, 'get_object_hash', [ new \stdClass() ] );
		$this->assert_is_string( $hash2 );
		$this->assertGreaterThan( 0, strlen( $hash2 ) );

		$this->assertNotEquals( $hash1, $hash2 );
	}
}
