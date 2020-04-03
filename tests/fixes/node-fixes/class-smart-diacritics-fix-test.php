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
 * Smart_Diacritics_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Diacritics_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Diacritics_Fix
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
class Smart_Diacritics_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Smart_Diacritics_Fix();
	}

	/**
	 * Provide data for testing smart_diacritics.
	 *
	 * @return array
	 */
	public function provide_smart_diacritics_data() {
		return [
			[ '<p>creme brulee</p>', '<p>crème brûlée</p>', 'en-US' ],
			[ 'no diacritics to replace, except creme', 'no diacritics to replace, except crème', 'en-US' ],
			[ 'ne vs. seine vs einzelne', 'né vs. seine vs einzelne', 'en-US' ],
			[ 'ne vs. sei&shy;ne vs einzelne', 'né vs. sei&shy;ne vs einzelne', 'en-US' ],
			[ 'Weiterhin müssen außenpolitische Experten raus aus ihrer Berliner Blase. In der genannten Umfrage', 'Weiterhin müssen außenpolitische Experten raus aus ihrer Berliner Blase. In der genannten Umfrage', 'de-DE' ],
		];
	}

	/**
	 * Provide data for testing smart_diacritics.
	 *
	 * @return array
	 */
	public function provide_smart_diacritics_error_in_pattern_data() {
		return [
			[ 'no diacritics to replace, except creme', 'en-US', 'creme' ],
		];
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_diacritics_error_in_pattern_data
	 *
	 * @param string $html  HTML input.
	 * @param string $lang  Language code.
	 * @param string $unset Replacement to unset.
	 */
	public function test_smart_diacritics_error_in_pattern( $html, $lang, $unset ) {

		$this->s->set_smart_diacritics( true );
		$this->s->set_diacritic_language( $lang );

		$replacements = $this->s[ Settings::DIACRITIC_REPLACEMENT_DATA ];
		unset( $replacements['replacements'][ $unset ] );
		$this->s[ Settings::DIACRITIC_REPLACEMENT_DATA ] = $replacements;

		$this->assertFixResultSame( $html, $html );
	}


	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang   Language code.
	 */
	public function test_apply( $input, $result, $lang ) {
		$this->s->set_smart_diacritics( true );
		$this->s->set_diacritic_language( $lang );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang   Language code.
	 */
	public function test_apply_off( $input, $result, $lang ) {
		$this->s->set_smart_diacritics( false );
		$this->s->set_diacritic_language( $lang );

		$this->assertFixResultSame( $input, $input );
	}
}
