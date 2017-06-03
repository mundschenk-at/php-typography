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

namespace PHP_Typography\Tests;

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
	 * Test get_object_hash function.
	 *
	 * @covers \PHP_Typography\get_object_hash
	 */
	public function test_get_object_hash() {
		$hash1 = \PHP_Typography\get_object_hash( 666 );
		$this->assertInternalType( 'string', $hash1 );
		$this->assertGreaterThan( 0, strlen( $hash1 ) );

		$hash2 = \PHP_Typography\get_object_hash( new \stdClass() );
		$this->assertInternalType( 'string', $hash2 );
		$this->assertGreaterThan( 0, strlen( $hash2 ) );

		$this->assertNotEquals( $hash1, $hash2 );
	}
}
