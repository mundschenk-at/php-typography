<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2020 Peter Putzer.
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
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Smart_Fractions_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Fractions_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Fractions_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Smart_Fractions_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
	 * @uses PHP_Typography\RE::escape_tags
	 */
	public function test_array_constructor() {
		$this->fix = new Node_Fixes\Smart_Fractions_Fix( 'foo', 'bar' );
		$this->assert_attribute_same( RE::escape_tags( '<sup class="foo">$1</sup>' . U::FRACTION_SLASH . '<sub class="bar">$2</sub>$3' ), 'replacement',   $this->fix, 'The replacement should contain the classes "foo" and "bar".' );
	}

	/**
	 * Provide data for testing smart_fractions.
	 *
	 * @return array
	 */
	public function provide_smart_fractions_data() {
		return [
			[
				'1/2 3/300 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub> <sup>3</sup>&frasl;<sub>300</sub> <sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub> 4/2015 1999/2000 <sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 3/300 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub> <sup class="num">3</sup>&frasl;<sub class="denom">300</sub> <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub> 4/2015 1999/2000 <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
			[
				'1/4.',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>.',
				'num',
				'denom',
			],
			[
				'1/4,',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>,',
				'num',
				'denom',
			],
			[
				'1/4;',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>;',
				'num',
				'denom',
			],
			[
				'1/4:',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>:',
				'num',
				'denom',
			],
			[
				'1/4?',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>?',
				'num',
				'denom',
			],
			[
				'1/4!',
				'<sup class="num">1</sup>&frasl;<sub class="denom">4</sub>!',
				'num',
				'denom',
			],
			[
				'1/2018',
				'1/2018',
				'',
				'',
			],
			[
				'99/2018',
				'<sup>99</sup>&frasl;<sub>2018</sub>',
				'',
				'',
			],
		];
	}

	/**
	 * Provide data for testing smart_fractions.
	 *
	 * @return array
	 */
	public function provide_smart_fractions_with_spacing_data() {
		return [
			[
				'1/2 3/300 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub>&#8239;<sup>3</sup>&frasl;<sub>300</sub> <sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub>&#8239;4/2015 1999/2000&#8239;<sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 3/300 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&#8239;<sup class="num">3</sup>&frasl;<sub class="denom">300</sub> <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&#8239;4/2015 1999/2000&#8239;<sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
			[
				'1/1, 10/10, 9/9, 50/50',
				'<sup class="num">1</sup>&frasl;<sub class="denom">1</sub>, 10/10, 9/9, 50/50',
				'num',
				'denom',
			],
		];
	}

	/**
	 * Provide data for testing smart_fractions.
	 *
	 * @return array
	 */
	public function provide_smart_fractions_only_spacing_data() {
		return [
			[
				'1/2 3/300 999/1000',
				'1/2&nbsp;3/300 999/1000',
				'',
				'',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'1/2&nbsp;4/2015 1999/2000&nbsp;999/1000',
				'',
				'',
			],
			[
				'1/2 3/300 999/1000',
				'1/2&nbsp;3/300 999/1000',
				'num',
				'denom',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'1/2&nbsp;4/2015 1999/2000&nbsp;999/1000',
				'num',
				'denom',
			],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_fractions_data
	 *
	 * @param string $input       HTML input.
	 * @param string $result      Expected result.
	 * @param string $numerator   Numerator CSS class.
	 * @param string $denominator Denominator CSS class.
	 */
	public function test_apply( $input, $result, $numerator, $denominator ) {
		$this->fix = new Node_Fixes\Smart_Fractions_Fix( $numerator, $denominator );

		$this->s->set_smart_fractions( true );
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_fraction_spacing( false );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_fractions_with_spacing_data
	 *
	 * @param string $input       HTML input.
	 * @param string $result      Expected result.
	 * @param string $numerator   Numerator CSS class.
	 * @param string $denominator Denominator CSS class.
	 */
	public function test_apply_with_spacing( $input, $result, $numerator, $denominator ) {
		$this->fix = new Node_Fixes\Smart_Fractions_Fix( $numerator, $denominator );

		$this->s->set_smart_fractions( true );
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_fraction_spacing( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_fractions_only_spacing_data
	 *
	 * @param string $input       HTML input.
	 * @param string $result      Expected result.
	 * @param string $numerator   Numerator CSS class.
	 * @param string $denominator Denominator CSS class.
	 */
	public function test_apply_only_spacing( $input, $result, $numerator, $denominator ) {
		$this->fix = new Node_Fixes\Smart_Fractions_Fix( $numerator, $denominator );

		$this->s->set_smart_fractions( false );
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_fraction_spacing( true );

		$this->assertFixResultSame( $input, $result );
	}


	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_fractions_data
	 *
	 * @param string $input       HTML input.
	 * @param string $result      Expected result.
	 * @param string $numerator   Numerator CSS class.
	 * @param string $denominator Denominator CSS class.
	 */
	public function test_apply_off( $input, $result, $numerator, $denominator ) {
		$this->fix = new Node_Fixes\Smart_Fractions_Fix( $numerator, $denominator );

		$this->s->set_smart_fractions( false );
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_fraction_spacing( false );

		$this->assertFixResultSame( $input, $input );
	}
}
