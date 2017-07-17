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

use \PHP_Typography\Fixes\Node_Fixes;
use \PHP_Typography\Settings;

/**
 * Style_Hanging_Punctuation_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Hanging_Punctuation_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Style_Hanging_Punctuation_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::remove_adjacent_characters
 * @uses PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix::__construct
 * @uses PHP_Typography\Arrays
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Style_Hanging_Punctuation_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->fix = new Node_Fixes\Style_Hanging_Punctuation_Fix( 'push-single', 'push-double', 'pull-single', 'pull-double' );
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->fix = new Node_Fixes\Style_Hanging_Punctuation_Fix( 'alpha', 'beta', 'gamma', 'delta' );

		$this->assertAttributeEquals( 'alpha', 'push_single_class', $this->fix );
		$this->assertAttributeEquals( 'beta',  'push_double_class', $this->fix );
		$this->assertAttributeEquals( 'gamma', 'pull_single_class', $this->fix );
		$this->assertAttributeEquals( 'delta', 'pull_double_class', $this->fix );
	}

	/**
	 * Provide data for testing stye_hanging_punctuation.
	 *
	 * @return array
	 */
	public function provide_style_hanging_punctuation_data() {
		return [
			[
				'"First "second "third.',
				'',
				'',
				'<span class="pull-double">"</span>First <span class="push-double"></span>&#8203;<span class="pull-double">"</span>second <span class="push-double"></span>&#8203;<span class="pull-double">"</span>third.',
			],
			[
				'"First "second "third.',
				'',
				' foo',
				'<span class="pull-double">"</span>First <span class="push-double"></span>&#8203;<span class="pull-double">"</span>second <span class="push-double"></span>&#8203;<span class="pull-double">"</span>third.',
			],
			[
				'"First "second "third.',
				'foo ',
				'',
				'<span class="push-double"></span>&#8203;<span class="pull-double">"</span>First <span class="push-double"></span>&#8203;<span class="pull-double">"</span>second <span class="push-double"></span>&#8203;<span class="pull-double">"</span>third.',
			],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $input  HTML input.
	 * @param string $left   Left sibling.
	 * @param string $right  Right sibling.
	 * @param string $result Expected result.
	 */
	public function test_apply_internal( $input, $left, $right, $result ) {
		$this->s->set_style_hanging_punctuation( true );

		$this->assertFixResultSame( $input, $result, $left, $right );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply_internal
	 *
	 * @uses ::apply
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $input  HTML input.
	 * @param string $left   Left sibling.
	 * @param string $right  Right sibling.
	 * @param string $result Expected result.
	 */
	public function test_apply_internal_off( $input, $left, $right, $result ) {
		$this->s->set_style_hanging_punctuation( false );

		$this->assertFixResultSame( $input, $input );
	}
}
