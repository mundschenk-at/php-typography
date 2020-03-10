<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2019 Peter Putzer.
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

namespace PHP_Typography\Tests;

use PHP_Typography\Settings;
use PHP_Typography\Strings;
use PHP_Typography\U;

use PHP_Typography\Settings\Dashes;
use PHP_Typography\Settings\Quotes;

use Brain\Monkey;

use Mockery as m;

/**
 * Unit test for Settings class.
 *
 * @coversDefaultClass \PHP_Typography\Settings
 * @usesDefaultClass \PHP_Typography\Settings
 *
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
 * @uses PHP_Typography\Strings::uchr
 * @uses PHP_Typography\DOM::inappropriate_tags
 */
class Settings_Test extends PHP_Typography_Testcase {
	/**
	 * Settings fixture.
	 *
	 * @var \PHP_Typography\Settings
	 */
	protected $settings;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->settings = new \PHP_Typography\Settings( false, [] );
	}

	/**
	 * Tests set_defaults.
	 *
	 * @covers ::set_defaults
	 *
	 * @uses ::array_map_assoc
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 * @uses PHP_Typography\DOM::inappropriate_tags
	 */
	public function test_set_defaults() {
		$second_settings = new \PHP_Typography\Settings( false );
		$this->assertAttributeEmpty( 'data', $second_settings );
		$second_settings->set_defaults();
		$this->assertAttributeNotEmpty( 'data', $second_settings );
	}

	/**
	 * Tests initialization.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::set_defaults
	 * @uses ::array_map_assoc
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 * @uses PHP_Typography\DOM::inappropriate_tags
	 */
	public function test_initialization() {
		$s = $this->settings;

		// No defaults.
		$this->assertAttributeEmpty( 'data', $s );

		// After set_defaults().
		$s->set_defaults();
		$this->assertAttributeNotEmpty( 'data', $s );

		$second_settings = new \PHP_Typography\Settings( true );
		$this->assertAttributeNotEmpty( 'data', $second_settings );
	}


	/**
	 * Tests __get.
	 *
	 * @covers ::__get
	 *
	 * @uses ::offsetGet
	 */
	public function test___get() {
		$s = $this->settings;

		$s['new_key'] = 42;
		$this->assertEquals( 42, $s->new_key );
	}

	/**
	 * Tests __set.
	 *
	 * @covers ::__set
	 *
	 * @uses ::__get
	 * @uses ::__isset
	 */
	public function test___set() {
		$s = $this->settings;

		$this->assertFalse( isset( $s->new_key ) );
		$s->new_key = 42;
		$this->assertTrue( isset( $s->new_key ) );
	}

	/**
	 * Tests __isset.
	 *
	 * @covers ::__isset
	 */
	public function test___isset() {
		$s = $this->settings;

		$this->assertFalse( isset( $s->new_key ) );
		$s->new_key = 42;
		$this->assertTrue( isset( $s->new_key ) );
	}

	/**
	 * Tests __unset.
	 *
	 * @covers ::__unset
	 */
	public function test___unset() {
		$s = $this->settings;

		$s->new_key = 42;
		$this->assertTrue( isset( $s->new_key ) );

		unset( $s->new_key );
		$this->assertFalse( isset( $s->new_key ) );
	}

	/**
	 * Tests offsetSet.
	 *
	 * @covers ::offsetSet
	 *
	 * @uses ::offsetGet
	 * @uses ::offsetExists
	 */
	public function test_offsetSet() {
		$s = $this->settings;

		// A key has to be used.
		$this->assertFalse( isset( $s[0] ) );
		$s[] = 666;
		$this->assertFalse( isset( $s[0] ) );

		$this->assertFalse( isset( $s['new_key'] ) );
		$s['new_key'] = 42;
		$this->assertEquals( 42, $s['new_key'] );
	}

	/**
	 * Tests offsetExists.
	 *
	 * @covers ::offsetExists
	 *
	 * @uses ::offsetSet
	 */
	public function test_offsetExists() {
		$s = $this->settings;

		$this->assertFalse( isset( $s['new_key'] ) );
		$s['new_key'] = 42;
		$this->assertTrue( isset( $s['new_key'] ) );

	}

	/**
	 * Tests offsetUnset.
	 *
	 * @covers ::offsetUnset
	 *
	 * @uses ::offsetSet
	 * @uses ::offsetGet
	 * @uses ::offsetExists
	 */
	public function test_offsetUnset() {
		$s = $this->settings;

		$s['new_key'] = 42;
		$this->assertTrue( isset( $s['new_key'] ) );

		unset( $s['new_key'] );
		$this->assertFalse( isset( $s['new_key'] ) );
	}

	/**
	 * Tests offsetGet.
	 *
	 * @covers ::offsetGet
	 *
	 * @uses ::offsetSet
	 */
	public function test_offsetGet() {
		$s = $this->settings;
		$this->assertNull( $s['new_key'] );

		$s['new_key'] = 42;
		$this->assertEquals( 42, $s['new_key'] );
	}

	/**
	 * Tests primary_quote_style.
	 *
	 * @covers ::primary_quote_style
	 *
	 * @uses ::set_smart_quotes_primary
	 */
	public function test_primary_quote_style() {
		$s = $this->settings;
		$s->set_smart_quotes_primary();

		$this->assertInstanceOf( Quotes::class, $s->primary_quote_style(), 'Primary quote style is not an instance of Quotes.' );
	}

	/**
	 * Tests secondary_quote_style.
	 *
	 * @covers ::secondary_quote_style
	 *
	 * @uses ::set_smart_quotes_secondary
	 */
	public function test_secondary_quote_style() {
		$s = $this->settings;
		$s->set_smart_quotes_secondary();

		$this->assertInstanceOf( Quotes::class, $s->secondary_quote_style(), 'Secondary quote style is not an instance of Quotes.' );
	}

	/**
	 * Tests dash_style.
	 *
	 * @covers ::dash_style
	 *
	 * @uses ::set_smart_dashes_style
	 */
	public function test_dash_style() {
		$s = $this->settings;
		$s->set_smart_dashes_style();

		$this->assertInstanceOf( Dashes::class, $s->dash_style(), 'Dash style is not an instance of Dashes.' );
	}

	/**
	 * Tests custom_units.
	 *
	 * @covers ::custom_units
	 */
	public function test_custom_units() {
		$s = $this->settings;

		$this->assertInternalType( 'string', $s->custom_units(), 'The result of custom_units() is not a string.' );
	}


	/**
	 * Tests set_ignore_parser_errors.
	 *
	 * @covers ::set_ignore_parser_errors
	 */
	public function test_set_ignore_parser_errors() {
		$s = $this->settings;

		$s->set_ignore_parser_errors( true );
		$this->assertTrue( $s[ Settings::PARSER_ERRORS_IGNORE ] );

		$s->set_ignore_parser_errors( false );
		$this->assertFalse( $s[ Settings::PARSER_ERRORS_IGNORE ] );
	}

	/**
	 * Tests set_parser_errors_handler.
	 *
	 * @covers ::set_parser_errors_handler
	 */
	public function test_set_parser_errors_handler() {
		$s = $this->settings;

		// Default: no handler.
		$this->assertEmpty( $s[ Settings::PARSER_ERRORS_HANDLER ] );

		// Valid handler.
		$s->set_parser_errors_handler(
			function( $errors ) {
				return [];
			}
		);
		$this->assertInternalType( 'callable', $s[ Settings::PARSER_ERRORS_HANDLER ] );
		$old_handler = $s[ Settings::PARSER_ERRORS_HANDLER ];
	}

	/**
	 * Tests set_parser_errors_handler with an invalid callback.
	 *
	 * @covers ::set_parser_errors_handler
	 */
	public function test_set_parser_errors_handler_invalid() {
		$s = $this->settings;

		// Default: no handler.
		$this->assertEmpty( $s[ Settings::PARSER_ERRORS_HANDLER ] );

		// Valid handler.
		$s->set_parser_errors_handler(
			function( $errors ) {
				return [];
			}
		);
		$this->assertInternalType( 'callable', $s[ Settings::PARSER_ERRORS_HANDLER ] );
		$old_handler = $s[ Settings::PARSER_ERRORS_HANDLER ];

		// PHP < 7.0 raises an error instead of throwing an "exception".
		if ( version_compare( phpversion(), '7.0.0', '<' ) ) {
			$this->expectException( \PHPUnit_Framework_Error::class );
		} else {
			$this->expectException( \TypeError::class );
		}

		// Invalid handler, previous handler not changed.
		$s->set_parser_errors_handler( 'foobar' );
		$this->assertInternalType( 'callable', $s[ Settings::PARSER_ERRORS_HANDLER ] );
		$this->assertSame( $old_handler, $s[ Settings::PARSER_ERRORS_HANDLER ] );
	}

	/**
	 * Tests set_tags_to_ignore.
	 *
	 * @covers ::set_tags_to_ignore
	 *
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_tags_to_ignore() {
		$s             = $this->settings;
		$always_ignore = [ 'iframe', 'textarea', 'button', 'select', 'optgroup', 'option', 'map', 'style', 'head', 'title', 'script', 'applet', 'object', 'param', 'svg', 'math' ];

		// Default tags.
		$s->set_tags_to_ignore( [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ] );
		$this->assertArraySubset( [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ], $s['ignoreTags'] );
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		// Auto-close tag and something else.
		$s->set_tags_to_ignore( [ 'img', 'foo' ] );
		$this->assertContains( 'foo', $s['ignoreTags'] );

		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		$s->set_tags_to_ignore( 'img foo  \	' ); // should not result in an error.
	}

	/**
	 * Tests set_classes_to_ignore.
	 *
	 * @covers ::set_classes_to_ignore
	 *
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_classes_to_ignore() {
		$s = $this->settings;

		$s->set_classes_to_ignore( 'foo bar' );
		$this->assertContains( 'foo', $this->settings['ignoreClasses'] );
		$this->assertContains( 'bar', $this->settings['ignoreClasses'] );
	}

	/**
	 * Tests set_ids_to_ignore.
	 *
	 * @covers ::set_ids_to_ignore
	 *
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_ids_to_ignore() {
		$s = $this->settings;

		$s->set_ids_to_ignore( 'foobar barfoo' );
		$this->assertContains( 'foobar', $this->settings['ignoreIDs'] );
		$this->assertContains( 'barfoo', $this->settings['ignoreIDs'] );
	}

	/**
	 * Tests set_smart_quotes.
	 *
	 * @covers ::set_smart_quotes
	 */
	public function test_set_smart_quotes() {
		$this->settings->set_smart_quotes( true );
		$this->assertTrue( $this->settings[ Settings::SMART_QUOTES ] );

		$this->settings->set_smart_quotes( false );
		$this->assertFalse( $this->settings[ Settings::SMART_QUOTES ] );
	}

	/**
	 * Tests set_smart_quotes_primary.
	 *
	 * @covers ::set_smart_quotes_primary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 */
	public function test_set_smart_quotes_primary() {
		$s = $this->settings;

		$quote_styles = [
			'doubleCurled',
			'doubleCurledReversed',
			'doubleLow9',
			'doubleLow9Reversed',
			'singleCurled',
			'singleCurledReversed',
			'singleLow9',
			'singleLow9Reversed',
			'doubleGuillemetsFrench',
			'doubleGuillemets',
			'doubleGuillemetsReversed',
			'singleGuillemets',
			'singleGuillemetsReversed',
			'cornerBrackets',
			'whiteCornerBracket',
		];

		foreach ( $quote_styles as $style ) {
			$s->set_smart_quotes_primary( $style );

			$this->assertSmartQuotesStyle( $style, $s->primary_quote_style()->open(), $s->primary_quote_style()->close() );
		}
	}

	/**
	 * Tests set_smart_quotes_primary with an invalid input.
	 *
	 * @covers ::set_smart_quotes_primary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @expectedException \DomainException
	 * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
	 */
	public function test_set_smart_quotes_primary_invalid() {
		$s = $this->settings;

		$s->set_smart_quotes_primary( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_quotes_primary with a Quotes object.
	 *
	 * @covers ::set_smart_quotes_primary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 */
	public function test_set_smart_quotes_primary_to_object() {
		$s = $this->settings;

		// Create a stub for the Token_Fixer interface.
		$fake_quotes = $this->createMock( Quotes::class );
		$fake_quotes->method( 'open' )->willReturn( 'x' );
		$fake_quotes->method( 'close' )->willReturn( 'y' );

		$s->set_smart_quotes_primary( $fake_quotes );

		$this->assertSame( 'x', $s->primary_quote_style()->open() );
		$this->assertSame( 'y', $s->primary_quote_style()->close() );
	}

	/**
	 * Tests set_smart_quotes_secondary.
	 *
	 * @covers ::set_smart_quotes_secondary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 */
	public function test_set_smart_quotes_secondary() {
		$s = $this->settings;

		$quote_styles = [
			'doubleCurled',
			'doubleCurledReversed',
			'doubleLow9',
			'doubleLow9Reversed',
			'singleCurled',
			'singleCurledReversed',
			'singleLow9',
			'singleLow9Reversed',
			'doubleGuillemetsFrench',
			'doubleGuillemets',
			'doubleGuillemetsReversed',
			'singleGuillemets',
			'singleGuillemetsReversed',
			'cornerBrackets',
			'whiteCornerBracket',
		];

		foreach ( $quote_styles as $style ) {
			$s->set_smart_quotes_secondary( $style );

			$this->assertSmartQuotesStyle( $style, $s->secondary_quote_style()->open(), $s->secondary_quote_style()->close() );
		}
	}

	/**
	 * Tests set_smart_quotes_secondary with an invalid input.
	 *
	 * @covers ::set_smart_quotes_secondary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @expectedException \DomainException
	 * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
	 */
	public function test_set_smart_quotes_secondary_invalid() {
		$s = $this->settings;

		$s->set_smart_quotes_secondary( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_quotes_secondary with a Quotes object.
	 *
	 * @covers ::set_smart_quotes_secondary
	 * @covers ::get_quote_style
	 * @covers ::get_style
	 */
	public function test_set_smart_quotes_secondary_to_object() {
		$s = $this->settings;

		// Create a stub for the Token_Fixer interface.
		$fake_quotes = $this->createMock( Quotes::class );
		$fake_quotes->method( 'open' )->willReturn( 'xx' );
		$fake_quotes->method( 'close' )->willReturn( 'yy' );

		$s->set_smart_quotes_secondary( $fake_quotes );

		$this->assertSame( 'xx', $s->secondary_quote_style()->open() );
		$this->assertSame( 'yy', $s->secondary_quote_style()->close() );
	}

	/**
	 * Tests set_smart_quotes_exceptions.
	 *
	 * @covers ::set_smart_quotes_exceptions
	 */
	public function test_set_smart_quotes_exceptions() {
		$this->settings->set_smart_quotes_exceptions();

		$exceptions = $this->settings[ Settings::SMART_QUOTES_EXCEPTIONS ];
		$this->assertCount( 2, $exceptions );
		$this->assertGreaterThan( 1, count( $exceptions['patterns'] ) );
		$this->assertEquals( count( $exceptions['patterns'] ), count( $exceptions['replacements'] ) );

		$this->settings->set_smart_quotes_exceptions( [ 'Yfoo' => 'Xfoo' ] );
		$exceptions = $this->settings[ Settings::SMART_QUOTES_EXCEPTIONS ];
		$this->assertCount( 2, $exceptions );
		$this->assertEquals( [ 'Yfoo' ], $exceptions['patterns'] );
		$this->assertEquals( [ 'Xfoo' ], $exceptions['replacements'] );
	}

	/**
	 * Test set_smart_dashes.
	 *
	 * @covers ::set_smart_dashes
	 */
	public function test_set_smart_dashes() {
		$this->settings->set_smart_dashes( true );
		$this->assertTrue( $this->settings[ Settings::SMART_DASHES ] );

		$this->settings->set_smart_dashes( false );
		$this->assertFalse( $this->settings[ Settings::SMART_DASHES ] );
	}

	/**
	 * Test set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 */
	public function test_set_smart_dashes_style() {
		$s = $this->settings;

		$s->set_smart_dashes_style( 'traditionalUS' );
		$dashes = $s->dash_style();

		$this->assertSame( U::EM_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( U::THIN_SPACE, $dashes->parenthetical_space() );
		$this->assertSame( U::THIN_SPACE, $dashes->interval_space() );

		$s->set_smart_dashes_style( 'international' );
		$dashes = $s->dash_style();

		$this->assertSame( U::EN_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( ' ', $dashes->parenthetical_space() );
		$this->assertSame( U::HAIR_SPACE, $dashes->interval_space() );

		$s->set_smart_dashes_style( 'internationalNoHairSpaces' );
		$dashes = $s->dash_style();

		$this->assertSame( U::EN_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( ' ', $dashes->parenthetical_space() );
		$this->assertSame( '', $dashes->interval_space() );
	}

	/**
	 * Test set_smart_dashes_style with a Dashes object.
	 *
	 * @covers ::set_smart_dashes_style
	 * @covers ::get_style
	 */
	public function test_set_smart_dashes_style_with_object() {
		$s = $this->settings;

		// Create a stub for the Token_Fixer interface.
		$fake_dashes = $this->createMock( Dashes::class );
		$fake_dashes->method( 'parenthetical_dash' )->willReturn( 'a' );
		$fake_dashes->method( 'parenthetical_space' )->willReturn( 'b' );
		$fake_dashes->method( 'interval_dash' )->willReturn( 'c' );
		$fake_dashes->method( 'interval_space' )->willReturn( 'd' );

		$s->set_smart_dashes_style( $fake_dashes );
		$dashes = $s->dash_style();

		$this->assertSame( 'a', $dashes->parenthetical_dash() );
		$this->assertSame( 'b', $dashes->parenthetical_space() );
		$this->assertSame( 'c', $dashes->interval_dash() );
		$this->assertSame( 'd', $dashes->interval_space() );
	}

	/**
	 * Tests set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 * @covers ::get_style
	 *
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 *
	 * @expectedException \DomainException
	 * @expectedExceptionMessageRegExp /^Invalid dash style \w+.$/
	 */
	public function test_set_smart_dashes_style_invalid() {
		$s = $this->settings;

		$s->set_smart_dashes_style( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_ellipses.
	 *
	 * @covers ::set_smart_ellipses
	 */
	public function test_set_smart_ellipses() {
		$this->settings->set_smart_ellipses( true );
		$this->assertTrue( $this->settings[ Settings::SMART_ELLIPSES ] );

		$this->settings->set_smart_ellipses( false );
		$this->assertFalse( $this->settings[ Settings::SMART_ELLIPSES ] );
	}

	/**
	 * Tests set_smart_diacritics.
	 *
	 * @covers ::set_smart_diacritics
	 */
	public function test_set_smart_diacritics() {
		$this->settings->set_smart_diacritics( true );
		$this->assertTrue( $this->settings[ Settings::SMART_DIACRITICS ] );

		$this->settings->set_smart_diacritics( false );
		$this->assertFalse( $this->settings[ Settings::SMART_DIACRITICS ] );
	}

	/**
	 * Tests set_diacritic_language.
	 *
	 * @covers ::set_diacritic_language
	 * @covers ::update_diacritics_replacement_arrays
	 * @covers ::parse_diacritics_rules
	 */
	public function test_set_diacritic_language() {
		$this->settings->set_diacritic_language( 'en-US' );
		$this->assertGreaterThan( 0, count( $this->settings[ Settings::DIACRITIC_WORDS ] ) );

		$this->settings->set_diacritic_language( 'foobar' );
		$this->assertFalse( isset( $this->settings[ Settings::DIACRITIC_WORDS ] ) );

		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings[ Settings::DIACRITIC_WORDS ] ) );
		$this->assertGreaterThan( 0, count( $this->settings[ Settings::DIACRITIC_WORDS ] ) );

		// Nothing changed since the last call.
		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings[ Settings::DIACRITIC_WORDS ] ) );
		$this->assertGreaterThan( 0, count( $this->settings[ Settings::DIACRITIC_WORDS ] ) );
	}

	/**
	 * Provide data for testing set_diacritic_custom_replacements.
	 */
	public function provide_set_diacritic_custom_replacements_data() {
		return [
			[
				'"foo" => "fóò", "bar" => "bâr"' . ", 'ha' => 'hä'",
				[ 'foo', 'bar', 'ha' ],
				[ 'fóò', 'bâr', 'hä' ],
			],
			[
				'"fo\'o" => "fó\'ò", "bar" => "bâr"' . ", 'h\"a' => 'h\"ä'",
				[ "fo'o", 'bar', 'h"a' ],
				[ "fó'ò", 'bâr', 'h"ä' ],
			],
			[
				[
					'fööbar' => 'fúbar',
				],
				[ 'fööbar' ],
				[ 'fúbar' ],
			],

			[
				[
					' ' => 'fúbar',
				],
				[],
				[],
			],

			[
				[
					'fööbar' => '',
				],
				[],
				[],
			],
			[
				'foobar',
				[],
				[],
			],
		];
	}

	/**
	 * Tests set_diacritic_custom_replacements.
	 *
	 * @covers ::set_diacritic_custom_replacements
	 * @covers ::parse_diacritics_replacement_string
	 * @covers ::update_diacritics_replacement_arrays
	 * @covers ::parse_diacritics_rules
	 *
	 * @uses ::array_map_assoc
	 *
	 * @dataProvider provide_set_diacritic_custom_replacements_data
	 *
	 * @param string|array $input  Custom replacements string or array.
	 * @param array        $keys   Expected keys.
	 * @param array        $values Expected values.
	 */
	public function test_set_diacritic_custom_replacements( $input, array $keys, array $values ) {
		$s = $this->settings;

		$s->set_diacritic_custom_replacements( $input );

		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $s[ Settings::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		}

		foreach ( $values as $value ) {
			$this->assertContains( $value, $s[ Settings::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		}

		$this->assertCount( count( $keys ), $s[ Settings::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		$this->assertCount( count( $values ), $s[ Settings::DIACRITIC_CUSTOM_REPLACEMENTS ] );
	}

	/**
	 * Test set_smart_marks.
	 *
	 * @covers ::set_smart_marks
	 */
	public function test_set_smart_marks() {
		$this->settings->set_smart_marks( true );
		$this->assertTrue( $this->settings[ Settings::SMART_MARKS ] );

		$this->settings->set_smart_marks( false );
		$this->assertFalse( $this->settings[ Settings::SMART_MARKS ] );
	}

	/**
	 * Test set_smart_area_units.
	 *
	 * @covers ::set_smart_area_units
	 */
	public function test_set_smart_area_units() {
		$this->settings->set_smart_area_units( true );
		$this->assertTrue( $this->settings[ Settings::SMART_AREA_UNITS ] );

		$this->settings->set_smart_area_units( false );
		$this->assertFalse( $this->settings[ Settings::SMART_AREA_UNITS ] );
	}

	/**
	 * Tests set_smart_math.
	 *
	 * @covers ::set_smart_math
	 */
	public function test_set_smart_math() {
		$this->settings->set_smart_math( true );
		$this->assertTrue( $this->settings[ Settings::SMART_MATH ] );

		$this->settings->set_smart_math( false );
		$this->assertFalse( $this->settings[ Settings::SMART_MATH ] );
	}

	/**
	 * Tests set_smart_exponents.
	 *
	 * @covers ::set_smart_exponents
	 */
	public function test_set_smart_exponents() {
		$this->settings->set_smart_exponents( true );
		$this->assertTrue( $this->settings[ Settings::SMART_EXPONENTS ] );

		$this->settings->set_smart_exponents( false );
		$this->assertFalse( $this->settings[ Settings::SMART_EXPONENTS ] );
	}

	/**
	 * Tests set_smart_fractions.
	 *
	 * @covers ::set_smart_fractions
	 */
	public function test_set_smart_fractions() {
		$this->settings->set_smart_fractions( true );
		$this->assertTrue( $this->settings[ Settings::SMART_FRACTIONS ] );

		$this->settings->set_smart_fractions( false );
		$this->assertFalse( $this->settings[ Settings::SMART_FRACTIONS ] );
	}

	/**
	 * Tests set_smart_ordinal_suffix.
	 *
	 * @covers ::set_smart_ordinal_suffix
	 */
	public function test_set_smart_ordinal_suffix() {
		$this->settings->set_smart_ordinal_suffix( true );
		$this->assertTrue( $this->settings[ Settings::SMART_ORDINAL_SUFFIX ] );

		$this->settings->set_smart_ordinal_suffix( false );
		$this->assertFalse( $this->settings[ Settings::SMART_ORDINAL_SUFFIX ] );
	}

	/**
	 * Tests set_smart_ordinal_suffix_match_roman_numerals.
	 *
	 * @covers ::set_smart_ordinal_suffix_match_roman_numerals
	 */
	public function test_set_smart_ordinal_suffix_match_roman_numerals() {
		$this->settings->set_smart_ordinal_suffix_match_roman_numerals( true );
		$this->assertTrue( $this->settings[ Settings::SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS ] );

		$this->settings->set_smart_ordinal_suffix_match_roman_numerals( false );
		$this->assertFalse( $this->settings[ Settings::SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS ] );
	}

	/**
	 * Tests set_single_character_word_spacing.
	 *
	 * @covers ::set_single_character_word_spacing
	 */
	public function test_set_single_character_word_spacing() {
		$this->settings->set_single_character_word_spacing( true );
		$this->assertTrue( $this->settings[ Settings::SINGLE_CHARACTER_WORD_SPACING ] );

		$this->settings->set_single_character_word_spacing( false );
		$this->assertFalse( $this->settings[ Settings::SINGLE_CHARACTER_WORD_SPACING ] );
	}

	/**
	 * Tests set_fraction_spacing.
	 *
	 * @covers ::set_fraction_spacing
	 */
	public function test_set_fraction_spacing() {
		$this->settings->set_fraction_spacing( true );
		$this->assertTrue( $this->settings[ Settings::FRACTION_SPACING ] );

		$this->settings->set_fraction_spacing( false );
		$this->assertFalse( $this->settings[ Settings::FRACTION_SPACING ] );
	}

	/**
	 * Tests set_unit_spacing.
	 *
	 * @covers ::set_unit_spacing
	 */
	public function test_set_unit_spacing() {
		$this->settings->set_unit_spacing( true );
		$this->assertTrue( $this->settings[ Settings::UNIT_SPACING ] );

		$this->settings->set_unit_spacing( false );
		$this->assertFalse( $this->settings[ Settings::UNIT_SPACING ] );
	}

	/**
	 * Tests set_numbered_abbreviation_spacing.
	 *
	 * @covers ::set_numbered_abbreviation_spacing
	 */
	public function test_set_numbered_abbreviation_spacing() {
		$this->settings->set_numbered_abbreviation_spacing( true );
		$this->assertTrue( $this->settings[ Settings::NUMBERED_ABBREVIATION_SPACING ] );

		$this->settings->set_numbered_abbreviation_spacing( false );
		$this->assertFalse( $this->settings[ Settings::NUMBERED_ABBREVIATION_SPACING ] );
	}

	/**
	 * Tests set_french_punctuation_spacing.
	 *
	 * @covers ::set_french_punctuation_spacing
	 */
	public function test_set_french_punctuation_spacing() {
		$this->settings->set_french_punctuation_spacing( true );
		$this->assertTrue( $this->settings[ Settings::FRENCH_PUNCTUATION_SPACING ] );

		$this->settings->set_french_punctuation_spacing( false );
		$this->assertFalse( $this->settings[ Settings::FRENCH_PUNCTUATION_SPACING ] );
	}

	/**
	 * Tests set_units.
	 *
	 * @covers ::set_units
	 *
	 * @uses ::update_unit_pattern
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_units() {
		$units_as_array  = [ 'foo', 'bar', 'xx/yy' ];
		$units_as_string = implode( ', ', $units_as_array );

		$this->settings->set_units( $units_as_array );
		foreach ( $units_as_array as $unit ) {
			$this->assertContains( $unit, $this->settings[ Settings::UNITS ] );
		}

		$this->settings->set_units( [] );
		foreach ( $units_as_array as $unit ) {
			$this->assertNotContains( $unit, $this->settings[ Settings::UNITS ] );
		}

		$this->settings->set_units( $units_as_string );
		foreach ( $units_as_array as $unit ) {
			$this->assertContains( $unit, $this->settings[ Settings::UNITS ] );
		}
	}

	/**
	 * Provides data for testing update_unit_pattern.
	 *
	 * @return array
	 */
	public function provide_update_unit_pattern_data() {
		return [
			[
				[ 'km/h', 'T$' ],
				'km\/h|T\$|',
			],
			[
				[ '¥', 'm[a]', 'n.', 'm^2' ],
				'¥|m\[a\]|n\.|m\^2|',
			],
		];
	}

	/**
	 * Tests update_unit_pattern.
	 *
	 * @covers ::update_unit_pattern
	 *
	 * @dataProvider provide_update_unit_pattern_data
	 *
	 * @param  string[] $units An array of units.
	 * @param  string   $regex The resulting regular expression.
	 */
	public function test_update_unit_pattern( array $units, $regex ) {
		$result = $this->invokeMethod( $this->settings, 'update_unit_pattern', [ $units ] );

		$this->assertSame( $regex, $result );
	}

	/**
	 * Tests set_dash_spacing.
	 *
	 * @covers ::set_dash_spacing
	 */
	public function test_set_dash_spacing() {
		$this->settings->set_dash_spacing( true );
		$this->assertTrue( $this->settings[ Settings::DASH_SPACING ] );

		$this->settings->set_dash_spacing( false );
		$this->assertFalse( $this->settings[ Settings::DASH_SPACING ] );
	}

	/**
	 * Tests set_space_collapse.
	 *
	 * @covers ::set_space_collapse
	 */
	public function test_set_space_collapse() {
		$this->settings->set_space_collapse( true );
		$this->assertTrue( $this->settings[ Settings::SPACE_COLLAPSE ] );

		$this->settings->set_space_collapse( false );
		$this->assertFalse( $this->settings[ Settings::SPACE_COLLAPSE ] );
	}

	/**
	 * Tests set_dewidow.
	 *
	 * @covers ::set_dewidow
	 */
	public function test_set_dewidow() {
		$this->settings->set_dewidow( true );
		$this->assertTrue( $this->settings[ Settings::DEWIDOW ] );

		$this->settings->set_dewidow( false );
		$this->assertFalse( $this->settings[ Settings::DEWIDOW ] );
	}

	/**
	 * Tests set_max_dewidow_length.
	 *
	 * @covers ::set_max_dewidow_length
	 */
	public function test_set_max_dewidow_length() {
		$this->settings->set_max_dewidow_length( 10 );
		$this->assertSame( 10, $this->settings[ Settings::DEWIDOW_MAX_LENGTH ] );

		$this->settings->set_max_dewidow_length( 1 );
		$this->assertSame( 5, $this->settings[ Settings::DEWIDOW_MAX_LENGTH ] );

		$this->settings->set_max_dewidow_length( 2 );
		$this->assertSame( 2, $this->settings[ Settings::DEWIDOW_MAX_LENGTH ] );
	}

	/**
	 * Tests set_dewidow_word_number.
	 *
	 * @covers ::set_dewidow_word_number
	 */
	public function test_set_dewidow_word_number() {
		$this->settings->set_dewidow_word_number( 10 );
		$this->assertSame( 1, $this->settings[ Settings::DEWIDOW_WORD_NUMBER ] );

		$this->settings->set_dewidow_word_number( 1 );
		$this->assertSame( 1, $this->settings[ Settings::DEWIDOW_WORD_NUMBER ] );

		$this->settings->set_dewidow_word_number( 2 );
		$this->assertSame( 2, $this->settings[ Settings::DEWIDOW_WORD_NUMBER ] );

		$this->settings->set_dewidow_word_number( 3 );
		$this->assertSame( 3, $this->settings[ Settings::DEWIDOW_WORD_NUMBER ] );

		$this->settings->set_dewidow_word_number( 4 );
		$this->assertSame( 1, $this->settings[ Settings::DEWIDOW_WORD_NUMBER ] );
	}

	/**
	 * Tests set_max_dewidow_pull.
	 *
	 * @covers ::set_max_dewidow_pull
	 */
	public function test_set_max_dewidow_pull() {
		$this->settings->set_max_dewidow_pull( 10 );
		$this->assertSame( 10, $this->settings[ Settings::DEWIDOW_MAX_PULL ] );

		$this->settings->set_max_dewidow_pull( 1 );
		$this->assertSame( 5, $this->settings[ Settings::DEWIDOW_MAX_PULL ] );

		$this->settings->set_max_dewidow_pull( 2 );
		$this->assertSame( 2, $this->settings[ Settings::DEWIDOW_MAX_PULL ] );
	}

	/**
	 * Tests set_wrap_hard_hyphens.
	 *
	 * @covers ::set_wrap_hard_hyphens
	 */
	public function test_set_wrap_hard_hyphens() {
		$this->settings->set_wrap_hard_hyphens( true );
		$this->assertTrue( $this->settings[ Settings::HYPHEN_HARD_WRAP ] );

		$this->settings->set_wrap_hard_hyphens( false );
		$this->assertFalse( $this->settings[ Settings::HYPHEN_HARD_WRAP ] );
	}

	/**
	 * Tests set_url_wrap.
	 *
	 * @covers ::set_url_wrap
	 */
	public function test_set_url_wrap() {
		$this->settings->set_url_wrap( true );
		$this->assertTrue( $this->settings[ Settings::URL_WRAP ] );

		$this->settings->set_url_wrap( false );
		$this->assertFalse( $this->settings[ Settings::URL_WRAP ] );
	}

	/**
	 * Tests set_email_wrap.
	 *
	 * @covers ::set_email_wrap
	 */
	public function test_set_email_wrap() {
		$this->settings->set_email_wrap( true );
		$this->assertTrue( $this->settings[ Settings::EMAIL_WRAP ] );

		$this->settings->set_email_wrap( false );
		$this->assertFalse( $this->settings[ Settings::EMAIL_WRAP ] );
	}

	/**
	 * Tests set_min_after_url_wrap.
	 *
	 * @covers ::set_min_after_url_wrap
	 */
	public function test_set_min_after_url_wrap() {
		$this->settings->set_min_after_url_wrap( 10 );
		$this->assertSame( 10, $this->settings[ Settings::URL_MIN_AFTER_WRAP ] );

		$this->settings->set_min_after_url_wrap( 0 );
		$this->assertSame( 5, $this->settings[ Settings::URL_MIN_AFTER_WRAP ] );

		$this->settings->set_min_after_url_wrap( 1 );
		$this->assertSame( 1, $this->settings[ Settings::URL_MIN_AFTER_WRAP ] );
	}

	/**
	 * Tests set_style_ampersands.
	 *
	 * @covers ::set_style_ampersands
	 */
	public function test_set_style_ampersands() {
		$this->settings->set_style_ampersands( true );
		$this->assertTrue( $this->settings[ Settings::STYLE_AMPERSANDS ] );

		$this->settings->set_style_ampersands( false );
		$this->assertFalse( $this->settings[ Settings::STYLE_AMPERSANDS ] );
	}

	/**
	 * Tests set_style_caps.
	 *
	 * @covers ::set_style_caps
	 */
	public function test_set_style_caps() {
		$this->settings->set_style_caps( true );
		$this->assertTrue( $this->settings[ Settings::STYLE_CAPS ] );

		$this->settings->set_style_caps( false );
		$this->assertFalse( $this->settings[ Settings::STYLE_CAPS ] );
	}

	/**
	 * Tests set_style_initial_quotes.
	 *
	 * @covers ::set_style_initial_quotes
	 */
	public function test_set_style_initial_quotes() {
		$this->settings->set_style_initial_quotes( true );
		$this->assertTrue( $this->settings[ Settings::STYLE_INITIAL_QUOTES ] );

		$this->settings->set_style_initial_quotes( false );
		$this->assertFalse( $this->settings[ Settings::STYLE_INITIAL_QUOTES ] );
	}

	/**
	 * Tests set_style_numbers.
	 *
	 * @covers ::set_style_numbers
	 */
	public function test_set_style_numbers() {
		$this->settings->set_style_numbers( true );
		$this->assertTrue( $this->settings[ Settings::STYLE_NUMBERS ] );

		$this->settings->set_style_numbers( false );
		$this->assertFalse( $this->settings[ Settings::STYLE_NUMBERS ] );
	}

	/**
	 * Tests set_style_hanging_punctuation.
	 *
	 * @covers ::set_style_hanging_punctuation
	 */
	public function test_set_style_hanging_punctuation() {
		$this->settings->set_style_hanging_punctuation( true );
		$this->assertTrue( $this->settings[ Settings::STYLE_HANGING_PUNCTUATION ] );

		$this->settings->set_style_hanging_punctuation( false );
		$this->assertFalse( $this->settings[ Settings::STYLE_HANGING_PUNCTUATION ] );
	}

	/**
	 * Tests set_initial_quote_tags.
	 *
	 * @covers ::set_initial_quote_tags
	 */
	public function test_set_initial_quote_tags() {
		$tags_as_array  = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'div' ];
		$tags_as_string = implode( ', ', $tags_as_array );

		$this->settings->set_initial_quote_tags( $tags_as_array );
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $this->settings[ Settings::INITIAL_QUOTE_TAGS ] );
		}

		$this->settings->set_initial_quote_tags( [] );
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayNotHasKey( $tag, $this->settings[ Settings::INITIAL_QUOTE_TAGS ] );
		}

		$this->settings->set_initial_quote_tags( $tags_as_string );
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $this->settings[ Settings::INITIAL_QUOTE_TAGS ] );
		}
	}

	/**
	 * Tests set_hyphenation.
	 *
	 * @covers ::set_hyphenation
	 */
	public function test_set_hyphenation() {
		$this->settings->set_hyphenation( true );
		$this->assertTrue( $this->settings[ Settings::HYPHENATION ] );

		$this->settings->set_hyphenation( false );
		$this->assertFalse( $this->settings[ Settings::HYPHENATION ] );
	}

	/**
	 * Provide data for set_hyphenation_language testing.
	 *
	 * @return array
	 */
	public function provide_hyphenation_language_data() {
		return [
			[ 'en-US',  true ],
			[ 'foobar', false ],
			[ 'no',     true ],
			[ 'de',     true ],
		];
	}

	/**
	 * Tests set_hyphenation_language.
	 *
	 * @covers ::set_hyphenation_language
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_language
	 *
	 * @dataProvider provide_hyphenation_language_data
	 *
	 * @param string $lang    Language code.
	 * @param bool   $success Expected success status.
	 */
	public function test_set_hyphenation_language( $lang, $success ) {
		$s = $this->settings;

		$s['hyphenationExceptions'] = []; // necessary for full coverage.

		$s->set_hyphenation_language( $lang );

		// If the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless.
		if ( $success || ! isset( $s->hyphenator ) ) {
			$this->assertSame( $lang, $s[ Settings::HYPHENATION_LANGUAGE ] );
		} else {
			$this->assertFalse( isset( $s[ Settings::HYPHENATION_LANGUAGE ] ) );
		}
	}

	/**
	 * Tests set_hyphenation_language.
	 *
	 * @covers ::set_hyphenation_language
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_language
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 *
	 * @dataProvider provide_hyphenation_language_data
	 *
	 * @param string $lang    Language code.
	 * @param bool   $success Expected success status.
	 */
	public function test_set_hyphenation_language_again( $lang, $success ) {
		$s = $this->settings;

		$s['hyphenationExceptions'] = []; // necessary for full coverage.

		for ( $i = 0; $i < 2; ++$i ) {
			$s->set_hyphenation_language( $lang );

			// If the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless.
			if ( $success ) {
				$this->assertSame( $lang, $s[ Settings::HYPHENATION_LANGUAGE ], "Round $i, success" );
			} elseif ( ! isset( $s->hyphenator ) ) {
				$this->assertSame( $lang, $s[ Settings::HYPHENATION_LANGUAGE ], "Round $i, no hyphenator" );
				// Clear hyphenation language if there was no hypehnator object.
				unset( $s[ Settings::HYPHENATION_LANGUAGE ] );
			} else {
				$this->assertFalse( isset( $s[ Settings::HYPHENATION_LANGUAGE ] ), "Round $i, unsuccessful" );
			}
		}
	}


	/**
	 * Tests set_min_length_hyphenation.
	 *
	 * @covers ::set_min_length_hyphenation
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_length_hyphenation() {
		$this->settings->set_min_length_hyphenation( 1 ); // too low, resets to default 5.
		$this->assertSame( 5, $this->settings[ Settings::HYPHENATION_MIN_LENGTH ] );

		$this->settings->set_min_length_hyphenation( 2 );
		$this->assertSame( 2, $this->settings[ Settings::HYPHENATION_MIN_LENGTH ] );

		$this->settings->set_min_length_hyphenation( 66 );
		$this->assertSame( 66, $this->settings[ Settings::HYPHENATION_MIN_LENGTH ] );
	}

	/**
	 * Tests set_min_before_hyphenation.
	 *
	 * @covers ::set_min_before_hyphenation
	 */
	public function test_set_min_before_hyphenation() {
		$this->settings->set_min_before_hyphenation( 0 ); // too low, resets to default 3.
		$this->assertSame( 3, $this->settings[ Settings::HYPHENATION_MIN_BEFORE ] );

		$this->settings->set_min_before_hyphenation( 1 );
		$this->assertSame( 1, $this->settings[ Settings::HYPHENATION_MIN_BEFORE ] );

		$this->settings->set_min_before_hyphenation( 66 );
		$this->assertSame( 66, $this->settings[ Settings::HYPHENATION_MIN_BEFORE ] );

	}

	/**
	 * Tests set_min_after_hyphenation.
	 *
	 * @covers ::set_min_after_hyphenation
	 */
	public function test_set_min_after_hyphenation() {
		$this->settings->set_min_after_hyphenation( 0 ); // too low, resets to default 2.
		$this->assertSame( 2, $this->settings[ Settings::HYPHENATION_MIN_AFTER ] );

		$this->settings->set_min_after_hyphenation( 1 );
		$this->assertSame( 1, $this->settings[ Settings::HYPHENATION_MIN_AFTER ] );

		$this->settings->set_min_after_hyphenation( 66 );
		$this->assertSame( 66, $this->settings[ Settings::HYPHENATION_MIN_AFTER ] );
	}

	/**
	 * Tests set_hyphenate_headings.
	 *
	 * @covers ::set_hyphenate_headings
	 */
	public function test_set_hyphenate_headings() {
		$this->settings->set_hyphenate_headings( true );
		$this->assertTrue( $this->settings[ Settings::HYPHENATE_HEADINGS ] );

		$this->settings->set_hyphenate_headings( false );
		$this->assertFalse( $this->settings[ Settings::HYPHENATE_HEADINGS ] );
	}

	/**
	 * Tests set_hyphenate_all_caps.
	 *
	 * @covers ::set_hyphenate_all_caps
	 */
	public function test_set_hyphenate_all_caps() {
		$this->settings->set_hyphenate_all_caps( true );
		$this->assertTrue( $this->settings[ Settings::HYPHENATE_ALL_CAPS ] );

		$this->settings->set_hyphenate_all_caps( false );
		$this->assertFalse( $this->settings[ Settings::HYPHENATE_ALL_CAPS ] );
	}

	/**
	 * Tests set_hyphenate_title_case.
	 *
	 * @covers ::set_hyphenate_title_case
	 */
	public function test_set_hyphenate_title_case() {
		$this->settings->set_hyphenate_title_case( true );
		$this->assertTrue( $this->settings[ Settings::HYPHENATE_TITLE_CASE ] );

		$this->settings->set_hyphenate_title_case( false );
		$this->assertFalse( $this->settings[ Settings::HYPHENATE_TITLE_CASE ] );
	}

	/**
	 * Tests set_hyphenate_compounds.
	 *
	 * @covers ::set_hyphenate_compounds
	 */
	public function test_set_hyphenate_compounds() {
		$this->settings->set_hyphenate_compounds( true );
		$this->assertTrue( $this->settings[ Settings::HYPHENATE_COMPOUNDS ] );

		$this->settings->set_hyphenate_compounds( false );
		$this->assertFalse( $this->settings[ Settings::HYPHENATE_COMPOUNDS ] );
	}

	/**
	 * Tests set_hyphenation_exceptions.
	 *
	 * @covers ::set_hyphenation_exceptions
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_hyphenation_exceptions_array() {
		$s = $this->settings;

		$exceptions = [ 'Hu-go', 'Fö-ba-ß' ];
		$s->set_hyphenation_exceptions( $exceptions );
		$this->assertContainsOnly( 'string', $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );
		$this->assertCount( 2, $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );

		$exceptions = [ 'bar-foo' ];
		$s->set_hyphenation_exceptions( $exceptions );
		$this->assertContainsOnly( 'string', $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );
		$this->assertCount( 1, $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );
	}

	/**
	 * Tests set_hyphenation_exceptions.
	 *
	 * @covers ::set_hyphenation_exceptions
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_set_hyphenation_exceptions_string() {
		$s          = $this->settings;
		$exceptions = 'Hu-go, Fö-ba-ß';

		$s->set_hyphenation_exceptions( $exceptions );
		$this->assertContainsOnly( 'string', $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );
		$this->assertCount( 2, $s[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ] );
	}

	/**
	 * Tests get_hash.
	 *
	 * @covers ::get_hash
	 * @covers ::jsonSerialize
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 * @uses PHP_Typography\Strings::maybe_split_parameters
	 */
	public function test_get_hash() {
		$s = $this->settings;

		// Finish initialization.
		$s->set_smart_quotes_primary();
		$s->set_smart_quotes_secondary();
		$s->set_smart_dashes_style();

		$s->set_smart_quotes( true );
		$hash1 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash1 ) );

		$s->set_smart_quotes( false );
		$hash2 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash2 ) );

		$s->set_smart_quotes_primary( \PHP_Typography\Settings\Quote_Style::SINGLE_CURLED );
		$hash3 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash3 ) );

		$s->set_smart_quotes_secondary( $this->createMock( Quotes::class ) );
		$hash4 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash4 ) );

		$s->set_smart_dashes_style( $this->createMock( Dashes::class ) );
		$hash5 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash5 ) );

		$s->remap_character( U::NO_BREAK_NARROW_SPACE, U::NO_BREAK_SPACE );
		$hash6 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash6 ) );

		$s->set_units( [ 'foo', 'bar' ] );
		$hash7 = $s->get_hash( 10 );
		$this->assertEquals( 10, strlen( $hash7 ) );

		$this->assertNotEquals( $hash1, $hash2, 'Hashes should not be equal.' );
		$this->assertNotEquals( $hash2, $hash3, 'Hashes after set_smart_quotes_primary are still equal.' );
		$this->assertNotEquals( $hash3, $hash4, 'Hashes after set_smart_quotes_secondary are still equal.' );
		$this->assertNotEquals( $hash4, $hash5, 'Hashes after set_smart_dashes_style are still equal.' );
		$this->assertNotEquals( $hash5, $hash6, 'Hashes after remapping no-break narrow space are still equal.' );
		$this->assertNotEquals( $hash6, $hash7, 'Hashes after set_units are still equal.' );
	}

	/**
	 * Tests apply_character_mapping.
	 *
	 * @covers ::remap_character
	 */
	public function test_remap_character() {
		$mapping = [
			'a' => 'A',
			'r' => 'z',
		];

		$s = new Settings( false, $mapping );
		$this->assertAttributeSame( $mapping, 'unicode_mapping', $s );

		$s->remap_character( 'a', 'a' );
		$this->assertAttributeSame( [ 'r' => 'z' ], 'unicode_mapping', $s );

		$s->remap_character( U::NO_BREAK_NARROW_SPACE, 'x' );
		$this->assertAttributeCount( 2, 'unicode_mapping', $s );
		$this->assertAttributeContains( 'x', 'unicode_mapping', $s );
	}


	/**
	 * Provides data for testing apply_character_mapping.
	 *
	 * @return array
	 */
	public function provide_apply_character_mapping_data() {
		return [
			[ 'foobar', 'foobAz' ],
			[ [ 'foobar' ], [ 'foobAz' ] ],
			[ [ 'foobar', 'fugazi' ], [ 'foobAz', 'fugAzi' ] ],
			[ '', '' ],
		];
	}

	/**
	 * Tests apply_character_mapping.
	 *
	 * @covers ::apply_character_mapping
	 *
	 * @dataProvider provide_apply_character_mapping_data
	 *
	 * @param  string|string[] $input  The input.
	 * @param  string|string[] $result The expected result.
	 */
	public function test_apply_character_mapping( $input, $result ) {
		$mapping = [
			'a' => 'A',
			'r' => 'z',
		];

		$s = new Settings( false, $mapping );

		$this->assertSame( $result, $s->apply_character_mapping( $input ) );
	}

	/**
	 * Provide data for testing array_map_assoc.
	 *
	 * @return array
	 */
	public function provide_array_map_assoc_data() {
		return [
			[
				function( $key, $value ) {
						return [ $value => $value * 2 ];
				},
				[ 1, 2, 3 ],
				[
					1 => 2,
					2 => 4,
					3 => 6,
				],
			],
			[
				function( $key, $value ) {
						return [];
				},
				[ 1, 2, 3 ],
				[],
			],
		];
	}

	/**
	 * Test array_map_assoc.
	 *
	 * @covers ::array_map_assoc
	 * @dataProvider provide_array_map_assoc_data
	 *
	 * @param  callable $callable The function to apply to the array.
	 * @param  array    $array    Input array.
	 * @param  array    $result   Expected output array.
	 */
	public function test_array_map_assoc( callable $callable, array $array, array $result ) {
		$this->assertSame( $result, $this->invokeStaticMethod( Settings::class, 'array_map_assoc', [ $callable, $array ] ) );
	}
}
