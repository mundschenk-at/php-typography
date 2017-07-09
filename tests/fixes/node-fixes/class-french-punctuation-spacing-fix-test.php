<?php
/**
 *  This file is part of wp-Typography.
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
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix;
use \PHP_Typography\Settings;

/**
 * French_Punctuation_Spacing_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Arrays
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Strings
 */
class French_Punctuation_Spacing_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
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
			[ '5 > 3', '5 > 3' ],
			[ 'Les « courants de bord ouest » du Pacifique ? Eh bien : ils sont "fabuleux".', 'Les &laquo;&#8239;courants de bord ouest&#8239;&raquo; du Pacifique&#8239;? Eh bien&nbsp;: ils sont "fabuleux".' ],
			[ '« Hello, this is a sentence. »', '&laquo;&#8239;Hello, this is a sentence.&#8239;&raquo;' ],
			[ 'À «programmer»?', '&Agrave; &laquo;&#8239;programmer&#8239;&raquo;&#8239;?' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_french_punctuation_spacing( true );
		$this->s->set_true_no_break_narrow_space( true );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
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
