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
use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix;
use PHP_Typography\Hyphenator\Cache;
use PHP_Typography\Settings;

/**
 * Process_Words_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix
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
class Process_Words_Fix_Test extends Node_Fix_Testcase {

	/**
	 * Our test object.
	 *
	 * @var Node_Fixes\Process_Words_Fix
	 */
	protected $fix;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->fix = new Node_Fixes\Process_Words_Fix();
	}

	/**
	 * Provide data for testing process_words.
	 *
	 * @return array
	 */
	public function provide_process_words_data() {
		return [
			[ 'superfluous', 'super&shy;flu&shy;ous', false ], // hyphenate.
			[ 'super-policemen', 'super-police&shy;men', false ], // hyphenate compounds.
			[ 'http://example.org', 'http://&#8203;exam&#8203;ple&#8203;.org', false ], // wrap URLs.
			[ 'foo@example.org', 'foo@&#8203;example.&#8203;org', false ], // wrap emails.
		];
	}

	/**
	 * Test get_text_parser.
	 *
	 * @covers ::get_text_parser
	 *
	 * @uses PHP_Typography\Text_Parser::__construct
	 */
	public function test_get_text_parser() {
		$this->assert_attribute_empty( 'text_parser', $this->fix );

		$parser1 = $this->fix->get_text_parser();
		$this->assertInstanceOf( '\PHP_Typography\Text_Parser', $parser1 );

		$parser2 = $this->fix->get_text_parser();
		$this->assertInstanceOf( '\PHP_Typography\Text_Parser', $parser2 );

		$this->assertSame( $parser1, $parser2 );
		$this->assert_attribute_instance_of( '\PHP_Typography\Text_Parser', 'text_parser', $this->fix );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::get_text_parser
	 * @uses ::register_token_fix
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_process_words_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_apply( $input, $result ) {
		// Create a stub for the Token_Fixer interface.
		$fake_token_fixer = $this->createMock( Token_Fix::class );
		$fake_token_fixer->method( 'apply' )->willReturn( $this->tokenize( $result ) );
		$fake_token_fixer->method( 'target' )->willReturn( Token_Fix::MIXED_WORDS );

		$this->fix->register_token_fix( $fake_token_fixer );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Tests register_token_fix.
	 *
	 * @covers ::register_token_fix
	 */
	public function test_register_token_fix() {
		// Create a stub for the Token_Fix interface.
		$fake_token_fixer = $this->createMock( Token_Fix::class );
		$fake_token_fixer->method( 'apply' )->willReturn( 'foo' );
		$fake_token_fixer->method( 'target' )->willReturn( Token_Fix::MIXED_WORDS );

		$this->fix->register_token_fix( $fake_token_fixer );
		$this->assert_attribute_contains( $fake_token_fixer, 'token_fixes', $this->fix, 'The registered fixer is not present in the $token_fixes array.' );
	}

	/**
	 * Tests update_hyphenator_cache.
	 *
	 * @covers ::update_hyphenator_cache
	 *
	 * @uses ::register_token_fix
	 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
	 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix::__construct
	 */
	public function test_update_hyphenator_cache() {
		// Create a stub for the Hyphenator\Cache class.
		$fake_cache = $this->createMock( Cache::class );

		// Create a stub for the Hyphenate_Fix class.
		$token_fixer = new Hyphenate_Fix();

		$this->fix->register_token_fix( $token_fixer );
		$this->fix->update_hyphenator_cache( $fake_cache );

		$this->assert_attribute_same( $fake_cache, 'cache', $token_fixer, 'The hyphenator cache was not update correctly.' );
	}
}
