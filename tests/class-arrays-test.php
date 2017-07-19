<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace PHP_Typography\Tests;

use PHP_Typography\Arrays;

/**
 * DOM unit test.
 *
 * @coversDefaultClass \PHP_Typography\Arrays
 * @usesDefaultClass \PHP_Typography\Arrays
 */
class Arrays_Test extends \PHPUnit\Framework\TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
	}

	/**
	 * Provide data for testing array_map_assoc.
	 *
	 * @return array
	 */
	public function provide_array_map_assoc_data() {
		return [
			[
				function( $key, $value ) {
						return [ $value, $value * 2 ];
				},
				[ 1, 2, 3 ],
				[
					1 => 2,
					2 => 4,
					3 => 6,
				],
			],
		];
	}

	/**
	 * Test array_map_assoc.
	 *
	 * @covers ::array_map_assoc
	 * @dataProvider provide_array_map_assoc_data
	 *
	 * @param  callable $callable The function to apply to the array.
	 * @param  array    $array    Input array.
	 * @param  array    $result   Expected output array.
	 */
	public function test_array_map_assoc( callable $callable, array $array, array $result ) {
		$this->assertSame( $result, Arrays::array_map_assoc( $callable, $array ) );
	}
}
