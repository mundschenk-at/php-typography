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
 * Smart_Dashes_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Dashes_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Dashes_Fix
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
class Smart_Dashes_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Smart_Dashes_Fix();
	}

	/**
	 * Provide data for testing smart dashes.
	 *
	 * @return array
	 */
	public function provide_smart_dashes_data() {
		return [
			[
				'Ein - mehr oder weniger - guter Gedanke, 1908-2008',
				'Ein &mdash; mehr oder weniger &mdash; guter Gedanke, 1908&ndash;2008',
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&ndash;2008',
			],
			[
				"We just don't know --- really---, but you know, --",
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
			],
			[
				'что природа жизни - это Блаженство',
				'что природа жизни &mdash; это Блаженство',
				'что природа жизни &ndash; это Блаженство',
			],
			[
				'Auch 3.-8. März sollte die - richtigen - Gedankenstriche verwenden.',
				'Auch 3.&ndash;8. M&auml;rz sollte die &mdash; richtigen &mdash; Gedankenstriche verwenden.',
				'Auch 3.&ndash;8. M&auml;rz sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
			],
			[
				'20.-30.',
				'20.&ndash;30.',
				'20.&ndash;30.',
			],
			[
				'Zu- und Abnahme',
				'Zu- und Abnahme',
				'Zu- und Abnahme',
			],
			[
				'Glücks-',
				'Glücks-',
				'Glücks-',
			],
			[
				'Foo-',
				'Foo-',
				'Foo-',
			],
			[
				'Warenein- und -ausgang',
				'Warenein- und &#8209;ausgang',
				'Warenein- und &#8209;ausgang',
			],
			[
				'Fugen-s',
				'Fugen&#8209;s',
				'Fugen&#8209;s',
			],
			[
				'ein-, zweimal',
				'ein&#8209;, zweimal',
				'ein&#8209;, zweimal',
			],
			[
				'Just call 800-4567',
				'Just call 800&#8209;4567',
				'Just call 800&#8209;4567',
			],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_apply( $input, $result_us, $result_int ) {
		$this->s->set_smart_dashes( true );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertFixResultSame( $input, $result_us );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertFixResultSame( $input, $result_int );

	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_apply_off( $input, $result_us, $result_int ) {
		$this->s->set_smart_dashes( false );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertFixResultSame( $input, $input );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertFixResultSame( $input, $input );
	}
}
