<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

use PHP_Typography\Strings;
use PHP_Typography\U;
use PHP_Typography\Text_Parser\Token;

/**
 * Abstract base class for \PHP_Typography\* unit tests.
 *
 * @since 6.6.0 Renamed to Testcase
 */
abstract class Testcase extends \Mundschenk\PHPUnit_Cross_Version\TestCase {

	/**
	 * Helper function to generate a valid token list from strings.
	 *
	 * @param string $value The string to tokenize.
	 * @param int    $type  Optional. Default 'word'.
	 *
	 * @return array
	 */
	protected function tokenize( $value, $type = Token::WORD ) {
		return [
			new Token( $value, $type ),
		];
	}

	/**
	 * Helper function to generate a valid word token list from strings.
	 *
	 * @param string $value Token value.
	 *
	 * @return array
	 */
	protected function tokenize_sentence( $value ) {
		$words  = explode( ' ', $value );
		$tokens = [];

		foreach ( $words as $word ) {
			$tokens[] = new Token( $word, Token::WORD );
		}

		return $tokens;
	}

	/**
	 * Reports an error identified by $message if the token at $index does not have the $value or $type given.
	 *
	 * @since 6.6.0
	 *
	 * @param  int     $index         A token position.
	 * @param  string  $value         A token value.
	 * @param  int     $type          A Token type constant.
	 * @param  Token[] $actual_tokens An array of tokens.
	 * @param  string  $message       Optional. Default ''.
	 */
	protected function assert_token( $index, $value, $type, array $actual_tokens, $message = '' ) {
		$this->assertArrayHasKey( $index, $actual_tokens, 'Token index not found' );
		$this->assertEquals( new Token( $value, $type ), $actual_tokens[ $index ], $message );
	}

	/**
	 * Reports an error identified by $message if the combined token values differ from the expected value.
	 *
	 * @param string|array $expected_value Either a word/sentence or a token array.
	 * @param array        $actual_tokens  A token array.
	 * @param string       $message        Optional. Default ''.
	 */
	protected function assert_tokens_same( $expected_value, array $actual_tokens, $message = '' ) {
		$this->assertContainsOnlyInstancesOf( Token::class, $actual_tokens, '$actual_tokens has to be an array of tokens.' );
		foreach ( $actual_tokens as $index => $token ) {
			$actual_tokens[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		if ( is_scalar( $expected_value ) ) {
			if ( false !== strpos( $expected_value, ' ' ) ) {
				$expected_value = $this->tokenize_sentence( $expected_value );
			} else {
				$expected_value = $this->tokenize( $expected_value );
			}
		}

		// Ensure clean HTML even when a scalar was passed.
		$this->assertContainsOnlyInstancesOf( Token::class, $expected_value, '$expected_value has to be a string or an array of tokens.' );
		$expected = [];
		foreach ( $expected_value as $index => $token ) {
			$expected[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		foreach ( $actual_tokens as $key => $token ) {
			$this->assertSame( $expected[ $key ]->value, $token->value, $message );
			$this->assertSame( $expected[ $key ]->type,  $token->type,  $message );
		}

		return true;
	}

	/**
	 * Reports an error identified by $message if the combined token values do
	 * not differ from the expected value.
	 *
	 * @param string|array $expected_value Either a word/sentence or a token array.
	 * @param array        $actual_tokens  A token array.
	 * @param string       $message        Optional. Default ''.
	 */
	protected function assert_tokens_not_same( $expected_value, array $actual_tokens, $message = '' ) : void {
		$this->assertContainsOnlyInstancesOf( Token::class, $actual_tokens, '$actual_tokens has to be an array of tokens.' );
		foreach ( $actual_tokens as $index => $token ) {
			$actual_tokens[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		if ( is_scalar( $expected_value ) ) {
			if ( false !== strpos( $expected_value, ' ' ) ) {
				$expected = $this->tokenize_sentence( $expected_value );
			} else {
				$expected = $this->tokenize( $expected_value );
			}
		} else {
			$this->assertContainsOnlyInstancesOf( Token::class, (array) $expected_value, '$expected_value has to be a string or an array of tokens.' );
			$expected = $expected_value;
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		$result = false;
		foreach ( $actual_tokens as $key => $token ) {
			if ( $expected[ $key ]->value !== $token->value || $expected[ $key ]->type !== $token->type ) {
				$result = true;
			}
		}

		$this->assertTrue( $result, $message );
	}

	/**
	 * Assert that the given quote styles match.
	 *
	 * @param string $style Style name.
	 * @param string $open  Opening quote character.
	 * @param string $close Closing quote character.
	 */
	protected function assert_smart_quotes_style( $style, $open, $close ) {
		switch ( $style ) {
			case 'doubleCurled':
				$this->assertSame( U::DOUBLE_QUOTE_OPEN, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::DOUBLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleCurledReversed':
				$this->assertSame( U::DOUBLE_QUOTE_CLOSE, $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::DOUBLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleLow9':
				$this->assertSame( U::DOUBLE_LOW_9_QUOTE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::DOUBLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleLow9Reversed':
				$this->assertSame( U::DOUBLE_LOW_9_QUOTE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::DOUBLE_QUOTE_OPEN, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleCurled':
				$this->assertSame( U::SINGLE_QUOTE_OPEN, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleCurledReversed':
				$this->assertSame( U::SINGLE_QUOTE_CLOSE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleLow9':
				$this->assertSame( U::SINGLE_LOW_9_QUOTE, $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleLow9Reversed':
				$this->assertSame( U::SINGLE_LOW_9_QUOTE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_QUOTE_OPEN, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemetsFrench':
				$this->assertSame( U::GUILLEMET_OPEN . U::NO_BREAK_NARROW_SPACE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::NO_BREAK_NARROW_SPACE . U::GUILLEMET_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemets':
				$this->assertSame( U::GUILLEMET_OPEN, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::GUILLEMET_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemetsReversed':
				$this->assertSame( U::GUILLEMET_CLOSE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::GUILLEMET_OPEN, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleGuillemets':
				$this->assertSame( U::SINGLE_ANGLE_QUOTE_OPEN, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_ANGLE_QUOTE_CLOSE, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'singleGuillemetsReversed':
				$this->assertSame( U::SINGLE_ANGLE_QUOTE_CLOSE, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::SINGLE_ANGLE_QUOTE_OPEN, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'cornerBrackets':
				$this->assertSame( U::LEFT_CORNER_BRACKET, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::RIGHT_CORNER_BRACKET, $close, "Closeing quote $close did not match quote style $style." );
				break;

			case 'whiteCornerBracket':
				$this->assertSame( U::LEFT_WHITE_CORNER_BRACKET, $open, "Opening quote $open did not match quote style $style." );
				$this->assertSame( U::RIGHT_WHITE_CORNER_BRACKET, $close, "Closeing quote $close did not match quote style $style." );
				break;

			default:
				$this->assertTrue( false, "Invalid quote style $style." );
		}
	}
}
