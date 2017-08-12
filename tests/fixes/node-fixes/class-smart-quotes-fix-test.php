<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix;
use \PHP_Typography\Settings;
use \PHP_Typography\Strings;

/**
 * Smart_Quotes_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
 * @uses PHP_Typography\Arrays
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Smart_Quotes_Fix_Test extends Node_Fix_Testcase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->fix = new Smart_Quotes_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->fix = new Smart_Quotes_Fix();

		$this->assertAttributeInternalType( 'array', 'apostrophe_exception_matches',      $this->fix );
		$this->assertAttributeInternalType( 'array', 'apostrophe_exception_replacements', $this->fix );
	}

	/**
	 * Provide data for testing smart_quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_data() {
		return [
			[ '"This is so 1996", he said.',       '&ldquo;This is so 1996&rdquo;, he said.' ],
			[ '6\'5"',                             '6&prime;5&Prime;' ],
			[ '6\' 5"',                            '6&prime; 5&Prime;' ],
			[ '6\'&nbsp;5"',                       '6&prime;&nbsp;5&Prime;' ],
			[ " 6'' ",                             ' 6&Prime; ' ], // nobody uses this for quotes, so it should be OK to keep the primes here.
			[ 'ein 32"-Fernseher',                 'ein 32&Prime;-Fernseher' ],
			[ "der 8'-Ölbohrer",                   'der 8&prime;-&Ouml;lbohrer' ],
			[ "der 1/4'-Bohrer",                   'der 1/4&prime;-Bohrer' ],
			[ 'Hier 1" "Typ 2" einsetzen',         'Hier 1&Prime; &ldquo;Typ 2&rdquo; einsetzen' ],
			[ "2/4'",                              '2/4&prime;' ],
			[ '3/44"',                             '3/44&Prime;' ],
			[ '("Some" word',                      '(&ldquo;Some&rdquo; word' ],
		];
	}

	/**
	 * Provide data for testing smart quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_special_data() {
		return [
			[ '("Some" word', '(&raquo;Some&laquo; word', 'doubleGuillemetsReversed', 'singleGuillemetsReversed' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::update_smart_quotes_brackets
	 *
	 * @dataProvider provide_smart_quotes_special_data
	 *
	 * @param string $html      HTML input.
	 * @param string $result    Expected entity-escaped result.
	 * @param string $primary   Primary quote style.
	 * @param string $secondary Secondard  quote style.
	 */
	public function test_smart_quotes_special( $html, $result, $primary, $secondary ) {
		$this->s->set_smart_quotes( true );
		$this->s->set_smart_quotes_primary( $primary );
		$this->s->set_smart_quotes_secondary( $secondary );

		$this->assertFixResultSame( $html, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::update_smart_quotes_brackets
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 */
	public function test_apply( $html, $result ) {
		$this->s->set_smart_quotes( true );

		$this->assertFixResultSame( $html, $result );
	}

	/**
	 * Test apply with left and right textnode siblings.
	 *
	 * @covers ::apply
	 *
	 * @uses ::update_smart_quotes_brackets
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 */
	public function test_apply_with_siblings( $html, $result ) {
		$this->s->set_smart_quotes( true );

		$this->assertFixResultSame( $html, $result, 'foo ', ' bar' );
	}

	/**
	 * Test dewidow.
	 *
	 * @covers ::apply
	 *
	 * @uses ::update_smart_quotes_brackets
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 */
	public function test_apply_off( $html, $result ) {
		$this->s->set_smart_quotes( false );

		$this->assertFixResultSame( $html, $html );
	}
}
