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
 * Style_Caps_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Caps_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Caps_Fix
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
class Style_Caps_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Provide data for testing caps styling.
	 *
	 * @return array
	 */
	public function provide_style_caps_data() {
		return [
			[ 'foo BAR bar', 'foo <span class="caps">BAR</span> bar' ],
			[ 'foo BARbaz', 'foo BARbaz' ],
			[ 'foo BAR123 baz', 'foo <span class="caps">BAR123</span> baz' ],
			[ 'foo 123BAR baz', 'foo <span class="caps">123BAR</span> baz' ],
			[ 'during WP-CLI commands', 'during <span class="caps">WP-CLI</span> commands' ],
			[ 'during WP‐CLI commands', 'during <span class="caps">WP‐CLI</span> commands' ], // HYPHEN instead of HYPHEN-MINUS.
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->fix = new Node_Fixes\Style_Caps_Fix( 'caps' );
		$this->s->set_style_caps( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->fix = new Node_Fixes\Style_Caps_Fix( 'caps' );
		$this->s->set_style_caps( false );

		$this->assertFixResultSame( $input, $input );
	}
}
