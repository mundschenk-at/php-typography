<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2024 Peter Putzer.
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

use PHP_Typography\Settings\Quotes;

use PHP_Typography\Tests\Testcase;

use Mockery as m;

/**
 * Quotes unit test.
 *
 * @coversDefaultClass \PHP_Typography\Settings\Quotes
 * @usesDefaultClass \PHP_Typography\Settings\Quotes
 *
 * @uses PHP_Typography\Settings\Quotes
 */
class Quotes_Test extends Testcase {

	/**
	 * Tests jsonSerialize.
	 *
	 * @covers ::jsonSerialize
	 */
	public function test_jsonSerialize(): void {
		$dashes = m::mock( Quotes::class )->makePartial();

		$dashes->shouldReceive( 'open' )->once()->andReturn( 'o' );
		$dashes->shouldReceive( 'close' )->once()->andReturn( 'c' );

		$this->assertIsString( \json_encode( $dashes ) );
	}
}
