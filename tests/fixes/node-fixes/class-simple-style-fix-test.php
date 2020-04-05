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
use PHP_Typography\Settings;

/**
 * Simple_Style_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Simple_Style_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Simple_Style_Fix
 *
 * @uses ::__construct
 * @uses ::apply_internal
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Style_Fix::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Simple_Style_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->fix = $this->getMockForAbstractClass( Node_Fixes\Simple_Style_Fix::class, [ '/(.*)/', 'fooBar', 'some-class' ] );

		$this->assert_attribute_same( '/(.*)/',     'regex',           $this->fix );
		$this->assert_attribute_same( 'fooBar',     'settings_switch', $this->fix );
		$this->assert_attribute_same( 'some-class', 'css_class',       $this->fix );
	}

	/**
	 * Provide data for testing apply_internal.
	 *
	 * @return array
	 */
	public function provide_apply_internal_data() {
		return [
			[ 'foo & bar', '<span class="some-class">foo & bar</span>' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_apply_internal_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->fix = $this->getMockForAbstractClass( Node_Fixes\Simple_Style_Fix::class, [ '/(.+)/u', 'styleAmpersands', 'some-class' ] );
		$this->s->set_style_ampersands( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_apply_internal_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->fix = $this->getMockForAbstractClass( Node_Fixes\Simple_Style_Fix::class, [ '/(.+)/u', 'styleAmpersands', 'some-class' ] );
		$this->s->set_style_ampersands( false );

		$this->assertFixResultSame( $input, $input );
	}
}
