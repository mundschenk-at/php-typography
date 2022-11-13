<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2022 Peter Putzer.
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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes;
use PHP_Typography\Settings;

/**
 * Wrap_URLs_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Wrap_URLs_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Wrap_URLs_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\RE
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Strings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Hyphenator\Cache
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 */
class Wrap_URLs_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Token_Fixes\Wrap_URLs_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$fix = new Token_Fixes\Wrap_URLs_Fix( null, true );

		$this->assert_attribute_same( Token_Fix::OTHER, 'target', $fix, 'The fixer should be targetting OTHER tokens.' );
		$this->assert_attribute_same( true, 'feed_compatible', $fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Provide data for testing wrap_urls.
	 *
	 * @return array
	 */
	public function provide_wrap_urls_data() {
		return [
			[ 'https://example.org/',                'https://&#8203;example&#8203;.org/',          2 ],
			[ 'http://example.org/',                 'http://&#8203;example&#8203;.org/',           2 ],
			[ 'https://my-example.org',              'https://&#8203;my&#8203;-example&#8203;.org', 2 ],
			[ 'https://example.org/some/long/path/', 'https://&#8203;example&#8203;.org/&#8203;s&#8203;o&#8203;m&#8203;e&#8203;/&#8203;l&#8203;o&#8203;n&#8203;g&#8203;/&#8203;path/', 5 ],
			[ 'https://example.org:8080/',           'https://&#8203;example&#8203;.org:8080/',     2 ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::split_domain
	 * @covers ::split_path
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_urls_data
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param int    $min_after Minimum number of characters after URL wrapping.
	 */
	public function test_apply( $input, $result, $min_after ) {
		$this->s->set_url_wrap( true );
		$this->s->set_min_after_url_wrap( $min_after );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_urls_data
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param int    $min_after Minimum number of characters after URL wrapping.
	 */
	public function test_apply_off( $input, $result, $min_after ) {
		$this->s->set_url_wrap( false );
		$this->s->set_min_after_url_wrap( $min_after );

		$this->assertFixResultSame( $input, $input );
	}
}
