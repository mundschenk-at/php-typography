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
use PHP_Typography\Fixes\Default_Registry;
use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\Fixes\Token_Fix;

use PHP_Typography\Tests\Testcase;

use Mockery as m;

/**
 * PHP_Typography unit test.
 *
 * @coversDefaultClass PHP_Typography\Fixes\Default_Registry
 * @usesDefaultClass PHP_Typography\Fixes\Default_Registry
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Default_Registry_Test extends \PHP_Typography\Tests\Testcase {

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 * @covers ::get_default_token_fixes
	 * @covers ::get_default_node_fixes
	 */
	public function test_constructor() {
		$registry = m::mock( Default_Registry::class )->makePartial();

		$fixes = [
			Registry::CHARACTERS         => [
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Maths_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Diacritics_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Dashes_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Ellipses_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Marks_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Area_Units_Fix::class, Node_Fix::class ),
			],
			Registry::SPACING_PRE_WORDS  => [
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Single_Character_Word_Spacing_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Dash_Spacing_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Unit_Spacing_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Numbered_Abbreviation_Spacing_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix::class, Node_Fix::class ),
			],
			Registry::SPACING_POST_WORDS => [
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Dewidow_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Space_Collapse_Fix::class, Node_Fix::class ),
			],
			Registry::HTML_INSERTION     => [
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Ordinal_Suffix_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Exponents_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Smart_Fractions_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Style_Caps_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Style_Numbers_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Style_Ampersands_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Style_Initial_Quotes_Fix::class, Node_Fix::class ),
				m::mock( 'alias:' . \PHP_Typography\Fixes\Node_Fixes\Style_Hanging_Punctuation_Fix::class, Node_Fix::class ),
			],
		];

		$token_fixes = [
			m::mock( 'alias:' . \PHP_Typography\Fixes\Token_Fixes\Wrap_Hard_Hyphens_Fix::class, Token_Fix::class ),
			m::mock( 'alias:' . \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Compounds_Fix::class, Token_Fix::class ),
			m::mock( 'alias:' . \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix::class, Token_Fix::class ),
			m::mock( 'alias:' . \PHP_Typography\Fixes\Token_Fixes\Wrap_URLs_Fix::class, Token_Fix::class ),
			m::mock( 'alias:' . \PHP_Typography\Fixes\Token_Fixes\Wrap_Emails_Fix::class, Token_Fix::class ),
		];

		foreach ( $fixes as $group => $fix_group ) {
			foreach ( $fix_group as $fix ) {
				$registry->shouldReceive( 'register_node_fix' )->once()->with( m::type( \get_class( $fix ) ), $group );
			}
		}

		foreach ( $token_fixes as $fix ) {
			$registry->shouldReceive( 'register_token_fix' )->once()->with( m::type( \get_class( $fix ) ) );
		}

		$this->assertNull( $registry->__construct( null, [] ) ); // @phpstan-ignore-line
	}
}
