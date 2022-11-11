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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes;
use PHP_Typography\Settings;

/**
 * Abstract_Token_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
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
class Abstract_Token_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Closure to call protected constructors.
	 *
	 * @var \Closure
	 */
	private $construct_caller;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->construct_caller = function( $target, $feed_compatible ) {
			$this->__construct( $target, $feed_compatible );
		};

	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$feed_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound    = $this->construct_caller->bindTo( $feed_fix, $feed_fix );
		$bound( Token_Fix::WORDS, true );

		$non_feed_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound        = $this->construct_caller->bindTo( $non_feed_fix, $non_feed_fix );
		$bound( Token_Fix::WORDS, false );

		$this->assert_attribute_same( true,  'feed_compatible', $feed_fix,     'The fixer should be feed_compatible.' );
		$this->assert_attribute_same( false, 'feed_compatible', $non_feed_fix, 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Tests the method feed_compatible().
	 *
	 * @covers ::feed_compatible
	 *
	 * @uses ::__construct
	 */
	public function test_feed_compatible() {
		$feed_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound    = $this->construct_caller->bindTo( $feed_fix, $feed_fix );
		$bound( Token_Fix::WORDS, true );

		$non_feed_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound        = $this->construct_caller->bindTo( $non_feed_fix, $non_feed_fix );
		$bound( Token_Fix::WORDS, false );

		$this->assertTrue( $feed_fix->feed_compatible(), 'The fixer should be feed_compatible.' );
		$this->assertFalse( $non_feed_fix->feed_compatible(), 'The fixer should not be feed_compatible.' );
	}

	/**
	 * Tests the method target().
	 *
	 * @covers ::target
	 *
	 * @uses ::__construct
	 */
	public function test_target() {
		$word_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound    = $this->construct_caller->bindTo( $word_fix, $word_fix );
		$bound( Token_Fix::WORDS, true );

		$other_fix = $this->getMockBuilder( Token_Fixes\Abstract_Token_Fix::class )
							->disableOriginalConstructor()
							->getMockForAbstractClass();
		$bound     = $this->construct_caller->bindTo( $other_fix, $other_fix );
		$bound( Token_Fix::OTHER, false );

		$this->assertSame( Token_Fix::WORDS, $word_fix->target(), 'The fixer should target WORD tokens.' );
		$this->assertSame( Token_Fix::OTHER, $other_fix->target(), 'The fixer should target OTHER tokens.' );
	}
}
