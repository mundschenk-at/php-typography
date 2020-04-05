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

use PHP_Typography\Settings\Dashes;
use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\U;

/**
 * Dash_Style unit test.
 *
 * @coversDefaultClass \PHP_Typography\Settings\Dash_Style
 * @usesDefaultClass \PHP_Typography\Settings\Dash_Style
 *
 * @uses PHP_Typography\Settings\Simple_Dashes
 */
class Dash_Style_Test extends Testcase {


	/**
	 * Provide test data for testing get_styled_dashes.
	 */
	public function provide_get_styled_dashes_data() {
		return [
			[ Dash_Style::TRADITIONAL_US, [ U::EM_DASH, U::THIN_SPACE, U::EN_DASH, U::THIN_SPACE ] ],
			[ Dash_Style::INTERNATIONAL, [ U::EN_DASH, ' ', U::EN_DASH, U::HAIR_SPACE ] ],
			[ 'foo', null ],
			[ 123, null ],
		];
	}

	/**
	 * Test get_styled_dashes.
	 *
	 * @covers ::get_styled_dashes
	 *
	 * @dataProvider provide_get_styled_dashes_data
	 *
	 * @param  mixed      $style  Style index.
	 * @param  array|null $result Result array (or null).
	 */
	public function test_get_styled_dashes( $style, array $result = null ) {
		$s = $this->createMock( \PHP_Typography\Settings::class );

		$dashes = Dash_Style::get_styled_dashes( $style, $s );

		if ( is_array( $result ) ) {
			$this->assertInstanceOf( Dashes::class, $dashes );
			$this->assertSame( $result[0], $dashes->parenthetical_dash() );
			$this->assertSame( $result[1], $dashes->parenthetical_space() );
			$this->assertSame( $result[2], $dashes->interval_dash() );
			$this->assertSame( $result[3], $dashes->interval_space() );
		} else {
			$this->assertNull( $dashes, 'get_styled_dashes should return null for invalid indices.' );
		}
	}
}
