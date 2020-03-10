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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes;
use PHP_Typography\Settings;

/**
 * Wrap_Emails_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Wrap_Emails_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Wrap_Emails_Fix
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
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 */
class Wrap_Emails_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->fix = new Token_Fixes\Wrap_Emails_Fix();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$fix = new Token_Fixes\Wrap_Emails_Fix( true );

		$this->assertAttributeEquals( Token_Fix::OTHER, 'target', $fix, 'The fixer should be targetting OTHER tokens.' );
		$this->assertAttributeEquals( true, 'feed_compatible', $fix, 'The fixer should not be feed_compatible.' );
	}


	/**
	 * Provide data for testing wrap_emails.
	 *
	 * @return array
	 */
	public function provide_wrap_emails_data() {
		return [
			[ 'code@example.org',         'code@&#8203;example.&#8203;org' ],
			[ 'some.name@sub.domain.org', 'some.&#8203;name@&#8203;sub.&#8203;domain.&#8203;org' ],
			[ 'funny123@summer1.org',     'funny123@&#8203;summer1.&#8203;org' ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		$this->s->set_email_wrap( true );

		$this->assertFixResultSame( $input, $result, false, $this->getTextnode( 'foo', $input ) );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply_off( $input, $result ) {
		$this->s->set_email_wrap( false );

		$this->assertFixResultSame( $input, $input, false, $this->getTextnode( 'foo', $input ) );
	}
}
