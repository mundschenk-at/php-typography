<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2019 Peter Putzer.
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

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use PHP_Typography\Fixes\Node_Fixes;
use PHP_Typography\Settings;

/**
 * Numbered_Abbreviation_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Numbered_Abbreviation_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Numbered_Abbreviation_Spacing_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Numbered_Abbreviation_Spacing_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Numbered_Abbreviation_Spacing_Fix();
	}

	/**
	 * Provide data for testing unit_spacing.
	 *
	 * @return array
	 */
	public function provide_numbered_abbreviation_spacing_data() {
		return [
			[ 'ÖNORM A 1080:2007', '&Ouml;NORM A&nbsp;1080:2007' ],
			[ 'Das steht in der ÖNORM EN ISO 13920!', 'Das steht in der &Ouml;NORM EN ISO&nbsp;13920!' ],
			[ 'ONR 191160:2010', 'ONR&nbsp;191160:2010' ],
			[ 'DIN ISO 2936', 'DIN ISO&nbsp;2936' ],
			[ 'DIN ISO/IEC 10561', 'DIN ISO/IEC&nbsp;10561' ],
			[ 'VG 96936', 'VG&nbsp;96936' ],
			[ 'LN 9118-2', 'LN&nbsp;9118-2' ],
			[ 'DIN 5032 Lichtmessung', 'DIN&nbsp;5032 Lichtmessung' ],
			[ 'DIN EN 118 Holzschutzmittel', 'DIN EN&nbsp;118 Holzschutzmittel' ],
			[ 'DIN EN ISO 9001 Qualitätsmanagementsysteme', 'DIN EN ISO&nbsp;9001 Qualit&auml;tsmanagementsysteme' ],
			[ 'Enthält E 100.', 'Enth&auml;lt E&nbsp;100.' ],
			[ 'E 160a', 'E&nbsp;160a' ],
			[ 'ISO/IEC 13818', 'ISO/IEC&nbsp;13818' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix::apply
	 *
	 * @dataProvider provide_numbered_abbreviation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_numbered_abbreviation_spacing( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix::apply
	 *
	 * @dataProvider provide_numbered_abbreviation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_numbered_abbreviation_spacing( false );

		$this->assertFixResultSame( $input, $input );
	}
}
