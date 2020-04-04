<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
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

namespace PHP_Typography\Tests\Text_Parser;

use PHP_Typography\Tests\Testcase;
use PHP_Typography\Text_Parser\Token;

/**
 * Token_Test unit test.
 *
 * @coversDefaultClass \PHP_Typography\Text_Parser\Token
 * @usesDefaultClass \PHP_Typography\Text_Parser\Token
 */
class Token_Test extends Testcase {

	/**
	 * Provide value pairs for testing the Token constructor.
	 *
	 * @return array
	 */
	public function provide___construct_data() {
		return [
			[ 'word', Token::WORD ],
			[ '',     Token::WORD ],        // empty string.
			[ ' ',    Token::SPACE ],       // space.
			[ '!?;',  Token::PUNCTUATION ],
		];
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @dataProvider provide___construct_data
	 *
	 * @param  string $value The token value.
	 * @param  int    $type  The token type.
	 */
	public function test___construct( $value, $type ) {
		$token = new Token( $value, $type );

		$this->assertInstanceOf( Token::class, $token );
	}

	/**
	 * Provide value pairs for testing the Token constructor.
	 *
	 * @return array
	 */
	public function provide___construct_with_exception_data() {
		return [
			[ 'word', 666 ],
			[ 'word', -1 ],
			[ null,   Token::WORD ],
			[ null,   -1000 ],
		];
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @dataProvider provide___construct_with_exception_data
	 *
	 * @param  string $value The token value.
	 * @param  int    $type  The token type.
	 */
	public function test___construct_with_exception( $value, $type ) {
		$this->expect_exception( \UnexpectedValueException::class );

		$token = new Token( $value, $type );

		$this->assertInstanceOf( Token::class, $token );
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @dataProvider provide___construct_data
	 *
	 * @param  string $value The token value.
	 * @param  int    $type  The token type.
	 */
	public function test___construct_called_twice( $value, $type ) {
		$this->expect_exception( \BadMethodCallException::class );

		$token = new Token( $value, $type );
		$token->__construct( 'foo', 0 );
	}

	/**
	 * Provide value pairs for testing the __get method.
	 *
	 * @return array
	 */
	public function provide___get_data() {
		return [
			[ 'word', Token::WORD, 'value',   'word' ],
			[ 'word', Token::WORD, 'type',    Token::WORD ],
			[ 'word', Token::WORD, 'mutable', false ],
			[ 'word', Token::WORD, 'foobar',  null ],
		];
	}

	/**
	 * Test __get.
	 *
	 * @covers ::__get
	 *
	 * @uses ::__construct
	 *
	 * @dataProvider provide___get_data
	 *
	 * @param  string $value    Token value.
	 * @param  int    $type     Token type.
	 * @param  string $property Property name.
	 * @param  mixed  $result   Expected result.
	 */
	public function test___get_successful( $value, $type, $property, $result ) {
		$token = new Token( $value, $type );

		$this->assertSame( $result, $token->$property );
	}

	/**
	 * Provide value pairs for testing the __set method.
	 *
	 * @return array
	 */
	public function provide___set_data() {
		return [
			[ 'word', Token::WORD, 'value',   'something' ],
			[ 'word', Token::WORD, 'type',    Token::PUNCTUATION ],
			[ 'word', Token::WORD, 'mutable', true ],
			[ 'word', Token::WORD, 'foo',     'bar' ],
		];
	}

	/**
	 * Test __set.
	 *
	 * @uses ::__construct
	 *
	 * @dataProvider provide___set_data
	 *
	 * @param  string $value     Token value.
	 * @param  int    $type      Token type.
	 * @param  string $property  Property name.
	 * @param  mixed  $new_value Property value to set.
	 */
	public function test___set( $value, $type, $property, $new_value ) {
		$token = new Token( $value, $type );

		$this->expect_exception( \BadMethodCallException::class );

		$token->$property = $new_value;
	}

	/**
	 * Test __unset.
	 *
	 * @uses ::__construct
	 *
	 * @dataProvider provide___set_data
	 *
	 * @param  string $value     Token value.
	 * @param  int    $type      Token type.
	 * @param  string $property  Property name.
	 * @param  mixed  $ignored   Ignored.
	 */
	public function test___unset( $value, $type, $property, $ignored ) {
		$token = new Token( $value, $type );

		$this->expect_exception( \BadMethodCallException::class );

		unset( $token->$property );
	}

	/**
	 * Provide value pairs for testing the with_value method.
	 *
	 * @return array
	 */
	public function provide_with_value_data() {
		return [
			[ 'word', Token::WORD,        'foobar', false ],
			[ 'word', Token::WORD,        'word',   true ],
			[ '!',    Token::PUNCTUATION, '?',      false ],
		];
	}
	/**
	 * Test with_value.
	 *
	 * @uses ::__construct
	 *
	 * @dataProvider provide_with_value_data
	 *
	 * @param  string $value     Token value.
	 * @param  int    $type      Token type.
	 * @param  string $new_value New token value.
	 * @param  bool   $identical Whether the new token is the old token.
	 */
	public function test_with_value( $value, $type, $new_value, $identical ) {
		$token     = new Token( $value, $type );
		$new_token = $token->with_value( $new_value );

		if ( $identical ) {
			$this->assertSame( $token, $new_token );
		} else {
			$this->assertNotSame( $token, $new_token );
		}

		$this->assertSame( $new_value, $new_token->value );
	}
}
