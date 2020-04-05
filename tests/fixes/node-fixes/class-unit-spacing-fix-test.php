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
 * Unit_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Unit_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Unit_Spacing_Fix
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
class Unit_Spacing_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Unit_Spacing_Fix();
	}

	/**
	 * Provide data for testing unit_spacing.
	 *
	 * @return array
	 */
	public function provide_unit_spacing_data() {
		return [
			[ 'It was 2 m from', 'It was 2&#8239;m from' ],
			[ '3 km/h', '3&#8239;km/h' ],
			[ '5 sg 44 kg', '5 sg 44&#8239;kg' ],
			[ '100 &deg;C', '100&#8239;&deg;C' ],
			[ '10 &euro;', '10&#8239;&euro;' ],
			[ '10 €', '10&#8239;&euro;' ],
			[ '1 ¢', '1&#8239;&cent;' ],
			[ '1 $', '1&#8239;$' ],
			[ '5 nanoamperes', '5&#8239;nanoamperes' ],
			[ '1 Ω', '1&#8239;&Omega;' ],
			[ '1 &Omega;', '1&#8239;&Omega;' ],
			[ '10 m2', '10&#8239;m2' ],
			[ '10 m²', '10&#8239;m²' ],
			[ '5 m³', '5&#8239;m³' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix::apply
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_unit_spacing( true );
		$this->s->set_true_no_break_narrow_space( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix::apply
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_unit_spacing( false );
		$this->s->set_true_no_break_narrow_space( true );

		$this->assertFixResultSame( $input, $input );
	}
}
