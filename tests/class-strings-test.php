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

use PHP_Typography\Strings;

/**
 * DOM unit test.
 *
 * @coversDefaultClass \PHP_Typography\Strings
 * @usesDefaultClass \PHP_Typography\Strings
 */
class Strings_Test extends Testcase {

	/**
	 * Reports an error identified by $message if the given function array contains a non-callable.
	 *
	 * @param array  $func    An array of string functions.
	 * @param string $message Optional. Default ''.
	 */
	protected function assert_string_functions( array $func, $message = '' ) {
		// Each function is a callable (except for the 'u' modifier string).
		foreach ( $func as $name => $function ) {
			if ( 'u' !== $name ) {
				$this->assertTrue( is_callable( $function ) );
			}
		}
	}

	/**
	 * Test ::functions.
	 *
	 * @covers ::functions
	 */
	public function test_functions() {
		$func_ascii = Strings::functions( 'ASCII' );
		$func_utf8  = Strings::functions( 'UTF-8 üäß' );

		// We are dealing with ararys.
		$this->assertTrue( is_array( $func_ascii ) );
		$this->assertTrue( is_array( $func_utf8 ) );

		// The arrays are not (almost) empty.
		$this->assertGreaterThan( 1, count( $func_ascii ), 'ASCII array contains fewer than 2 functions.' );
		$this->assertGreaterThan( 1, count( $func_utf8 ),  'UTF-8 array contains fewer than 2 functions.' );

		// The keys are identical.
		$this->assertSame( array_keys( $func_ascii ), array_keys( $func_utf8 ) );

		// Each function is a callable (except for the 'u' modifier string).
		$this->assert_string_functions( $func_ascii );
		$this->assert_string_functions( $func_utf8 );
	}

	/**
	 * Test ::functions.
	 *
	 * @covers ::functions
	 */
	public function test_functions_invalid_encoding() {
		$func = Strings::functions( \mb_convert_encoding( 'Ungültiges Encoding', 'ISO-8859-2' ) );

		$this->assertTrue( \is_array( $func ) );
		$this->assertCount( 0, $func );
	}

	/**
	 * Provide data for testing maybe_split_parameters.
	 *
	 * @return array
	 */
	public function provide_maybe_split_parameters_data() {
		return [
			[ [], [] ],
			[ '', [] ],
			[ ',', [] ],
			[ 'a,b', [ 'a', 'b' ] ],
			[ 'foo, bar, xxx', [ 'foo', 'bar', 'xxx' ] ],
			[ [ 'foo', 'bar', 'xxx' ], [ 'foo', 'bar', 'xxx' ] ],
			[ [ 1, 2, 'drei' ], [ 1, 2, 'drei' ] ],
		];
	}

	/**
	 * Test maybe_split_parameters.
	 *
	 * @covers ::maybe_split_parameters
	 * @dataProvider provide_maybe_split_parameters_data
	 *
	 * @param string|array $input  Parameters sring/array.
	 * @param array        $result Expected output.
	 */
	public function test_maybe_split_parameters( $input, $result ) {
		$this->assertSame( $result, Strings::maybe_split_parameters( $input ) );
	}
}
