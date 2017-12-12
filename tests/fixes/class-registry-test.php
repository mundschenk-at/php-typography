<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
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

namespace PHP_Typography\Tests\Fixes;

use PHP_Typography\Fixes\Registry;
use PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;
use PHP_Typography\Settings;
use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Strings;
use PHP_Typography\U;

/**
 * PHP_Typography unit test.
 *
 * @coversDefaultClass PHP_Typography\Fixes\Registry
 * @usesDefaultClass PHP_Typography\Fixes\Registry
 *
 * @uses ::__construct
 * @uses ::register_node_fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 */
class Registry_Test extends \PHP_Typography\Tests\PHP_Typography_Testcase {

	/**
	 * The Registry instance.
	 *
	 * @var Registry
	 */
	protected $r;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->r = new Registry();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}


	/**
	 * Tests register_node_fix.
	 *
	 * @covers ::register_node_fix
	 */
	public function test_register_node_fix() {
		foreach ( Registry::GROUPS as $group ) {
			// Create a stub for the Node_Fix interface.
			$fake_node_fixer = $this->createMock( Node_Fix::class );
			$fake_node_fixer->method( 'apply' )->willReturn( 'foo' );

			$this->r->register_node_fix( $fake_node_fixer, $group );
			$this->assertContains( $fake_node_fixer, $this->readAttribute( $this->r, 'node_fixes' )[ $group ] );
		}
	}

	/**
	 * Tests register_node_fix.
	 *
	 * @covers ::register_node_fix
	 *
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessageRegExp /^Invalid fixer group .+\.$/
	 */
	public function test_register_node_fix_invalid_group() {

		// Create a stub for the Node_Fix interface.
		$fake_node_fixer = $this->createMock( Node_Fix::class );
		$fake_node_fixer->method( 'apply' )->willReturn( 'foo' );

		$this->r->register_node_fix( $fake_node_fixer, 'invalid group parameter' );
	}

	/**
	 * Tests register_token_fix.
	 *
	 * @covers ::register_token_fix
	 *
	 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix::__construct
	 * @uses PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix::register_token_fix
	 */
	public function test_register_token_fix() {

		// Create a stub for the Token_Fix interface.
		$fake_token_fixer = $this->createMock( Token_Fix::class );
		$fake_token_fixer->method( 'apply' )->willReturn( 'foo' );
		$fake_token_fixer->method( 'target' )->willReturn( Token_Fix::MIXED_WORDS );

		$this->r->register_token_fix( $fake_token_fixer );
		$this->assertTrue( true, 'An error occured during Token_Fix registration.' );
	}


}
