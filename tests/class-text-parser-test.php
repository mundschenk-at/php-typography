<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2024 Peter Putzer.
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

use PHP_Typography\Exceptions\Invalid_Encoding_Exception;
use PHP_Typography\Text_Parser\Token;
use PHP_Typography\Text_Parser;

/**
 * Unit test for \PHP_Typography\Text_Parser class.
 *
 * @coversDefaultClass \PHP_Typography\Text_Parser
 * @usesDefaultClass \PHP_Typography\Text_Parser
 *
 * @uses PHP_Typography\Text_Parser::__construct
 * @uses PHP_Typography\Text_Parser\Token
 * @uses PHP_Typography\Strings::functions
 */
class Text_Parser_Test extends Testcase {

	/**
	 * Test load.
	 *
	 * @covers ::__construct
	 * @covers ::tokenize
	 * @covers ::parse_ambiguous_token
	 * @covers ::is_preceeded_by
	 * @covers ::is_not_preceeded_by
	 *
	 * @uses ::get_all
	 */
	public function test_load() {
		$too_long        = 'A really long string with a word that is wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwway too long.';
		$still_too_long  = 'A really long string with a word that is aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaalmost too long.';
		$almost_too_long = 'A really long string with a word that is aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaalmost too long.';

		// Previously, we didn't allow really long strings, but this is unnecessary with PHP.
		$this->assertInstanceOf( Text_Parser::class, new Text_Parser( $too_long ) );
		$this->assertInstanceOf( Text_Parser::class, new Text_Parser( $still_too_long ) );
		$this->assertInstanceOf( Text_Parser::class, new Text_Parser( $almost_too_long ) );

		$interesting = 'Quoth the raven, "nevermore"! Äöüß?';
		$parser      = new Text_Parser( $interesting );

		$tokens = $parser->get_all();

		$this->assertCount( 13, $tokens );
		$this->assert_token( 0, 'Quoth', Token::WORD, $tokens );
		$this->assert_token( 5, ',', Token::PUNCTUATION, $tokens );
		$this->assert_token( 11, 'Äöüß', Token::WORD, $tokens );

		return $parser;
	}

	/**
	 * Test load with email address.
	 *
	 * @covers ::__construct
	 * @covers ::tokenize
	 * @covers ::parse_ambiguous_token
	 * @covers ::is_preceeded_by
	 * @covers ::is_not_preceeded_by
	 *
	 * @uses ::get_all
	 */
	public function test_load_email() {
		$string = 'Quoth the raven, "nevermore"! Please mail to someone@example.org.';
		$parser = new Text_Parser( $string );

		$tokens = $parser->get_all();
		$this->assertCount( 19, $tokens );

		$this->assert_token( 0, 'Quoth', Token::WORD, $tokens );
		$this->assert_token( 5, ',', Token::PUNCTUATION, $tokens );
		$this->assert_token( 17, 'someone@example.org', Token::OTHER , $tokens );

		return $parser;
	}

	/**
	 * Test load with URL.
	 *
	 * @covers ::__construct
	 * @covers ::tokenize
	 * @covers ::parse_ambiguous_token
	 * @covers ::is_preceeded_by
	 * @covers ::is_not_preceeded_by
	 *
	 * @uses ::get_all
	 */
	public function test_load_url() {
		$string = 'Quoth the raven, "nevermore"! Please open http://example.org or foo:WordPress or foo:W@rdPress or @example or @:@:@:risk.';
		$parser = new Text_Parser( $string );

		$tokens = $parser->get_all();
		$this->assertCount( 33, $tokens );

		$this->assert_token( 0, 'Quoth', Token::WORD, $tokens );
		$this->assert_token( 5, ',', Token::PUNCTUATION, $tokens );
		$this->assert_token( 15, 'http://example.org', Token::OTHER, $tokens );
		$this->assert_token( 19, 'foo:WordPress', Token::OTHER, $tokens );
		$this->assert_token( 23, 'foo:W@rdPress', Token::OTHER, $tokens );
		$this->assert_token( 27, '@example', Token::OTHER, $tokens );
		$this->assert_token( 31, '@:@:@:risk', Token::OTHER, $tokens );

		return $parser;
	}

	/**
	 * Test load with a compound word.
	 *
	 * @covers ::__construct
	 * @covers ::tokenize
	 * @covers ::parse_ambiguous_token
	 * @covers ::is_preceeded_by
	 * @covers ::is_not_preceeded_by
	 *
	 * @uses ::get_all
	 */
	public function test_load_compound_word() {
		$string = 'Some don\'t trust the captain-owner.';
		$parser = new Text_Parser( $string );

		$tokens = $parser->get_all();
		$this->assertCount( 10, $tokens );

		$this->assert_token( 0, 'Some', Token::WORD, $tokens );
		$this->assert_token( 2, "don't", Token::OTHER, $tokens );
		$this->assert_token( 8, 'captain-owner', Token::WORD, $tokens );

		return $parser;
	}

	/**
	 * Test load with an invalid encoding.
	 *
	 * @covers ::__construct
	 */
	public function test_load_invalid_encoding() {
		$string = mb_convert_encoding( 'Ein längerer String im falschen Zeichensatz', 'ISO-8859-2' );

		$this->expect_exception( Invalid_Encoding_Exception::class );
		new Text_Parser( $string );
	}

	/**
	 * Test get_text.
	 *
	 * @covers ::get_text
	 *
	 * @uses ::is_not_preceeded_by
	 * @uses ::is_preceeded_by
	 * @uses ::__construct
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 */
	public function test_get_text() {
		$interesting = 'Quoth the raven, "nevermore"! Äöüß?';
		$parser      = new Text_Parser( $interesting );

		$result = $parser->get_text();

		$this->assertSame( $interesting, $result );
	}

	/**
	 * Test update.
	 *
	 * @covers ::update
	 *
	 * @uses ::get_all
	 * @uses ::is_not_preceeded_by
	 * @uses ::is_preceeded_by
	 * @uses ::__construct
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 * @uses ::get_text
	 */
	public function test_update() {
		$interesting = 'Quoth the raven, "nevermore"! Äöüß?';
		$parser      = new Text_Parser( $interesting );

		$tokens     = $parser->get_all();
		$tokens[12] = $tokens[12]->with_value( '' ); // "?".
		$tokens[11] = $tokens[11]->with_value( '' ); // "Äöüß".
		$tokens[10] = $tokens[10]->with_value( '' ); // " ".
		$tokens[9]  = $tokens[9]->with_value( $tokens[9]->value . '!' );
		$parser->update( $tokens );

		$this->assertSame( 'Quoth the raven, "nevermore"!!', $parser->get_text() );

		return $parser;
	}

	/**
	 * Test get_all.
	 *
	 * @covers ::get_all
	 *
	 * @uses ::__construct
	 * @uses ::is_not_preceeded_by
	 * @uses ::is_preceeded_by
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 */
	public function test_get_all() {
		$interesting = 'Quoth the raven, "nevermore"!';
		$parser      = new Text_Parser( $interesting );

		$tokens = $parser->get_all();
		$this->assertCount( 10, $tokens );

		return $parser;
	}

	/**
	 * Test get_spaces.
	 *
	 * @covers ::get_spaces
	 * @depends test_get_all
	 *
	 * @uses ::get_type
	 *
	 * @param \PHP_Typography\Text_Parser $parser The parser to use.
	 */
	public function test_get_spaces( Text_Parser $parser ) {
		$tokens = $parser->get_spaces();
		$this->assertCount( 3, $tokens );

		return $parser;
	}

	/**
	 * Test get_punctuation.
	 *
	 * @covers ::get_punctuation
	 * @depends test_get_all
	 *
	 * @uses ::get_type
	 *
	 * @param \PHP_Typography\Text_Parser $parser The parser to use.
	 */
	public function test_get_punctuation( Text_Parser $parser ) {
		$tokens = $parser->get_punctuation();
		$this->assertCount( 3, $tokens );

		return $parser;
	}

	/**
	 * Test get_words.
	 *
	 * @covers ::get_words
	 * @depends test_get_all
	 *
	 * @uses ::conforms_to_caps_policy
	 * @uses ::conforms_to_compounds_policy
	 * @uses ::conforms_to_letters_policy
	 * @uses ::check_policy
	 * @uses ::get_type
	 * @uses ::is_preceeded_by
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 *
	 * @param \PHP_Typography\Text_Parser $parser The parser to use.
	 */
	public function test_get_words( Text_Parser $parser ) {
		$tokens = $parser->get_words();
		$this->assertCount( 4, $tokens );

		$parser = new Text_Parser( 'A few m1xed W0RDS.' );
		$tokens = $parser->get_words( Text_Parser::REQUIRE_ALL_LETTERS, Text_Parser::NO_ALL_CAPS );
		$this->assertCount( 1, $tokens );
		$this->assert_contains_equals( new Token( 'few', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::ALLOW_ALL_LETTERS, Text_Parser::NO_ALL_CAPS );
		$this->assertCount( 2, $tokens );
		$this->assert_contains_equals( new Token( 'few', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'm1xed', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::NO_ALL_LETTERS, Text_Parser::NO_ALL_CAPS );
		$this->assertCount( 1, $tokens );
		$this->assert_contains_equals( new Token( 'm1xed', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::REQUIRE_ALL_LETTERS, Text_Parser::ALLOW_ALL_CAPS );
		$this->assertCount( 2, $tokens );
		$this->assert_contains_equals( new Token( 'A', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'few', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::ALLOW_ALL_LETTERS, Text_Parser::ALLOW_ALL_CAPS );
		$this->assertCount( 4, $tokens );
		$this->assert_contains_equals( new Token( 'A', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'few', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'm1xed', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'W0RDS', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::NO_ALL_LETTERS, Text_Parser::ALLOW_ALL_CAPS );
		$this->assertCount( 2, $tokens );
		$this->assert_contains_equals( new Token( 'm1xed', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'W0RDS', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::REQUIRE_ALL_LETTERS, Text_Parser::REQUIRE_ALL_CAPS );
		$this->assertCount( 1, $tokens );
		$this->assert_contains_equals( new Token( 'A', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::ALLOW_ALL_LETTERS, Text_Parser::REQUIRE_ALL_CAPS );
		$this->assertCount( 2, $tokens );
		$this->assert_contains_equals( new Token( 'A', Token::WORD ), $tokens, '' );
		$this->assert_contains_equals( new Token( 'W0RDS', Token::WORD ), $tokens, '' );

		$tokens = $parser->get_words( Text_Parser::NO_ALL_LETTERS, Text_Parser::REQUIRE_ALL_CAPS );
		$this->assertCount( 1, $tokens );
		$this->assert_contains_equals( new Token( 'W0RDS', Token::WORD ), $tokens, '' );
	}

	/**
	 * Providate data for testing conforms_to_letters_policy.
	 *
	 * @return array
	 */
	public function provide_conforms_to_letters_policy_data() {
		return [
			[ 'simple',   Token::WORD, Text_Parser::ALLOW_ALL_LETTERS, true ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::ALLOW_ALL_LETTERS, true ],
			[ 'simple',   Token::WORD, Text_Parser::NO_ALL_LETTERS, false ],
			[ 'simple99', Token::WORD, Text_Parser::NO_ALL_LETTERS, true ],
			[ 'simple',   Token::WORD, Text_Parser::REQUIRE_ALL_LETTERS, true ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::REQUIRE_ALL_LETTERS, true ],
			[ 'SIMPLE99', Token::WORD, Text_Parser::REQUIRE_ALL_LETTERS, false ],
			[ 'simple99', Token::WORD, Text_Parser::ALLOW_ALL_LETTERS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::ALLOW_ALL_LETTERS, true ],
			[ 'sim-ple',  Token::WORD, Text_Parser::NO_ALL_LETTERS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::NO_ALL_LETTERS, true ],
			[ 'sim-ple',  Token::WORD, Text_Parser::REQUIRE_ALL_LETTERS, false ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::REQUIRE_ALL_LETTERS, false ],
		];
	}

	/**
	 * Test conforms_to_letters_policy.
	 *
	 * @covers ::conforms_to_letters_policy
	 * @covers ::check_policy
	 * @dataProvider provide_conforms_to_letters_policy_data
	 *
	 * @uses ::__construct
	 * @uses ::is_preceeded_by
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 *
	 * @param string $value  Token value.
	 * @param int    $type   Token type.
	 * @param int    $policy Letters policy.
	 * @param bool   $result Expected result.
	 */
	public function test_conforms_to_letters_policy( $value, $type, $policy, $result ) {
		// Ensure that encoding can be determined.
		$parser = new Text_Parser( $value );
		$token  = new Token( $value, $type );

		$this->assertSame( $result, $this->invoke_method( $parser, 'conforms_to_letters_policy', [ $token, $policy ] ) );
	}

	/**
	 * Providate data for testing conforms_to_caps_policy.
	 *
	 * @return array
	 */
	public function provide_conforms_to_caps_policy_data() {
		return [
			[ 'simple',   Token::WORD, Text_Parser::ALLOW_ALL_CAPS, true ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::ALLOW_ALL_CAPS, true ],
			[ 'simple',   Token::WORD, Text_Parser::NO_ALL_CAPS, true ],
			[ 'simple99', Token::WORD, Text_Parser::NO_ALL_CAPS, true ],
			[ 'simple',   Token::WORD, Text_Parser::REQUIRE_ALL_CAPS, false ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::REQUIRE_ALL_CAPS, true ],
			[ 'SIMPLE99', Token::WORD, Text_Parser::REQUIRE_ALL_CAPS, true ],
			[ 'simple99', Token::WORD, Text_Parser::ALLOW_ALL_CAPS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::ALLOW_ALL_CAPS, true ],
			[ 'sim-ple',  Token::WORD, Text_Parser::NO_ALL_CAPS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::NO_ALL_CAPS, false ],
			[ 'sim-ple',  Token::WORD, Text_Parser::REQUIRE_ALL_CAPS, false ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::REQUIRE_ALL_CAPS, true ],
		];
	}

	/**
	 * Test conforms_to_caps_policy.
	 *
	 * @covers ::conforms_to_caps_policy
	 * @covers ::check_policy
	 * @dataProvider provide_conforms_to_caps_policy_data
	 *
	 * @uses ::__construct
	 * @uses ::is_preceeded_by
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 *
	 * @param string $value  Token value.
	 * @param int    $type   Token type.
	 * @param int    $policy All caps policy.
	 * @param bool   $result Expected result.
	 */
	public function test_conforms_to_caps_policy( $value, $type, $policy, $result ) {
		// Ensure that encoding can be determined.
		$parser = new Text_Parser( $value );
		$token  = new Token( $value, $type );

		$this->assertSame( $result, $this->invoke_method( $parser, 'conforms_to_caps_policy', [ $token, $policy ] ) );
	}

	/**
	 * Providate data for testing conforms_to_compounds_policy.
	 *
	 * @return array
	 */
	public function provide_conforms_to_compounds_policy() {
		return [
			[ 'simple',   Token::WORD, Text_Parser::ALLOW_COMPOUNDS, true ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::ALLOW_COMPOUNDS, true ],
			[ 'simple',   Token::WORD, Text_Parser::NO_COMPOUNDS, true ],
			[ 'simple99', Token::WORD, Text_Parser::NO_COMPOUNDS, true ],
			[ 'simple',   Token::WORD, Text_Parser::REQUIRE_COMPOUNDS, false ],
			[ 'SIMPLE',   Token::WORD, Text_Parser::REQUIRE_COMPOUNDS, false ],
			[ 'SIMPLE99', Token::WORD, Text_Parser::REQUIRE_COMPOUNDS, false ],
			[ 'simple99', Token::WORD, Text_Parser::ALLOW_COMPOUNDS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::ALLOW_COMPOUNDS, true ],
			[ 'sim-ple',  Token::WORD, Text_Parser::NO_COMPOUNDS, false ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::NO_COMPOUNDS, false ],
			[ 'sim-ple',  Token::WORD, Text_Parser::REQUIRE_COMPOUNDS, true ],
			[ 'SIM-PLE',  Token::WORD, Text_Parser::REQUIRE_COMPOUNDS, true ],
		];
	}

	/**
	 * Test conforms_to_compounds_policy.
	 *
	 * @covers ::conforms_to_compounds_policy
	 * @covers ::check_policy
	 * @dataProvider provide_conforms_to_compounds_policy
	 *
	 * @uses ::__construct
	 * @uses ::is_preceeded_by
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 *
	 * @param string $value  Token value.
	 * @param int    $type   Token type.
	 * @param int    $policy Compounds policy.
	 * @param bool   $result Expected result.
	 */
	public function test_conforms_to_compounds_policy( $value, $type, $policy, $result ) {
		// Ensure that encoding can be determined.
		$parser = new Text_Parser( $value );
		$token  = new Token( $value, $type );

		$this->assertSame( $result, $this->invoke_method( $parser, 'conforms_to_compounds_policy', [ $token, $policy ] ) );
	}

	/**
	 * Test get_other.
	 *
	 * @covers ::get_other
	 * @depends test_get_all
	 *
	 * @uses ::get_type
	 *
	 * @param \PHP_Typography\Text_Parser $parser The parser to use.
	 */
	public function test_get_other( Text_Parser $parser ) {
		$tokens = $parser->get_other();
		$this->assertCount( 0, $tokens );

		return $parser;
	}

	/**
	 * Test get_type.
	 *
	 * @covers ::get_type
	 *
	 * @uses ::get_all
	 * @uses ::is_preceeded_by
	 * @uses ::__construct
	 * @uses ::parse_ambiguous_token
	 * @uses ::tokenize
	 */
	public function test_get_type() {
		$parser = new Text_Parser( 'A few m1xed W0RDS.' );

		$words  = [];
		$tokens = $parser->get_all();
		foreach ( $tokens as $index => $token ) {
			if ( Token::WORD === $token->type ) {
				$words[ $index ] = $token;
			}
		}

		$this->assertSame( $words, $parser->get_type( Token::WORD ) );
	}
}
