<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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
use PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix;
use PHP_Typography\Strings;
use PHP_Typography\U;

use PHP_Typography\Tests\Testcase;

use Mockery as m;

/**
 * PHP_Typography unit test.
 *
 * @coversDefaultClass PHP_Typography\Fixes\Registry
 * @usesDefaultClass PHP_Typography\Fixes\Registry
 *
 * @uses ::register_node_fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
 */
class Registry_Test extends Testcase {

	/**
	 * The Registry instance.
	 *
	 * @var Registry|\Mockery\MockInterface
	 */
	protected $r;

	/**
	 * Test fixture.
	 *
	 * @var Process_Words_Fix|\Mockery\MockInterface
	 */
	protected $pw_fix;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->r      = m::mock( Registry::class )->makePartial();
		$this->pw_fix = m::mock( Process_Words_Fix::class );
		$this->set_value( $this->r, 'process_words_fix', $this->pw_fix );

		$fixes                              = $this->get_value( $this->r, 'node_fixes' );
		$fixes[ Registry::PROCESS_WORDS ][] = $this->pw_fix;
		$this->set_value( $this->r, 'node_fixes', $fixes );
	}

	/**
	 * Tests __construct.
	 *
	 * @covers ::__construct
	 *
	 * @uses \PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix::__construct
	 */
	public function test_constructor() {
		$fix = m::mock( Registry::class )->makePartial();
		$fix->shouldReceive( 'register_node_fix' )->once()->with( m::type( Process_Words_Fix::class ), Registry::PROCESS_WORDS );

		$this->assertNull( $fix->__construct() ); // @phpstan-ignore-line
	}

	/**
	 * Tests get_node_fixes.
	 *
	 * @covers ::get_node_fixes
	 */
	public function test_get_node_fixes() {
		$fixes = $this->r->get_node_fixes();

		$this->assert_is_array( $fixes );
		$this->assertCount( count( Registry::GROUPS ), $fixes );
	}

	/**
	 * Tests register_node_fix.
	 *
	 * @covers ::register_node_fix
	 */
	public function test_register_node_fix() {
		foreach ( Registry::GROUPS as $group ) {
			// Create a stub for the Node_Fix interface.
			$fake_node_fixer = m::mock( Node_Fix::class );

			$this->r->register_node_fix( $fake_node_fixer, $group );
			$this->assertContains( $fake_node_fixer, $this->get_value( $this->r, 'node_fixes' )[ $group ] );
		}
	}

	/**
	 * Tests register_node_fix.
	 *
	 * @covers ::register_node_fix
	 */
	public function test_register_node_fix_invalid_group() {

		// Create a stub for the Node_Fix interface.
		$fake_node_fixer = m::mock( Node_Fix::class );

		$this->expect_exception( \InvalidArgumentException::class );
		$this->expect_exception_message_matches( '/^Invalid fixer group .+\.$/' );

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
		$fake_token_fixer = m::mock( Token_Fix::class );

		$this->pw_fix->shouldReceive( 'register_token_fix' )->once()->with( $fake_token_fixer );

		$this->r->register_token_fix( $fake_token_fixer );
		$this->assertTrue( true, 'An error occured during Token_Fix registration.' );
	}

	/**
	 * Tests update_hyphenator_cache.
	 *
	 * @covers ::update_hyphenator_cache
	 */
	public function test_update_hyphenator_cache() {
		$cache = m::mock( \PHP_Typography\Hyphenator\Cache::class );

		$this->pw_fix->shouldReceive( 'update_hyphenator_cache' )->once()->with( $cache );

		$this->assertNull( $this->r->update_hyphenator_cache( $cache ) ); // @phpstan-ignore-line
	}

	/**
	 * Tests apply_fixes.
	 *
	 * @covers ::apply_fixes
	 */
	public function test_apply_fixes() {
		$node = m::mock( \DOMText::class );
		$s    = m::mock( Settings::class );

		foreach ( Registry::GROUPS as $group ) {
			$fix = m::mock( \PHP_Typography\Fixes\Node_Fix::class );
			$this->r->register_node_fix( $fix, $group );
			$fix->shouldReceive( 'apply' )->once()->with( $node, $s, false );
		}

		$this->pw_fix->shouldReceive( 'apply' )->once()->with( $node, $s, false );

		$this->assertNull( $this->r->apply_fixes( $node, $s, false, false ) ); // @phpstan-ignore-line
	}

	/**
	 * Tests apply_fixes for feeds.
	 *
	 * @covers ::apply_fixes
	 */
	public function test_apply_fixes_to_feed() {
		$node = m::mock( \DOMText::class );
		$s    = m::mock( Settings::class );

		$toggle = false;
		foreach ( Registry::GROUPS as $group ) {
			$fix = m::mock( \PHP_Typography\Fixes\Node_Fix::class );
			$this->r->register_node_fix( $fix, $group );

			$fix->shouldReceive( 'feed_compatible' )->once()->andReturn( $toggle );

			if ( $toggle ) {
				$fix->shouldReceive( 'apply' )->once()->with( $node, $s, false );
			} else {
				$fix->shouldNotReceive( 'apply' );
			}
		}

		$this->pw_fix->shouldReceive( 'apply' )->once()->with( $node, $s, false );
		$this->pw_fix->shouldReceive( 'feed_compatible' )->once()->andReturn( true );

		$this->assertNull( $this->r->apply_fixes( $node, $s, false, true ) ); // @phpstan-ignore-line
	}
}
