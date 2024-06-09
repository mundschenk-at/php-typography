<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2024 Peter Putzer.
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

use BadMethodCallException;
use PHP_Typography\Settings;
use PHP_Typography\U;

use PHP_Typography\Settings\Dashes;
use PHP_Typography\Settings\Quotes;

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
 * @uses PHP_Typography\DOM::inappropriate_tags
 */
class Settings_Test extends Testcase {
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
	protected function set_up() {
		parent::set_up();

		$this->settings = new \PHP_Typography\Settings( false, [] );
	}

	/**
	 * Tests set_defaults.
	 *
	 * @covers ::set_defaults
	 *
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 * @uses PHP_Typography\DOM::inappropriate_tags
	 */
	public function test_set_defaults() {
		$second_settings = new \PHP_Typography\Settings( false );
		$this->assert_attribute_count( 1, 'data', $second_settings );
		$second_settings->set_defaults();
		$this->assert_attribute_count( 54, 'data', $second_settings );
	}

	/**
	 * Tests initialization.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::set_defaults
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 * @uses PHP_Typography\DOM::inappropriate_tags
	 */
	public function test_initialization() {
		$s = $this->settings;

		// No defaults.
		$this->assert_attribute_count( 1, 'data', $s );

		// After set_defaults().
		$s->set_defaults();
		$this->assert_attribute_not_count( 1, 'data', $s );

		$second_settings = new \PHP_Typography\Settings( true );
		$this->assert_attribute_count( 54, 'data', $second_settings );
	}

	/**
	 * Tests __call with invalid method name.
	 *
	 * @covers ::__call
	 */
	public function test___call() {
		$s = $this->settings;

		$this->expect_exception( BadMethodCallException::class );
		$this->expect_exception_message_matches( '/^Invalid method .* called\.$/' );
		$s->foobar();
	}

	/**
	 * Tests __get.
	 *
	 * @covers ::__get
	 */
	public function test___get() {
		$s = $this->settings;

		$s->new_key = 42;
		$this->assertEquals( 42, $s->new_key ); // @phpstan-ignore-line
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
		$s->new_key = 42; // @phpstan-ignore-line
		$this->assertTrue( isset( $s->new_key ) );
	}

	/**
	 * Tests __isset.
	 *
	 * @covers ::__isset
	 *
	 * @uses ::__call
	 */
	public function test___isset() {
		$s = $this->settings;

		// Random key.
		$this->assertFalse( isset( $s->new_key ) );
		$s->new_key = 42; // @phpstan-ignore-line
		$this->assertTrue( isset( $s->new_key ) );

		// Virtual property.
		$this->assertFalse( isset( $s->classes_to_ignore ) );
		$s->set_classes_to_ignore( [ 'foo' ] );
		$this->assertTrue( isset( $s->classes_to_ignore ) );
	}

	/**
	 * Tests __unset.
	 *
	 * @covers ::__unset
	 */
	public function test___unset() {
		$s = $this->settings;

		$s->new_key = 42; // @phpstan-ignore-line
		$this->assertTrue( isset( $s->new_key ) );

		unset( $s->new_key );
		$this->assertFalse( isset( $s->new_key ) );
	}

	/**
	 * Tests set_ignore_parser_errors.
	 *
	 * @uses ::__call
	 */
	public function test_set_ignore_parser_errors() {
		$s = $this->settings;

		$s->set_ignore_parser_errors( true );
		$this->assertTrue( $s->ignore_parser_errors );

		$s->set_ignore_parser_errors( false );
		$this->assertFalse( $s->ignore_parser_errors );
	}

	/**
	 * Tests set_parser_errors_handler.
	 *
	 * @uses ::__call
	 */
	public function test_set_parser_errors_handler() {
		$s = $this->settings;

		// Default: no handler.
		$this->assertEmpty( $s->parser_errors_handler );

		// Valid handler.
		$s->set_parser_errors_handler(
			function ( $errors ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- error handler signature.
				return [];
			}
		);
		$this->assert_is_callable( $s->parser_errors_handler );
		$old_handler = $s->parser_errors_handler;
	}

	/**
	 * Tests set_parser_errors_handler with an invalid callback.
	 *
	 * @uses ::__call
	 */
	public function test_set_parser_errors_handler_invalid() {
		$s = $this->settings;

		// Default: no handler.
		$this->assertEmpty( $s->parser_errors_handler );

		// Valid handler.
		$s->set_parser_errors_handler(
			function ( $errors ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- error handler signature.
				return [];
			}
		);
		$this->assert_is_callable( $s->parser_errors_handler );
		$old_handler = $s->parser_errors_handler;

		$this->expect_exception( \TypeError::class );

		// Invalid handler, previous handler not changed.
		$s->set_parser_errors_handler( 'foobar' );
		$this->assert_is_callable( $s->parser_errors_handler );
		$this->assertSame( $old_handler, $s->parser_errors_handler );
	}

	/**
	 * Tests set_tags_to_ignore.
	 *
	 * @covers ::set_tags_to_ignore
	 */
	public function test_set_tags_to_ignore() {
		$s              = $this->settings;
		$always_ignore  = [ 'iframe', 'textarea', 'button', 'select', 'optgroup', 'option', 'map', 'style', 'head', 'title', 'script', 'applet', 'object', 'param', 'svg', 'math' ];
		$tags_to_ignore = [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ];

		// Default tags.
		$s->set_tags_to_ignore( $tags_to_ignore );
		foreach ( $tags_to_ignore as $tag ) {
			$this->assertContains( $tag, $s->tags_to_ignore );
		}
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s->tags_to_ignore );
		}

		// Auto-close tag and something else.
		$s->set_tags_to_ignore( [ 'img', 'foo' ] );
		$this->assertContains( 'foo', $s->tags_to_ignore );

		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s->tags_to_ignore );
		}

		$s->set_tags_to_ignore( [ 'img', 'foo', ' ' ] ); // should not result in an error.
	}

	/**
	 * Tests set_classes_to_ignore.
	 *
	 * @uses ::__call
	 */
	public function test_set_classes_to_ignore() {
		$s = $this->settings;

		$s->set_classes_to_ignore( [ 'foo', 'bar' ] );
		$this->assertContains( 'foo', $s->classes_to_ignore );
		$this->assertContains( 'bar', $s->classes_to_ignore );
	}

	/**
	 * Tests set_ids_to_ignore.
	 *
	 * @uses ::__call
	 */
	public function test_set_ids_to_ignore() {
		$s = $this->settings;

		$s->set_ids_to_ignore( [ 'foobar', 'barfoo' ] );
		$this->assertContains( 'foobar', $s->ids_to_ignore );
		$this->assertContains( 'barfoo', $s->ids_to_ignore );
	}

	/**
	 * Tests set_smart_quotes.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_quotes() {
		$this->settings->set_smart_quotes( true );
		$this->assertTrue( $this->settings->smart_quotes );

		$this->settings->set_smart_quotes( false );
		$this->assertFalse( $this->settings->smart_quotes );
	}

	/**
	 * Tests set_smart_quotes_primary.
	 *
	 * @covers ::set_smart_quotes_primary
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

			$this->assert_smart_quotes_style( $style, $s->primary_quote_style->open(), $s->primary_quote_style->close() );
		}
	}

	/**
	 * Tests set_smart_quotes_primary with an invalid input.
	 *
	 * @covers ::set_smart_quotes_primary
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 */
	public function test_set_smart_quotes_primary_invalid() {
		$s = $this->settings;

		$this->expect_exception( \DomainException::class );
		$this->expect_exception_message_matches( '/^Invalid quote style \w+\.$/' );

		$s->set_smart_quotes_primary( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_quotes_primary with a Quotes object.
	 *
	 * @covers ::set_smart_quotes_primary
	 */
	public function test_set_smart_quotes_primary_to_object() {
		$s = $this->settings;

		// Create a stub for the Token_Fixer interface.
		$fake_quotes = $this->createMock( Quotes::class );
		$fake_quotes->method( 'open' )->willReturn( 'x' );
		$fake_quotes->method( 'close' )->willReturn( 'y' );

		$s->set_smart_quotes_primary( $fake_quotes );

		$this->assertSame( 'x', $s->primary_quote_style->open() );
		$this->assertSame( 'y', $s->primary_quote_style->close() );
	}

	/**
	 * Tests set_smart_quotes_secondary.
	 *
	 * @covers ::set_smart_quotes_secondary
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

			$this->assert_smart_quotes_style( $style, $s->secondary_quote_style->open(), $s->secondary_quote_style->close() );
		}
	}

	/**
	 * Tests set_smart_quotes_secondary with an invalid input.
	 *
	 * @covers ::set_smart_quotes_secondary
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 */
	public function test_set_smart_quotes_secondary_invalid() {
		$s = $this->settings;

		$this->expect_exception( \DomainException::class );
		$this->expect_exception_message_matches( '/^Invalid quote style \w+\.$/' );

		$s->set_smart_quotes_secondary( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_quotes_secondary with a Quotes object.
	 *
	 * @covers ::set_smart_quotes_secondary
	 */
	public function test_set_smart_quotes_secondary_to_object() {
		$s = $this->settings;

		// Create a stub for the Token_Fixer interface.
		$fake_quotes = $this->createMock( Quotes::class );
		$fake_quotes->method( 'open' )->willReturn( 'xx' );
		$fake_quotes->method( 'close' )->willReturn( 'yy' );

		$s->set_smart_quotes_secondary( $fake_quotes );

		$this->assertSame( 'xx', $s->secondary_quote_style->open() );
		$this->assertSame( 'yy', $s->secondary_quote_style->close() );
	}

	/**
	 * Tests set_smart_quotes_exceptions.
	 *
	 * @covers ::set_smart_quotes_exceptions
	 */
	public function test_set_smart_quotes_exceptions() {
		$this->settings->set_smart_quotes_exceptions();

		$exceptions = $this->settings->smart_quotes_exceptions;
		$this->assertCount( 2, $exceptions );
		$this->assertGreaterThan( 1, count( $exceptions['patterns'] ) );
		$this->assertEquals( count( $exceptions['patterns'] ), count( $exceptions['replacements'] ) );

		$this->settings->set_smart_quotes_exceptions( [ 'Yfoo' => 'Xfoo' ] );
		$exceptions = $this->settings->smart_quotes_exceptions;
		$this->assertCount( 2, $exceptions );
		$this->assertEquals( [ 'Yfoo' ], $exceptions['patterns'] );
		$this->assertEquals( [ 'Xfoo' ], $exceptions['replacements'] );
	}

	/**
	 * Test set_smart_dashes.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_dashes() {
		$this->settings->set_smart_dashes( true );
		$this->assertTrue( $this->settings->smart_dashes );

		$this->settings->set_smart_dashes( false );
		$this->assertFalse( $this->settings->smart_dashes );
	}

	/**
	 * Test set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 *
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 */
	public function test_set_smart_dashes_style() {
		$s = $this->settings;

		$s->set_smart_dashes_style( 'traditionalUS' );
		$dashes = $s->dash_style;

		$this->assertSame( U::EM_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( U::THIN_SPACE, $dashes->parenthetical_space() );
		$this->assertSame( U::THIN_SPACE, $dashes->interval_space() );

		$s->set_smart_dashes_style( 'international' );
		$dashes = $s->dash_style;

		$this->assertSame( U::EN_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( ' ', $dashes->parenthetical_space() );
		$this->assertSame( U::HAIR_SPACE, $dashes->interval_space() );

		$s->set_smart_dashes_style( 'internationalNoHairSpaces' );
		$dashes = $s->dash_style;

		$this->assertSame( U::EN_DASH, $dashes->parenthetical_dash() );
		$this->assertSame( U::EN_DASH, $dashes->interval_dash() );
		$this->assertSame( ' ', $dashes->parenthetical_space() );
		$this->assertSame( '', $dashes->interval_space() );
	}

	/**
	 * Test set_smart_dashes_style with a Dashes object.
	 *
	 * @covers ::set_smart_dashes_style
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
		$dashes = $s->dash_style;

		$this->assertSame( 'a', $dashes->parenthetical_dash() );
		$this->assertSame( 'b', $dashes->parenthetical_space() );
		$this->assertSame( 'c', $dashes->interval_dash() );
		$this->assertSame( 'd', $dashes->interval_space() );
	}

	/**
	 * Tests set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 *
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 */
	public function test_set_smart_dashes_style_invalid() {
		$s = $this->settings;

		$this->expect_exception( \DomainException::class );
		$this->expect_exception_message_matches( '/^Invalid dash style \w+\.$/' );

		$s->set_smart_dashes_style( 'invalidStyleName' );
	}

	/**
	 * Tests set_smart_ellipses.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_ellipses() {
		$this->settings->set_smart_ellipses( true );
		$this->assertTrue( $this->settings->smart_ellipses );

		$this->settings->set_smart_ellipses( false );
		$this->assertFalse( $this->settings->smart_ellipses );
	}

	/**
	 * Tests set_smart_diacritics.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_diacritics() {
		$this->settings->set_smart_diacritics( true );
		$this->assertTrue( $this->settings->smart_diacritics );

		$this->settings->set_smart_diacritics( false );
		$this->assertFalse( $this->settings->smart_diacritics );
	}

	/**
	 * Tests set_diacritic_language.
	 *
	 * @covers ::set_diacritic_language
	 *
	 * @uses ::update_diacritics_replacement_arrays
	 */
	public function test_set_diacritic_language() {
		$this->settings->set_diacritic_language( 'en-US' );
		$this->assertGreaterThan( 0, count( $this->settings->diacritic_words ) );

		$this->settings->set_diacritic_language( 'foobar' );
		$this->assertFalse( isset( $this->settings->diacritic_words ) );

		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings->diacritic_words ) );
		$this->assertGreaterThan( 0, count( $this->settings->diacritic_words ) );

		// Nothing changed since the last call.
		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings->diacritic_words ) );
		$this->assertGreaterThan( 0, count( $this->settings->diacritic_words ) );
	}

	/**
	 * Provide data for testing set_diacritic_custom_replacements.
	 */
	public function provide_set_diacritic_custom_replacements_data() {
		return [
			[
				[
					'foo' => 'fóò',
					'bar' => 'bâr',
					'ha'  => 'hä',
				],
				[ 'foo', 'bar', 'ha' ],
				[ 'fóò', 'bâr', 'hä' ],
			],
			[
				[
					"fo'o" => "fó'ò",
					'bar'  => 'bâr',
					'h"a'  => 'h"ä',
				],
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
		];
	}

	/**
	 * Tests set_diacritic_custom_replacements.
	 *
	 * @covers ::set_diacritic_custom_replacements
	 *
	 * @uses ::update_diacritics_replacement_arrays
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
			$this->assertArrayHasKey( $key, $s->diacritic_custom_replacements );
		}

		foreach ( $values as $value ) {
			$this->assertContains( $value, $s->diacritic_custom_replacements );
		}

		$this->assertCount( count( $keys ), $s->diacritic_custom_replacements );
		$this->assertCount( count( $values ), $s->diacritic_custom_replacements );
	}

	/**
	 * Tests update_diacritics_replacement_arrays.
	 *
	 * @covers ::update_diacritics_replacement_arrays
	 *
	 * @uses ::set_diacritic_custom_replacements
	 */
	public function test_update_diacritics_replacement_arrays(): void {
		$s                 = $this->settings;
		$custom_diacritics = [
			'foobar' => 'foobar',
		];

		// Fake standard diacritics.
		$settings_data                              = $this->get_value( $s, 'data' );
		$settings_data[ Settings::DIACRITIC_WORDS ] = [
			'foobar' => 'fööbär',
			'barfoo' => 'bärföö',
		];
		$this->set_value( $s, 'data', $settings_data );

		$s->set_diacritic_custom_replacements( $custom_diacritics );

		$this->assertSame( 'foobar', $s->diacritic_combined['replacements']['foobar'] );
		$this->assertSame( 'bärföö', $s->diacritic_combined['replacements']['barfoo'] );
	}

	/**
	 * Test set_smart_marks.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_marks() {
		$this->settings->set_smart_marks( true );
		$this->assertTrue( $this->settings->smart_marks );

		$this->settings->set_smart_marks( false );
		$this->assertFalse( $this->settings->smart_marks );
	}

	/**
	 * Test set_smart_area_units.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_area_units() {
		$this->settings->set_smart_area_units( true );
		$this->assertTrue( $this->settings->smart_area_units );

		$this->settings->set_smart_area_units( false );
		$this->assertFalse( $this->settings->smart_area_units );
	}

	/**
	 * Tests set_smart_math.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_math() {
		$this->settings->set_smart_math( true );
		$this->assertTrue( $this->settings->smart_math );

		$this->settings->set_smart_math( false );
		$this->assertFalse( $this->settings->smart_math );
	}

	/**
	 * Tests set_smart_exponents.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_exponents() {
		$this->settings->set_smart_exponents( true );
		$this->assertTrue( $this->settings->smart_exponents );

		$this->settings->set_smart_exponents( false );
		$this->assertFalse( $this->settings->smart_exponents );
	}

	/**
	 * Tests set_smart_fractions.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_fractions() {
		$this->settings->set_smart_fractions( true );
		$this->assertTrue( $this->settings->smart_fractions );

		$this->settings->set_smart_fractions( false );
		$this->assertFalse( $this->settings->smart_fractions );
	}

	/**
	 * Tests set_smart_ordinal_suffix.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_ordinal_suffix() {
		$this->settings->set_smart_ordinal_suffix( true );
		$this->assertTrue( $this->settings->smart_ordinal_suffix );

		$this->settings->set_smart_ordinal_suffix( false );
		$this->assertFalse( $this->settings->smart_ordinal_suffix );
	}

	/**
	 * Tests set_smart_ordinal_suffix_match_roman_numerals.
	 *
	 * @uses ::__call
	 */
	public function test_set_smart_ordinal_suffix_match_roman_numerals() {
		$this->settings->set_smart_ordinal_suffix_match_roman_numerals( true );
		$this->assertTrue( $this->settings->smart_ordinal_suffix_match_roman_numerals );

		$this->settings->set_smart_ordinal_suffix_match_roman_numerals( false );
		$this->assertFalse( $this->settings->smart_ordinal_suffix_match_roman_numerals );
	}

	/**
	 * Tests set_single_character_word_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_single_character_word_spacing() {
		$this->settings->set_single_character_word_spacing( true );
		$this->assertTrue( $this->settings->single_character_word_spacing );

		$this->settings->set_single_character_word_spacing( false );
		$this->assertFalse( $this->settings->single_character_word_spacing );
	}

	/**
	 * Tests set_fraction_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_fraction_spacing() {
		$this->settings->set_fraction_spacing( true );
		$this->assertTrue( $this->settings->fraction_spacing );

		$this->settings->set_fraction_spacing( false );
		$this->assertFalse( $this->settings->fraction_spacing );
	}

	/**
	 * Tests set_unit_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_unit_spacing() {
		$this->settings->set_unit_spacing( true );
		$this->assertTrue( $this->settings->unit_spacing );

		$this->settings->set_unit_spacing( false );
		$this->assertFalse( $this->settings->unit_spacing );
	}

	/**
	 * Tests set_numbered_abbreviation_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_numbered_abbreviation_spacing() {
		$this->settings->set_numbered_abbreviation_spacing( true );
		$this->assertTrue( $this->settings->numbered_abbreviation_spacing );

		$this->settings->set_numbered_abbreviation_spacing( false );
		$this->assertFalse( $this->settings->numbered_abbreviation_spacing );
	}

	/**
	 * Tests set_french_punctuation_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_french_punctuation_spacing() {
		$this->settings->set_french_punctuation_spacing( true );
		$this->assertTrue( $this->settings->french_punctuation_spacing );

		$this->settings->set_french_punctuation_spacing( false );
		$this->assertFalse( $this->settings->french_punctuation_spacing );
	}

	/**
	 * Provides data for testing update_unit_pattern.
	 *
	 * @return array
	 */
	public function provide_set_unit_data() {
		return [
			[ [ 'foo', 'bar', 'xx/yy' ], 'foo|bar|xx\/yy|' ],
			[ [ 'km/h', 'T$' ], 'km\/h|T\$|' ],
			[ [ '¥', 'm[a]', 'n.', 'm^2' ], '¥|m\[a\]|n\.|m\^2|' ],
			[ [], '' ],
		];
	}

	/**
	 * Tests update_unit_pattern.
	 *
	 * @covers ::set_units
	 *
	 * @dataProvider provide_set_unit_data
	 *
	 * @param  string[] $units An array of units.
	 * @param  string   $regex The resulting regular expression.
	 */
	public function test_set_units( array $units, string $regex ): void {
		$this->settings->set_units( $units );
		$this->assertSame( $regex, $this->settings->custom_units );
	}

	/**
	 * Tests set_dash_spacing.
	 *
	 * @uses ::__call
	 */
	public function test_set_dash_spacing() {
		$this->settings->set_dash_spacing( true );
		$this->assertTrue( $this->settings->dash_spacing );

		$this->settings->set_dash_spacing( false );
		$this->assertFalse( $this->settings->dash_spacing );
	}

	/**
	 * Tests set_space_collapse.
	 *
	 * @uses ::__call
	 */
	public function test_set_space_collapse() {
		$this->settings->set_space_collapse( true );
		$this->assertTrue( $this->settings->space_collapse );

		$this->settings->set_space_collapse( false );
		$this->assertFalse( $this->settings->space_collapse );
	}

	/**
	 * Tests set_dewidow.
	 *
	 * @uses ::__call
	 */
	public function test_set_dewidow() {
		$this->settings->set_dewidow( true );
		$this->assertTrue( $this->settings->dewidow );

		$this->settings->set_dewidow( false );
		$this->assertFalse( $this->settings->dewidow );
	}

	/**
	 * Tests set_max_dewidow_length.
	 *
	 * @uses ::__call
	 */
	public function test_set_max_dewidow_length() {
		$this->settings->set_max_dewidow_length( 10 );
		$this->assertSame( 10, $this->settings->max_dewidow_length );

		$this->settings->set_max_dewidow_length( 2 );
		$this->assertSame( 2, $this->settings->max_dewidow_length );
	}

	/**
	 * Tests set_max_dewidow_length.
	 *
	 * @uses ::__call
	 */
	public function test_set_max_dewidow_length_too_low() {
		$this->settings->set_max_dewidow_length( 10 );
		$this->assertSame( 10, $this->settings->max_dewidow_length );

		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_max_dewidow_length( 1 );
		$this->assertSame( 10, $this->settings->max_dewidow_length );
	}

	/**
	 * Tests set_dewidow_word_number.
	 *
	 * @uses ::__call
	 */
	public function test_set_dewidow_word_number() {
		$this->settings->set_dewidow_word_number( 1 );
		$this->assertSame( 1, $this->settings->dewidow_word_number );

		$this->settings->set_dewidow_word_number( 2 );
		$this->assertSame( 2, $this->settings->dewidow_word_number );

		$this->settings->set_dewidow_word_number( 3 );
		$this->assertSame( 3, $this->settings->dewidow_word_number );
	}

	/**
	 * Tests set_dewidow_word_number.
	 *
	 * @uses ::__call
	 */
	public function test_set_dewidow_word_number_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_dewidow_word_number( 0 );
		$this->assertSame( 1, $this->settings->dewidow_word_number );
	}

	/**
	 * Tests set_dewidow_word_number.
	 *
	 * @uses ::__call
	 */
	public function test_set_dewidow_word_number_too_high() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_dewidow_word_number( 4 );
		$this->assertSame( 1, $this->settings->dewidow_word_number );
	}

	/**
	 * Tests set_max_dewidow_pull.
	 *
	 * @uses ::__call
	 */
	public function test_set_max_dewidow_pull() {
		$this->settings->set_max_dewidow_pull( 10 );
		$this->assertSame( 10, $this->settings->max_dewidow_pull );

		$this->settings->set_max_dewidow_pull( 2 );
		$this->assertSame( 2, $this->settings->max_dewidow_pull );
	}

	/**
	 * Tests set_max_dewidow_pull.
	 *
	 * @uses ::__call
	 */
	public function test_set_max_dewidow_pull_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_max_dewidow_pull( 1 );
		$this->assertSame( 5, $this->settings->max_dewidow_pull );
	}


	/**
	 * Tests set_wrap_hard_hyphens.
	 *
	 * @uses ::__call
	 */
	public function test_set_wrap_hard_hyphens() {
		$this->settings->set_wrap_hard_hyphens( true );
		$this->assertTrue( $this->settings->wrap_hard_hyphens );

		$this->settings->set_wrap_hard_hyphens( false );
		$this->assertFalse( $this->settings->wrap_hard_hyphens );
	}

	/**
	 * Tests set_wrap_urls.
	 *
	 * @uses ::__call
	 */
	public function test_set_wrap_urls() {
		$this->settings->set_wrap_urls( true );
		$this->assertTrue( $this->settings->wrap_urls );

		$this->settings->set_wrap_urls( false );
		$this->assertFalse( $this->settings->wrap_urls );
	}

	/**
	 * Tests set_wrap_emails.
	 *
	 * @uses ::__call
	 */
	public function test_set_wrap_emails() {
		$this->settings->set_wrap_emails( true );
		$this->assertTrue( $this->settings->wrap_emails );

		$this->settings->set_wrap_emails( false );
		$this->assertFalse( $this->settings->wrap_emails );
	}

	/**
	 * Tests set_min_after_url_wrap.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_after_url_wrap() {
		$this->settings->set_min_after_url_wrap( 10 );
		$this->assertSame( 10, $this->settings->min_after_url_wrap );

		$this->settings->set_min_after_url_wrap( 1 );
		$this->assertSame( 1, $this->settings->min_after_url_wrap );
	}

	/**
	 * Tests set_min_after_url_wrap.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_after_url_wrap_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_min_after_url_wrap( 0 );
		$this->assertSame( 5, $this->settings->min_after_url_wrap );
	}

	/**
	 * Tests set_style_ampersands.
	 *
	 * @uses ::__call
	 */
	public function test_set_style_ampersands() {
		$this->settings->set_style_ampersands( true );
		$this->assertTrue( $this->settings->style_ampersands );

		$this->settings->set_style_ampersands( false );
		$this->assertFalse( $this->settings->style_ampersands );
	}

	/**
	 * Tests set_style_caps.
	 *
	 * @uses ::__call
	 */
	public function test_set_style_caps() {
		$this->settings->set_style_caps( true );
		$this->assertTrue( $this->settings->style_caps );

		$this->settings->set_style_caps( false );
		$this->assertFalse( $this->settings->style_caps );
	}

	/**
	 * Tests set_style_initial_quotes.
	 *
	 * @uses ::__call
	 */
	public function test_set_style_initial_quotes() {
		$this->settings->set_style_initial_quotes( true );
		$this->assertTrue( $this->settings->style_initial_quotes );

		$this->settings->set_style_initial_quotes( false );
		$this->assertFalse( $this->settings->style_initial_quotes );
	}

	/**
	 * Tests set_style_numbers.
	 *
	 * @uses ::__call
	 */
	public function test_set_style_numbers() {
		$this->settings->set_style_numbers( true );
		$this->assertTrue( $this->settings->style_numbers );

		$this->settings->set_style_numbers( false );
		$this->assertFalse( $this->settings->style_numbers );
	}

	/**
	 * Tests set_style_hanging_punctuation.
	 *
	 * @uses ::__call
	 */
	public function test_set_style_hanging_punctuation() {
		$this->settings->set_style_hanging_punctuation( true );
		$this->assertTrue( $this->settings->style_hanging_punctuation );

		$this->settings->set_style_hanging_punctuation( false );
		$this->assertFalse( $this->settings->style_hanging_punctuation );
	}

	/**
	 * Tests set_initial_quote_tags.
	 *
	 * @covers ::set_initial_quote_tags
	 */
	public function test_set_initial_quote_tags() {
		$tags_as_array = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'div' ];

		$this->settings->set_initial_quote_tags( $tags_as_array );
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $this->settings->initial_quote_tags );
		}

		$this->settings->set_initial_quote_tags( [] );
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayNotHasKey( $tag, $this->settings->initial_quote_tags );
		}
	}

	/**
	 * Tests set_hyphenation.
	 *
	 * @uses ::__call
	 */
	public function test_set_hyphenation() {
		$this->settings->set_hyphenation( true );
		$this->assertTrue( $this->settings->hyphenation );

		$this->settings->set_hyphenation( false );
		$this->assertFalse( $this->settings->hyphenation );
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
	 * @uses ::__call
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

		$s->set_hyphenation_language( $lang );

		// If the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless.
		if ( $success || ! isset( $s->hyphenator ) ) {
			$this->assertSame( $lang, $s->hyphenation_language );
		} else {
			$this->assertFalse( isset( $s->hyphenation_language ) );
		}
	}

	/**
	 * Tests set_hyphenation_language.
	 *
	 * @uses ::__call
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

		for ( $i = 0; $i < 2; ++$i ) {
			$s->set_hyphenation_language( $lang );

			// If the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless.
			if ( $success ) {
				$this->assertSame( $lang, $s->hyphenation_language, "Round $i, success" );
			} elseif ( ! isset( $s->hyphenator ) ) {
				$this->assertSame( $lang, $s->hyphenation_language, "Round $i, no hyphenator" );
				// Clear hyphenation language if there was no hypehnator object.
				unset( $s->hyphenation_language );
			} else {
				$this->assertFalse( isset( $s->hyphenation_language ), "Round $i, unsuccessful" );
			}
		}
	}


	/**
	 * Tests set_min_length_hyphenation.
	 *
	 * @uses ::__call
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_length_hyphenation() {
		$this->settings->set_min_length_hyphenation( 2 );
		$this->assertSame( 2, $this->settings->min_length_hyphenation );

		$this->settings->set_min_length_hyphenation( 66 );
		$this->assertSame( 66, $this->settings->min_length_hyphenation );
	}

	/**
	 * Tests set_min_length_hyphenation.
	 *
	 * @uses ::__call
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_length_hyphenation_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_min_length_hyphenation( 1 );
		$this->assertSame( 5, $this->settings->min_length_hyphenation );
	}

	/**
	 * Tests set_min_before_hyphenation.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_before_hyphenation() {
		$this->settings->set_min_before_hyphenation( 1 );
		$this->assertSame( 1, $this->settings->min_before_hyphenation );

		$this->settings->set_min_before_hyphenation( 66 );
		$this->assertSame( 66, $this->settings->min_before_hyphenation );
	}

	/**
	 * Tests set_min_before_hyphenation.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_before_hyphenation_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_min_before_hyphenation( 0 ); // too low, resets to default 3.
		$this->assertSame( 3, $this->settings->min_before_hyphenation );
	}

	/**
	 * Tests set_min_after_hyphenation.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_after_hyphenation() {
		$this->settings->set_min_after_hyphenation( 1 );
		$this->assertSame( 1, $this->settings->min_after_hyphenation );

		$this->settings->set_min_after_hyphenation( 66 );
		$this->assertSame( 66, $this->settings->min_after_hyphenation );
	}

	/**
	 * Tests set_min_after_hyphenation.
	 *
	 * @uses ::__call
	 */
	public function test_set_min_after_hyphenation_too_low() {
		$this->expect_exception( \OutOfRangeException::class );

		$this->settings->set_min_after_hyphenation( 0 ); // too low, resets to default 2.
		$this->assertSame( 2, $this->settings->min_after_hyphenation );
	}

	/**
	 * Tests set_hyphenate_headings.
	 *
	 * @uses ::__call
	 */
	public function test_set_hyphenate_headings() {
		$this->settings->set_hyphenate_headings( true );
		$this->assertTrue( $this->settings->hyphenate_headings );

		$this->settings->set_hyphenate_headings( false );
		$this->assertFalse( $this->settings->hyphenate_headings );
	}

	/**
	 * Tests set_hyphenate_all_caps.
	 *
	 * @uses ::__call
	 */
	public function test_set_hyphenate_all_caps() {
		$this->settings->set_hyphenate_all_caps( true );
		$this->assertTrue( $this->settings->hyphenate_all_caps );

		$this->settings->set_hyphenate_all_caps( false );
		$this->assertFalse( $this->settings->hyphenate_all_caps );
	}

	/**
	 * Tests set_hyphenate_title_case.
	 *
	 * @uses ::__call
	 */
	public function test_set_hyphenate_title_case() {
		$this->settings->set_hyphenate_title_case( true );
		$this->assertTrue( $this->settings->hyphenate_title_case );

		$this->settings->set_hyphenate_title_case( false );
		$this->assertFalse( $this->settings->hyphenate_title_case );
	}

	/**
	 * Tests set_hyphenate_compounds.
	 *
	 * @uses ::__call
	 */
	public function test_set_hyphenate_compounds() {
		$this->settings->set_hyphenate_compounds( true );
		$this->assertTrue( $this->settings->hyphenate_compounds );

		$this->settings->set_hyphenate_compounds( false );
		$this->assertFalse( $this->settings->hyphenate_compounds );
	}

	/**
	 * Tests set_hyphenation_exceptions.
	 *
	 * @uses ::__call
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 */
	public function test_set_hyphenation_exceptions() {
		$s = $this->settings;

		$exceptions = [ 'Hu-go', 'Fö-ba-ß' ];
		$s->set_hyphenation_exceptions( $exceptions );
		$this->assertContainsOnly( 'string', $s->hyphenation_exceptions );
		$this->assertCount( 2, $s->hyphenation_exceptions );

		$exceptions = [ 'bar-foo' ];
		$s->set_hyphenation_exceptions( $exceptions );
		$this->assertContainsOnly( 'string', $s->hyphenation_exceptions );
		$this->assertCount( 1, $s->hyphenation_exceptions );
	}

	/**
	 * Tests get_hash.
	 *
	 * @covers ::get_hash
	 * @covers ::jsonSerialize
	 *
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
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
		$this->assert_attribute_same( $mapping, 'unicode_mapping', $s );

		$s->remap_character( 'a', 'a' );
		$this->assert_attribute_same( [ 'r' => 'z' ], 'unicode_mapping', $s );

		$s->remap_character( U::NO_BREAK_NARROW_SPACE, 'x' );
		$this->assert_attribute_count( 2, 'unicode_mapping', $s );
		$this->assert_attribute_contains( 'x', 'unicode_mapping', $s );
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
}
