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
use PHP_Typography\Settings\Simple_Quotes;
use PHP_Typography\U;

/**
 * Simple_Quotes unit test.
 *
 * @coversDefaultClass \PHP_Typography\Settings\Simple_Quotes
 * @usesDefaultClass \PHP_Typography\Settings\Simple_Quotes
 *
 * @uses PHP_Typography\Settings\Simple_Quotes
 */
class Simple_Quotes_Test extends Testcase {

	/**
	 * Provide data for testing the Simple_Quotes class.
	 *
	 * @return array
	 */
	public function provide_simple_quotes_data() {
		return [
			[ 'a', 'b' ],
			[ 'foo', 'bar' ],
		];
	}

	/**
	 * Test the class.
	 *
	 * @covers ::__construct
	 * @covers ::open
	 * @covers ::close
	 *
	 * @dataProvider provide_simple_quotes_data
	 *
	 * @param string $open  Required.
	 * @param string $close Required.
	 */
	public function test_simple_quotes( $open, $close ) {
		$quotes = new Simple_Quotes( $open, $close );

		$this->assertSame( $open, $quotes->open() );
		$this->assertSame( $close, $quotes->close() );
	}
}
