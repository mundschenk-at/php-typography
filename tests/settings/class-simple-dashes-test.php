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

namespace PHP_Typography\Tests\Settings;

use PHP_Typography\Tests\Testcase;

use PHP_Typography\Settings\Quotes;
use PHP_Typography\Settings\Simple_Dashes;
use PHP_Typography\U;

/**
 * Simple_Dashes unit test.
 *
 * @coversDefaultClass \PHP_Typography\Settings\Simple_Dashes
 * @usesDefaultClass \PHP_Typography\Settings\Simple_Dashes
 *
 * @uses PHP_Typography\Settings\Simple_Dashes
 */
class Simple_Dashes_Test extends Testcase {

	/**
	 * Provide data for testing the Simple_Dashes class.
	 *
	 * @return array
	 */
	public function provide_simple_dashes_data() {
		return [
			[ 'a', 'b', 'c', 'd' ],
			[ 'foo', 'bar', 'bay', 'baz' ],
		];
	}

	/**
	 * Test the class.
	 *
	 * @covers ::__construct
	 * @covers ::parenthetical_dash
	 * @covers ::parenthetical_space
	 * @covers ::interval_dash
	 * @covers ::interval_space
	 *
	 * @dataProvider provide_simple_dashes_data
	 *
	 * @param string $pdash  Required.
	 * @param string $pspace Required.
	 * @param string $idash  Required.
	 * @param string $ispace Required.
	 */
	public function test_simple_sashes( $pdash, $pspace, $idash, $ispace ) {
		$dashes = new Simple_Dashes( $pdash, $pspace, $idash, $ispace );

		$this->assertSame( $pdash, $dashes->parenthetical_dash() );
		$this->assertSame( $pspace, $dashes->parenthetical_space() );
		$this->assertSame( $idash, $dashes->interval_dash() );
		$this->assertSame( $ispace, $dashes->interval_space() );
	}
}
