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

use PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix;
use PHP_Typography\Settings;

/**
 * French_Punctuation_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix
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
class French_Punctuation_Spacing_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->fix = new French_Punctuation_Spacing_Fix();
	}

	/**
	 * Provide data for testing French punctuation rules.
	 *
	 * @return array
	 */
	public function provide_french_punctuation_spacing_data() {
		return [
			[ "Je t'aime ; m'aimes-tu ?", "Je t'aime&#8239;; m'aimes-tu&#8239;?" ],
			[ "Je t'aime; m'aimes-tu?", "Je t'aime&#8239;; m'aimes-tu&#8239;?" ],
			[ 'Au secours !', 'Au secours&#8239;!' ],
			[ 'Au secours!', 'Au secours&#8239;!' ],
			[ 'Jean a dit : Foo', 'Jean a dit&nbsp;: Foo' ],
			[ 'Jean a dit: Foo', 'Jean a dit&nbsp;: Foo' ],
			[ 'http://example.org', 'http://example.org' ],
			[ 'foo &Ouml; & ; bar', 'foo &Ouml; &amp; ; bar' ],
			[ 'foo; <bar>', 'foo&#8239;; <bar>' ],
			[ '5 > 3', '5 > 3' ],
			[ 'Les « courants de bord ouest » du Pacifique ? Eh bien : ils sont "fabuleux".', 'Les &laquo;&#8239;courants de bord ouest&#8239;&raquo; du Pacifique&#8239;? Eh bien&nbsp;: ils sont "fabuleux".' ],
			[ '« Hello, this is a sentence. »', '&laquo;&#8239;Hello, this is a sentence.&#8239;&raquo;' ],
			[ 'À «programmer»?', '&Agrave; &laquo;&#8239;programmer&#8239;&raquo;&#8239;?' ],
			[ '«Pourquoi», c’est une bonne question', '«&#8239;Pourquoi&#8239;», c’est une bonne question' ],
			[ '(sans franchir la case «carte de crédit»)', '(sans franchir la case &laquo;&#8239;carte de cr&eacute;dit&#8239;&raquo;)' ],
			[ '(«sans» franchir la case carte de crédit)', '(&laquo;&#8239;sans&#8239;&raquo; franchir la case carte de cr&eacute;dit)' ],
			[ '[«sans» franchir la case «carte de crédit»]', '[&laquo;&#8239;sans&#8239;&raquo; franchir la case &laquo;&#8239;carte de cr&eacute;dit&#8239;&raquo;]' ],
		];
	}

	/**
	 * Provide data for testing French punctuation rules.
	 *
	 * @return array
	 */
	public function provide_french_punctuation_spacing_with_siblings_data() {
		return [
			[ ': foo', '&nbsp;: foo' ],
			[ 'À «', '&Agrave; &laquo;&#8239;' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::remove_adjacent_characters
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_french_punctuation_spacing( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply with left and right textnode siblings.
	 *
	 * @covers ::apply
	 *
	 * @uses ::remove_adjacent_characters
	 *
	 * @dataProvider provide_french_punctuation_spacing_with_siblings_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 */
	public function test_apply_with_siblings( $html, $result ) {
		$this->s->set_french_punctuation_spacing( true );

		$this->assertFixResultSame( $html, $result, 'foo', 'bar' );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::remove_adjacent_characters
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_french_punctuation_spacing( false );

		$this->assertFixResultSame( $input, $input );
	}
}
