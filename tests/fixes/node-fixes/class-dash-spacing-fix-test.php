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

use PHP_Typography\Fixes\Node_Fixes\Dash_Spacing_Fix;
use PHP_Typography\Settings;

/**
 * Dash_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Dash_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Dash_Spacing_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Dash_Spacing_Fix_Test extends Node_Fix_Testcase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Dash_Spacing_Fix();
	}

	/**
	 * Provide data for testing dash spacing.
	 *
	 * @return array
	 */
	public function provide_dash_spacing_data() {
		return [
			[
				'Ein &mdash; mehr oder weniger &mdash; guter Gedanke, 1908&ndash;2008',
				'Ein&thinsp;&mdash;&thinsp;mehr oder weniger&thinsp;&mdash;&thinsp;guter Gedanke, 1908&thinsp;&ndash;&thinsp;2008',
				'traditionalUS',
			],
			[
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&ndash;2008',
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&#8202;&ndash;&#8202;2008',
				'international',
			],
			[
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&ndash;2008',
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&ndash;2008',
				'internationalNoHairSpaces',
			],
			[
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
				"We just don't know&thinsp;&mdash;&thinsp;really&thinsp;&mdash;&thinsp;, but you know, &ndash;",
				'traditionalUS',
			],
			[
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
				"We just don't know&#8202;&mdash;&#8202;really&#8202;&mdash;&#8202;, but you know, &ndash;",
				'international',
			],
			[
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
				"We just don't know&mdash;really&mdash;, but you know, &ndash;",
				'internationalNoHairSpaces',
			],
			[
				'Auch 3.&ndash;8. März sollte die &mdash; richtigen &mdash; Gedankenstriche verwenden.',
				'Auch 3.&thinsp;&ndash;&thinsp;8. M&auml;rz sollte die&thinsp;&mdash;&thinsp;richtigen&thinsp;&mdash;&thinsp;Gedankenstriche verwenden.',
				'traditionalUS',
			],
			[
				'Auch 3.&ndash;8. März sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
				'Auch 3.&#8202;&ndash;&#8202;8. M&auml;rz sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
				'international',
			],
			[
				'Auch 3.&ndash;8. März sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
				'Auch 3.&ndash;8. M&auml;rz sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
				'internationalNoHairSpaces',
			],
		];
	}

	/**
	 * Provide data for testing smart dashes (where hyphen should not be changed).
	 *
	 * @return array
	 */
	public function provide_dash_spacing_unchanged_data() {
		return [
			[ 'Vor- und Nachteile, i-Tüpfelchen, 100-jährig, Fritz-Walter-Stadion, 2015-12-03, 01-01-1999, 2012-04' ],
			[ 'Bananen-Milch und -Brot' ],
			[ 'pick-me-up' ],
			[ 'You may see a yield that is two-, three-, or fourfold.' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::update_dash_spacing_regex
	 *
	 * @dataProvider provide_dash_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Entity-escaped result.
	 * @param string $style  Dash style.
	 */
	public function test_apply( $input, $result, $style ) {
		$this->s->set_dash_spacing( true );
		$this->s->set_smart_dashes_style( $style );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::update_dash_spacing_regex
	 *
	 * @dataProvider provide_dash_spacing_unchanged_data
	 *
	 * @param string $input HTML input.
	 */
	public function test_apply_unchanged( $input ) {
		$this->s->set_dash_spacing( true );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertFixResultSame( $input, $input );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertFixResultSame( $input, $input );

		$this->s->set_smart_dashes_style( 'internationalNoHairSpaces' );
		$this->assertFixResultSame( $input, $input );
	}

	/**
	 * Test dewidow.
	 *
	 * @covers ::apply
	 *
	 * @uses ::update_dash_spacing_regex
	 *
	 * @dataProvider provide_dash_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Entity-escaped result.
	 * @param string $style  Dash style.
	 */
	public function test_apply_off( $input, $result, $style ) {
		$this->s->set_dash_spacing( false );
		$this->s->set_smart_dashes_style( $style );

		$this->assertFixResultSame( $input, $input );
	}
}
