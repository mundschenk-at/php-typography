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

/**
 * Smart_Ordinal_Suffix_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Ordinal_Suffix_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Smart_Ordinal_Suffix_Fix
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
class Smart_Ordinal_Suffix_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
	 */
	public function test_array_constructor() {
		$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix( 'foo' );

		$this->assert_attribute_same( RE::escape_tags( '$1<sup class="foo">$2</sup>' ), 'replacement', $this->fix, 'The replacement CSS class should be "foo".' );
	}

	/**
	 * Provide data for testing ordinal suffixes.
	 *
	 * @return array
	 */
	public function provide_smart_ordinal_suffix_data() {
		return [
			[ 'in the 1st instance',          'in the 1<sup>st</sup> instance', '' ],
			[ 'in the 2nd degree',            'in the 2<sup>nd</sup> degree',   '' ],
			[ 'a 3rd party',                  'a 3<sup>rd</sup> party',         '' ],
			[ '12th Night',                   '12<sup>th</sup> Night',          '' ],
			[ 'in the 1st instance, we',      'in the 1<sup class="ordinal">st</sup> instance, we',  'ordinal' ],
			[ 'murder in the 2nd degree',     'murder in the 2<sup class="ordinal">nd</sup> degree', 'ordinal' ],
			[ 'a 3rd party',                  'a 3<sup class="ordinal">rd</sup> party',              'ordinal' ],
			[ 'the 12th Night',               'the 12<sup class="ordinal">th</sup> Night',           'ordinal' ],
			[ 'la 1ère guerre',               'la 1<sup class="ordinal">&egrave;re</sup> guerre',    'ordinal' ],
			[ 'la 1re guerre mondiale',       'la 1<sup class="ordinal">re</sup> guerre mondiale',   'ordinal' ],
		];
	}

	/**
	 * Provide data for testing ordinal suffixes.
	 *
	 * @return array
	 */
	public function provide_smart_ordinal_suffix_roman_numeral_data() {
		return [
			[ 'la IIIIre heure',              'la IIII<sup>re</sup> heure',     '' ],
			[ 'la IVre heure',                'la IV<sup>re</sup> heure',       '' ],
			[ 'François Ier',                 'Fran&ccedil;ois I<sup>er</sup>', '' ],
			[ 'MDCCLXXVIo',                   'MDCCLXXVI<sup>o</sup>',          '' ],
			[ 'Certain HTML entities',        'Certain HTML entities',          '' ], // Negative test.
			[ 'Cer&shy;tain HTML entities',   'Cer&shy;tain HTML entities',     '' ], // Negative test.
			[ 'Cer&#8203;tain HTML entities', 'Cer&#8203;tain HTML entities',   '' ], // Negative test.
			[ 'Le Président',                 'Le Président',                   '' ], // Negative test.
			[ 'Ce livre est très bon.',       'Ce livre est très bon.',         '' ], // Negative test.
			[ 'De geologische structuur',     'De geologische structuur',       '' ], // Negative test.
			[ 'Me? I like ice cream.',        'Me? I like ice cream.',          '' ], // Negative test.
			[ 'le XIXe siècle',               'le XIX<sup class="ordinal">e</sup> si&egrave;cle',    'ordinal' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_ordinal_suffix_data
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class Optional.
	 */
	public function test_apply( $input, $result, $css_class ) {
		$this->s->set_smart_ordinal_suffix( true );

		if ( ! empty( $css_class ) ) {
			$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix( $css_class );
		}

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_ordinal_suffix_roman_numeral_data
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class Optional.
	 */
	public function test_apply_roman_numerals_on( $input, $result, $css_class ) {
		$this->s->set_smart_ordinal_suffix( true );
		$this->s->set_smart_ordinal_suffix_match_roman_numerals( true );

		if ( ! empty( $css_class ) ) {
			$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix( $css_class );
		}

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_ordinal_suffix_roman_numeral_data
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class Optional.
	 */
	public function test_apply_roman_numerals_off( $input, $result, $css_class ) {
		$this->s->set_smart_ordinal_suffix( true );

		if ( ! empty( $css_class ) ) {
			$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix( $css_class );
		}

		$this->assertFixResultSame( $input, $input );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\RE::escape_tags
	 *
	 * @dataProvider provide_smart_ordinal_suffix_data
	 * @dataProvider provide_smart_ordinal_suffix_roman_numeral_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $css_class Optional.
	 */
	public function test_apply_off( $input, $result, $css_class ) {
		$this->s->set_smart_ordinal_suffix( false );
		$this->s->set_smart_ordinal_suffix_match_roman_numerals( true );

		if ( ! empty( $css_class ) ) {
			$this->fix = new Node_Fixes\Smart_Ordinal_Suffix_Fix( $css_class );
		}

		$this->assertFixResultSame( $input, $input );
	}
}
