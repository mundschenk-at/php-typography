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

use PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix;
use PHP_Typography\Settings;
use PHP_Typography\Settings\Quote_Style;
use PHP_Typography\Strings;

/**
 * Smart_Quotes_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
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
	protected function set_up() {
		parent::set_up();

		$this->fix = new Smart_Quotes_Fix();
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
			[ 'Some "word")',                      'Some &ldquo;word&rdquo;)' ],
			[ '"So \'this\'", she said',           '&ldquo;So &lsquo;this&rsquo;&#8239;&rdquo;, she said' ],
			[ '"\'This\' is it?"',                 '&ldquo;&#8239;&lsquo;This&rsquo; is it?&rdquo;' ],
			[ 'from the early \'60s, American',    'from the early &#700;60s, American' ],
		];
	}

	/**
	 * Provide data for testing smart quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_special_data() {
		return [
			[
				'("Some" word',
				'(&raquo;Some&laquo; word',
				Quote_Style::DOUBLE_GUILLEMETS_REVERSED,
				Quote_Style::SINGLE_GUILLEMETS_REVERSED,
			],
			[
				'(sans franchir la case "carte de crédit")',
				'(sans franchir la case &laquo;&#8239;carte de cr&eacute;dit&#8239;&raquo;)',
				Quote_Style::DOUBLE_GUILLEMETS_FRENCH,
				Quote_Style::SINGLE_GUILLEMETS_REVERSED,
			],
			[
				' et <code>aria-labelledby</code>',
				' et <code>aria-labelledby</code>',
				Quote_Style::DOUBLE_GUILLEMETS_FRENCH,
				Quote_Style::SINGLE_CURLED,
				'"',
				'',
			],
			[
				'foo',
				'foo',
				Quote_Style::DOUBLE_GUILLEMETS_FRENCH,
				Quote_Style::SINGLE_CURLED,
				'"',
				'"',
			],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::calc_adjacent_length
	 *
	 * @uses ::update_smart_quotes_brackets
	 *
	 * @dataProvider provide_smart_quotes_special_data
	 *
	 * @param string $html      HTML input.
	 * @param string $result    Expected entity-escaped result.
	 * @param string $primary   Primary quote style.
	 * @param string $secondary Secondard  quote style.
	 * @param string $previous  Optional. Default ''.
	 * @param string $next      Optional. Default ''.
	 */
	public function test_smart_quotes_special( $html, $result, $primary, $secondary, $previous = '', $next = '' ) {
		$this->s->set_tags_to_ignore( [ 'code' ] );
		$this->s->set_smart_quotes( true );
		$this->s->set_smart_quotes_primary( $primary );
		$this->s->set_smart_quotes_secondary( $secondary );

		$this->assertFixResultSame( $html, $result, $previous, $next );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::update_smart_quotes_brackets
	 * @covers ::calc_adjacent_length
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
	 * @covers ::calc_adjacent_length
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
