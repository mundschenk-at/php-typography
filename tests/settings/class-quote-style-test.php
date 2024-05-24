<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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
use PHP_Typography\Settings\Quote_Style;
use PHP_Typography\U;

use Mockery as m;

/**
 * Quote_Style unit test.
 *
 * @coversDefaultClass \PHP_Typography\Settings\Quote_Style
 * @usesDefaultClass \PHP_Typography\Settings\Quote_Style
 *
 * @uses PHP_Typography\Settings\Simple_Quotes
 */
class Quote_Style_Test extends Testcase {


	/**
	 * Provide test data for testing get_styled_quotes.
	 */
	public function provide_get_styled_quotes_data() {
		$quotes_mock = m::mock( Quotes::class );

		return [
			[ Quote_Style::DOUBLE_CURLED, [ U::DOUBLE_QUOTE_OPEN, U::DOUBLE_QUOTE_CLOSE ] ],
			[ Quote_Style::DOUBLE_CURLED_REVERSED, [ U::DOUBLE_QUOTE_CLOSE, U::DOUBLE_QUOTE_CLOSE ] ],
			[ Quote_Style::DOUBLE_LOW_9, [ U::DOUBLE_LOW_9_QUOTE, U::DOUBLE_QUOTE_CLOSE ] ],
			[ Quote_Style::DOUBLE_LOW_9_REVERSED, [ U::DOUBLE_LOW_9_QUOTE, U::DOUBLE_QUOTE_OPEN ] ],
			[ Quote_Style::SINGLE_CURLED, [ U::SINGLE_QUOTE_OPEN, U::SINGLE_QUOTE_CLOSE ] ],
			[ Quote_Style::SINGLE_CURLED_REVERSED, [ U::SINGLE_QUOTE_CLOSE, U::SINGLE_QUOTE_CLOSE ] ],
			[ Quote_Style::SINGLE_LOW_9, [ U::SINGLE_LOW_9_QUOTE, U::SINGLE_QUOTE_CLOSE ] ],
			[ Quote_Style::SINGLE_LOW_9_REVERSED, [ U::SINGLE_LOW_9_QUOTE, U::SINGLE_QUOTE_OPEN ] ],
			[ Quote_Style::DOUBLE_GUILLEMETS, [ U::GUILLEMET_OPEN, U::GUILLEMET_CLOSE ] ],
			[ Quote_Style::DOUBLE_GUILLEMETS_REVERSED, [ U::GUILLEMET_CLOSE, U::GUILLEMET_OPEN ] ],
			[ Quote_Style::SINGLE_GUILLEMETS, [ U::SINGLE_ANGLE_QUOTE_OPEN, U::SINGLE_ANGLE_QUOTE_CLOSE ] ],
			[ Quote_Style::SINGLE_GUILLEMETS_REVERSED, [ U::SINGLE_ANGLE_QUOTE_CLOSE, U::SINGLE_ANGLE_QUOTE_OPEN ] ],
			[ Quote_Style::CORNER_BRACKETS, [ U::LEFT_CORNER_BRACKET, U::RIGHT_CORNER_BRACKET ] ],
			[ Quote_Style::WHITE_CORNER_BRACKETS, [ U::LEFT_WHITE_CORNER_BRACKET, U::RIGHT_WHITE_CORNER_BRACKET ] ],
			[ $quotes_mock, $quotes_mock ],
			[ 'foo', null ],
			[ 123, null ],
		];
	}

	/**
	 * Test get_styled_quotes.
	 *
	 * @covers ::get_styled_quotes
	 *
	 * @dataProvider provide_get_styled_quotes_data
	 *
	 * @param  mixed             $style  Style index.
	 * @param  array|Quotes|null $result Result array (or null).
	 */
	public function test_get_styled_quotes( $style, $result = null ) {
		if ( null === $result ) {
			$this->expect_exception_message_matches( "/^Invalid quote style $style.\$/" );
		}

		$quotes = Quote_Style::get_styled_quotes( $style );

		if ( is_array( $result ) ) {
			$this->assertInstanceOf( Quotes::class, $quotes );
			$this->assertSame( $result[0], $quotes->open() );
			$this->assertSame( $result[1], $quotes->close() );
		} elseif ( $style instanceof Quotes ) {
			$this->assertSame( $style, $quotes );
		} else {
			$this->assertNull( $quotes, 'get_styled_quotes should return null for invalid indices.' );
		}
	}

	/**
	 * Test get_styled_quotes.
	 *
	 * @covers ::get_styled_quotes
	 */
	public function test_get_styled_quotes_french_guillemets() {
		$quotes = Quote_Style::get_styled_quotes( Quote_Style::DOUBLE_GUILLEMETS_FRENCH );

		$this->assertInstanceOf( Quotes::class, $quotes );
		$this->assertSame( U::GUILLEMET_OPEN, \mb_substr( $quotes->open(), 0, 1 ) );
		$this->assertSame( U::GUILLEMET_CLOSE, \mb_substr( $quotes->close(), -1 ) );
	}
}
