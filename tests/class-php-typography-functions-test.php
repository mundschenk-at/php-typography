<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2016-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

// Can't autoload functions.
require_once dirname( __DIR__ ) . '/php-typography/php-typography-functions.php';

/**
 * Test cases for php-typography/php-typography-functions.php
 */
class PHP_Typography_Functions_Test extends \PHPUnit\Framework\TestCase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * Provide data for testing arrays_intersect.
	 *
	 * @return array
	 */
	public function provide_arrays_intersect_data() {
		return [
			[ [], [], false ],
			[ [ 1, 2, 3 ], [ 2, 4, 1 ], true ],
			[ [ 1, 2, 3 ], [], false ],
			[ [], [ 1, 2, 3 ], false ],
		];
	}

	/**
	 * $a1 and $a2 need to be arrays of object indexes < 10
	 *
	 * @covers \PHP_Typography\arrays_intersect
	 * @dataProvider provide_arrays_intersect_data
	 *
	 * @param  array $a1     First array.
	 * @param  array $a2     Second array.
	 * @param  bool  $result Expected result.
	 */
	public function test_arrays_intersect( array $a1, array $a2, $result ) {
		$nodes = [];
		for ( $i = 0; $i < 10; ++$i ) {
			$nodes[] = new \DOMText( "foo $i" );
		}

		$array1 = [];
		foreach ( $a1 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array1[] = $nodes[ $index ];
			}
		}

		$array2 = [];
		foreach ( $a2 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array2[ spl_object_hash( $nodes[ $index ] ) ] = $nodes[ $index ];
			}
		}

		$this->assertSame( $result, \PHP_Typography\arrays_intersect( $array1, $array2 ) );
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
	 * @covers \PHP_Typography\is_odd
	 * @dataProvider provide_is_odd_data
	 *
	 * @param  int  $number A number.
	 * @param  bool $result Expected result.
	 */
	public function test_is_odd( $number, $result ) {
		if ( $result ) {
			$this->assertTrue( \PHP_Typography\is_odd( $number ) );
		} else {
			$this->assertFalse( \PHP_Typography\is_odd( $number ) );
		}
	}

	/**
	 * Test get_object_hash function.
	 *
	 * @covers \PHP_Typography\get_object_hash
	 */
	public function test_get_object_hash() {
		$hash1 = \PHP_Typography\get_object_hash( 666 );
		$this->assertInternalType( 'string', $hash1 );
		$this->assertGreaterThan( 0, strlen( $hash1 ) );

		$hash2 = \PHP_Typography\get_object_hash( new stdClass() );
		$this->assertInternalType( 'string', $hash2 );
		$this->assertGreaterThan( 0, strlen( $hash2 ) );

		$this->assertNotEquals( $hash1, $hash2 );
	}
}
