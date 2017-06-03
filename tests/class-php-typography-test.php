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

namespace PHP_Typography\Tests;

use \PHP_Typography\Strings;

/**
 * PHP_Typography unit test.
 *
 * @coversDefaultClass \PHP_Typography\PHP_Typography
 * @usesDefaultClass \PHP_Typography\PHP_Typography
 *
 * @uses PHP_Typography\PHP_Typography
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\get_ancestors
 * @uses PHP_Typography\has_class
 * @uses PHP_Typography\nodelist_to_array
 * @uses PHP_Typography\uchr
 * @uses PHP_Typography\arrays_intersect
 * @uses PHP_Typography\is_odd
 * @uses PHP_Typography\mb_str_split
 */
class PHP_Typography_Test extends PHP_Typography_Testcase {

	/**
	 * The PHP_Typography instance.
	 *
	 * @var PHP_Typography
	 */
	protected $typo;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		$this->typo = new \PHP_Typography\PHP_Typography( false );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
	}

	/**
	 * Test get_settings.
	 *
	 * @covers ::get_settings
	 */
	public function test_get_settings() {
		$typo = $this->typo;
		$s    = $typo->get_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );

		$second_typo = new \PHP_Typography\PHP_Typography( false, 'lazy' );
		$s           = $second_typo->get_settings();

		$this->assertSame( null, $s );
	}

	/**
	 * Test set_ignore_parser_errors.
	 *
	 * @covers ::set_ignore_parser_errors
	 */
	public function test_set_ignore_parser_errors() {
		$typo = $this->typo;

		$typo->set_ignore_parser_errors( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['parserErrorsIgnore'] );

		$typo->set_ignore_parser_errors( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['parserErrorsIgnore'] );
	}

	/**
	 * Test set_parser_errors_handler.
	 *
	 * @covers ::set_parser_errors_handler
	 */
	public function test_set_parser_errors_handler() {
		$typo = $this->typo;

		// Default: no handler.
		$s = $typo->get_settings();
		$this->assertEmpty( $s['parserErrorsHandler'] );

		// Valid handler.
		$typo->set_parser_errors_handler( function ( $errors ) {
			return [];
		} );
		$s = $typo->get_settings();
		$this->assertInternalType( 'callable', $s['parserErrorsHandler'] );
		$old_handler = $s['parserErrorsHandler'];

		// Invalid handler, previous handler not changed.
		$typo->set_parser_errors_handler( 'foobar' );
		$s = $typo->get_settings();
		$this->assertInternalType( 'callable', $s['parserErrorsHandler'] );
		$this->assertSame( $old_handler, $s['parserErrorsHandler'] );
	}

	/**
	 * Test set_tags_to_ignore.
	 *
	 * @covers ::set_tags_to_ignore
	 *
	 * @uses \PHP_Typography\Text_Parser
	 */
	public function test_set_tags_to_ignore() {
		$typo = $this->typo;
		$always_ignore = [
			'iframe',
			'textarea',
			'button',
			'select',
			'optgroup',
			'option',
			'map',
			'style',
			'head',
			'title',
			'script',
			'applet',
			'object',
			'param',
		];
		$self_closing_tags = [
			'area',
			'base',
			'basefont',
			'br',
			'frame',
			'hr',
			'img',
			'input',
			'link',
			'meta',
		];

		// Default tags.
		$typo->set_tags_to_ignore( [
			'code',
			'head',
			'kbd',
			'object',
			'option',
			'pre',
			'samp',
			'script',
			'noscript',
			'noembed',
			'select',
			'style',
			'textarea',
			'title',
			'var',
			'math',
		] );

		// Inspect settings.
		$s = $typo->get_settings();
		$this->assertArraySubset( [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ], $s['ignoreTags'] );
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}
		foreach ( $self_closing_tags as $tag ) {
			$this->assertNotContains( $tag, $s['ignoreTags'] );
		}

		// Auto-close tag and something else.
		$typo->set_tags_to_ignore( [ 'img', 'foo' ] );
		$s = $typo->get_settings();
		$this->assertContains( 'foo', $s['ignoreTags'] );
		foreach ( $self_closing_tags as $tag ) {
			$this->assertNotContains( $tag, $s['ignoreTags'] );
		}
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		$typo->set_tags_to_ignore( 'img foo  \	' ); // Should not result in an error.

		$html = '<p><foo>Ignore this "quote",</foo><span class="other"> but not "this" one.</span></p>';
		$expected = '<p><foo>Ignore this "quote",</foo><span class="other"> but not &ldquo;this&rdquo; one.</span></p>';
		$typo->set_smart_quotes( true );
		$this->assertSame( $expected, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test set_classes_to_ignore.
	 *
	 * @covers ::set_classes_to_ignore
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_set_classes_to_ignore() {
		$typo = $this->typo;

		$typo->set_classes_to_ignore( 'foo bar' );
		$s = $typo->get_settings();

		$this->assertContains( 'foo', $s['ignoreClasses'] );
		$this->assertContains( 'bar', $s['ignoreClasses'] );

		$html = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';
		$expected = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';
		$typo->set_smart_quotes( true );
		$this->assertSame( $expected, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test set_ids_to_ignore.
	 *
	 * @covers ::set_ids_to_ignore
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_set_ids_to_ignore() {
		$typo = $this->typo;
		$typo->set_ids_to_ignore( 'foobar barfoo' );
		$s = $typo->get_settings();

		$this->assertContains( 'foobar', $s['ignoreIDs'] );
		$this->assertContains( 'barfoo', $s['ignoreIDs'] );

		$html = '<p><span id="foobar">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p id="barfoo">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';
		$expected = '<p><span id="foobar">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p id="barfoo">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';
		$typo->set_smart_quotes( true );
		$this->assertSame( $expected, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Integrate all three "ignore" variants.
	 *
	 * @covers ::set_classes_to_ignore
	 * @covers ::set_ids_to_ignore
	 * @covers ::set_tags_to_ignore
	 * @covers ::query_tags_to_ignore
	 *
	 * @depends test_set_ids_to_ignore
	 * @depends test_set_classes_to_ignore
	 * @depends test_set_tags_to_ignore
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_complete_ignore() {
		$typo = $this->typo;

		$typo->set_ids_to_ignore( 'foobar barfoo' );
		$typo->set_classes_to_ignore( 'foo bar' );
		$typo->set_tags_to_ignore( [ 'img', 'foo' ] );

		$html = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';
		$expected = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';
		$typo->set_smart_quotes( true );
		$this->assertSame( $expected, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test set_smart_quotes.
	 *
	 * @covers ::set_smart_quotes
	 */
	public function test_set_smart_quotes() {
		$typo = $this->typo;

		$typo->set_smart_quotes( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartQuotes'] );

		$typo->set_smart_quotes( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartQuotes'] );
	}

	/**
	 * Test set_smart_quotes_primary.
	 *
	 * @covers ::set_smart_quotes_primary
	 */
	public function test_set_smart_quotes_primary() {
		$typo = $this->typo;
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
			$typo->set_smart_quotes_primary( $style );
			$s = $typo->get_settings();

			$this->assertSmartQuotesStyle( $style, $s->chr( 'doubleQuoteOpen' ), $s->chr( 'doubleQuoteClose' ) );
		}
	}

	/**
	 * Test set_smart_quotes_primary.
	 *
	 * @covers ::set_smart_quotes_primary
	 *
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
	 */
	public function test_set_smart_quotes_primary_invalid() {
		$typo = $this->typo;

		$typo->set_smart_quotes_primary( 'invalidStyleName' );
	}

	/**
	 * Test set_smart_quotes_secondary.
	 *
	 * @covers ::set_smart_quotes_secondary
	 */
	public function test_set_smart_quotes_secondary() {
		$typo = $this->typo;
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
			$typo->set_smart_quotes_secondary( $style );
			$s = $typo->get_settings();

			$this->assertSmartQuotesStyle( $style, $s->chr( 'singleQuoteOpen' ), $s->chr( 'singleQuoteClose' ) );
		}
	}

	/**
	 * Test set_smart_quotes_secondary.
	 *
	 * @covers ::set_smart_quotes_secondary
	 *
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
	 */
	public function test_set_smart_quotes_secondary_invalid() {
		$typo = $this->typo;

		$typo->set_smart_quotes_secondary( 'invalidStyleName' );
	}

	/**
	 * Assert that the given quote styles match.
	 *
	 * @param string $style Style name.
	 * @param string $open  Opening quote character.
	 * @param string $close Closing quote character.
	 */
	private function assertSmartQuotesStyle( $style, $open, $close ) { // @codingStandardsIgnoreLine
		switch ( $style ) {
			case 'doubleCurled':
				$this->assertSame( Strings::uchr( 8220 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8221 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleCurledReversed':
				$this->assertSame( Strings::uchr( 8221 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8221 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleLow9':
				$this->assertSame( Strings::uchr( 8222 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8221 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleLow9Reversed':
				$this->assertSame( Strings::uchr( 8222 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8220 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleCurled':
				$this->assertSame( Strings::uchr( 8216 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8217 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleCurledReversed':
				$this->assertSame( Strings::uchr( 8217 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8217 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleLow9':
				$this->assertSame( Strings::uchr( 8218 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8217 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleLow9Reversed':
				$this->assertSame( Strings::uchr( 8218 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8216 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemetsFrench':
				$this->assertSame( Strings::uchr( 171 ) . Strings::uchr( 160 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 160 ) . Strings::uchr( 187 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemets':
				$this->assertSame( Strings::uchr( 171 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 187 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'doubleGuillemetsReversed':
				$this->assertSame( Strings::uchr( 187 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 171 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleGuillemets':
				$this->assertSame( Strings::uchr( 8249 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8250 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'singleGuillemetsReversed':
				$this->assertSame( Strings::uchr( 8250 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 8249 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'cornerBrackets':
				$this->assertSame( Strings::uchr( 12300 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 12301 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			case 'whiteCornerBracket':
				$this->assertSame( Strings::uchr( 12302 ), $open,  "Opening quote $open did not match quote style $style." );
				$this->assertSame( Strings::uchr( 12303 ), $close, "Closing quote $close did not match quote style $style." );
				break;

			default:
				$this->assertTrue( false, "Invalid quote style $style." );
		} // End switch().
	}

	/**
	 * Test set_smart_dashes.
	 *
	 * @covers ::set_smart_dashes
	 */
	public function test_set_smart_dashes() {
		$typo = $this->typo;
		$typo->set_smart_dashes( true );
		$s = $typo->get_settings();

		$this->assertTrue( $s['smartDashes'] );

		$typo->set_smart_dashes( false );
		$s = $typo->get_settings();

		$this->assertFalse( $s['smartDashes'] );
	}

	/**
	 * Test set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 */
	public function test_set_smart_dashes_style() {
		$typo = $this->typo;
		$typo->set_smart_dashes_style( 'traditionalUS' );
		$s = $typo->get_settings();

		$this->assertEquals( $s->chr( 'emDash' ), $s->chr( 'parentheticalDash' ) );
		$this->assertEquals( $s->chr( 'enDash' ), $s->chr( 'intervalDash' ) );
		$this->assertEquals( $s->chr( 'thinSpace' ), $s->chr( 'parentheticalDashSpace' ) );
		$this->assertEquals( $s->chr( 'thinSpace' ), $s->chr( 'intervalDashSpace' ) );

		$typo->set_smart_dashes_style( 'international' );
		$s = $typo->get_settings();

		$this->assertEquals( $s->chr( 'enDash' ), $s->chr( 'parentheticalDash' ) );
		$this->assertEquals( $s->chr( 'enDash' ), $s->chr( 'intervalDash' ) );
		$this->assertEquals( ' ', $s->chr( 'parentheticalDashSpace' ) );
		$this->assertEquals( $s->chr( 'hairSpace' ), $s->chr( 'intervalDashSpace' ) );
	}

	/**
	 * Test set_smart_dashes_style.
	 *
	 * @covers ::set_smart_dashes_style
	 *
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 * @expectedExceptionMessageRegExp /^Invalid dash style \w+.$/
	 */
	public function test_set_smart_dashes_style_invalid() {
		$typo = $this->typo;

		$typo->set_smart_dashes_style( 'invalidStyleName' );
	}

	/**
	 * Test set_smart_ellipses.
	 *
	 * @covers ::set_smart_ellipses
	 */
	public function test_set_smart_ellipses() {
		$typo = $this->typo;
		$typo->set_smart_ellipses( true );
		$s = $typo->get_settings();

		$this->assertTrue( $s['smartEllipses'] );

		$typo->set_smart_ellipses( false );
		$s = $typo->get_settings();

		$this->assertFalse( $s['smartEllipses'] );
	}

	/**
	 * Test t_smart_diacritics.
	 *
	 * @covers ::set_smart_diacritics
	 */
	public function test_set_smart_diacritics() {
		$typo = $this->typo;
		$typo->set_smart_diacritics( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartDiacritics'] );

		$typo->set_smart_diacritics( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartDiacritics'] );
	}

	/**
	 * Test set_diacritic_language.
	 *
	 * @covers ::set_diacritic_language
	 */
	public function test_set_diacritic_language() {
		$typo = $this->typo;
		$typo->set_diacritic_language( 'en-US' );
		$s = $typo->get_settings();
		$this->assertGreaterThan( 0, count( $s['diacriticWords'] ) );

		$typo->set_diacritic_language( 'foobar' );
		$s = $typo->get_settings();
		$this->assertFalse( isset( $s['diacriticWords'] ) );

		$typo->set_diacritic_language( 'de-DE' );
		$s = $typo->get_settings();
		$this->assertTrue( isset( $s['diacriticWords'] ) );
		$this->assertGreaterThan( 0, count( $s['diacriticWords'] ) );

		// Nothing changed since the last call.
		$typo->set_diacritic_language( 'de-DE' );
		$s = $typo->get_settings();
		$this->assertTrue( isset( $s['diacriticWords'] ) );
		$this->assertGreaterThan( 0, count( $s['diacriticWords'] ) );
	}

	/**
	 * Test set_diacritic_custom_replacements.
	 *
	 * @covers ::set_diacritic_custom_replacements
	 */
	public function test_set_diacritic_custom_replacements() {
		$typo = $this->typo;

		$typo->set_diacritic_custom_replacements( '"foo" => "fóò", "bar" => "bâr"' . ", 'ha' => 'hä'" );
		$s = $typo->get_settings();
		$this->assertArrayHasKey( 'foo', $s['diacriticCustomReplacements'] );
		$this->assertArrayHasKey( 'bar', $s['diacriticCustomReplacements'] );
		$this->assertArrayHasKey( 'ha', $s['diacriticCustomReplacements'] );
		$this->assertContains( 'fóò', $s['diacriticCustomReplacements'] );
		$this->assertContains( 'bâr', $s['diacriticCustomReplacements'] );
		$this->assertContains( 'hä', $s['diacriticCustomReplacements'] );

		$typo->set_diacritic_custom_replacements( [
			'fööbar' => 'fúbar',
		] );
		$s = $typo->get_settings();
		$this->assertArrayNotHasKey( 'foo', $s['diacriticCustomReplacements'] );
		$this->assertArrayNotHasKey( 'bar', $s['diacriticCustomReplacements'] );
		$this->assertArrayHasKey( 'fööbar', $s['diacriticCustomReplacements'] );
		$this->assertContains( 'fúbar', $s['diacriticCustomReplacements'] );
	}

	/**
	 * Test set_smart_marks.
	 *
	 * @covers ::set_smart_marks
	 */
	public function test_set_smart_marks() {
		$typo = $this->typo;

		$typo->set_smart_marks( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartMarks'] );

		$typo->set_smart_marks( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartMarks'] );
	}

	/**
	 * Test set_smart_math.
	 *
	 * @covers ::set_smart_math
	 */
	public function test_set_smart_math() {
		$typo = $this->typo;

		$typo->set_smart_math( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartMath'] );

		$typo->set_smart_math( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartMath'] );
	}

	/**
	 * Test set_smart_exponents.
	 *
	 * @covers ::set_smart_exponents
	 */
	public function test_set_smart_exponents() {
		$typo = $this->typo;

		$typo->set_smart_exponents( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartExponents'] );

		$typo->set_smart_exponents( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartExponents'] );
	}

	/**
	 * Test set_smart_fractions.
	 *
	 * @covers ::set_smart_fractions
	 */
	public function test_set_smart_fractions() {
		$typo = $this->typo;

		$typo->set_smart_fractions( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartFractions'] );

		$typo->set_smart_fractions( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartFractions'] );
	}

	/**
	 * Test set_smart_ordinal_suffix.
	 *
	 * @covers ::set_smart_ordinal_suffix
	 */
	public function test_set_smart_ordinal_suffix() {
		$typo = $this->typo;

		$typo->set_smart_ordinal_suffix( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['smartOrdinalSuffix'] );

		$typo->set_smart_ordinal_suffix( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['smartOrdinalSuffix'] );
	}

	/**
	 * Test set_single_character_word_spacing.
	 *
	 * @covers ::set_single_character_word_spacing
	 */
	public function test_set_single_character_word_spacing() {
		$typo = $this->typo;

		$typo->set_single_character_word_spacing( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['singleCharacterWordSpacing'] );

		$typo->set_single_character_word_spacing( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['singleCharacterWordSpacing'] );
	}

	/**
	 * Test set_fraction_spacing.
	 *
	 * @covers ::set_fraction_spacing
	 */
	public function test_set_fraction_spacing() {
		$typo = $this->typo;

		$typo->set_fraction_spacing( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['fractionSpacing'] );

		$typo->set_fraction_spacing( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['fractionSpacing'] );
	}

	/**
	 * Test set_unit_spacing.
	 *
	 * @covers ::set_unit_spacing
	 */
	public function test_set_unit_spacing() {
		$typo = $this->typo;

		$typo->set_unit_spacing( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['unitSpacing'] );

		$typo->set_unit_spacing( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['unitSpacing'] );
	}


	/**
	 * Test set_french_punctuation_spacing.
	 *
	 * @covers ::set_french_punctuation_spacing
	 */
	public function test_set_french_punctuation_spacing() {
		$typo = $this->typo;

		$typo->set_french_punctuation_spacing( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['frenchPunctuationSpacing'] );

		$typo->set_french_punctuation_spacing( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['frenchPunctuationSpacing'] );
	}


	/**
	 * Test s.
	 *
	 * @covers ::set_units
	 */
	public function test_set_units() {
		$typo = $this->typo;

		$units_as_array = [ 'foo', 'bar', 'xx/yy' ];
		$units_as_string = implode( ', ', $units_as_array );

		$typo->set_units( $units_as_array );
		$s = $typo->get_settings();
		foreach ( $units_as_array as $unit ) {
			$this->assertContains( $unit, $s['units'] );
		}

		$typo->set_units( [] );
		$s = $typo->get_settings();
		foreach ( $units_as_array as $unit ) {
			$this->assertNotContains( $unit, $s['units'] );
		}

		$typo->set_units( $units_as_string );
		$s = $typo->get_settings();
		foreach ( $units_as_array as $unit ) {
			$this->assertContains( $unit, $s['units'] );
		}
	}

	/**
	 * Test set_dash_spacing.
	 *
	 * @covers ::set_dash_spacing
	 */
	public function test_set_dash_spacing() {
		$typo = $this->typo;

		$typo->set_dash_spacing( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['dashSpacing'] );

		$typo->set_dash_spacing( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['dashSpacing'] );
	}

	/**
	 * Test set_space_collapse.
	 *
	 * @covers ::set_space_collapse
	 */
	public function test_set_space_collapse() {
		$typo = $this->typo;

		$typo->set_space_collapse( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['spaceCollapse'] );

		$typo->set_space_collapse( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['spaceCollapse'] );
	}

	/**
	 * Test set_dewidow.
	 *
	 * @covers ::set_dewidow
	 */
	public function test_set_dewidow() {
		$typo = $this->typo;

		$typo->set_dewidow( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['dewidow'] );

		$typo->set_dewidow( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['dewidow'] );
	}

	/**
	 * Test set_max_dewidow_length.
	 *
	 * @covers ::set_max_dewidow_length
	 */
	public function test_set_max_dewidow_length() {
		$typo = $this->typo;

		$typo->set_max_dewidow_length( 10 );
		$s = $typo->get_settings();
		$this->assertSame( 10, $s['dewidowMaxLength'] );

		$typo->set_max_dewidow_length( 1 );
		$s = $typo->get_settings();
		$this->assertSame( 5, $s['dewidowMaxLength'] );

		$typo->set_max_dewidow_length( 2 );
		$s = $typo->get_settings();
		$this->assertSame( 2, $s['dewidowMaxLength'] );
	}

	/**
	 * Test set_max_dewidow_pull.
	 *
	 * @covers ::set_max_dewidow_pull
	 */
	public function test_set_max_dewidow_pull() {
		$typo = $this->typo;

		$typo->set_max_dewidow_pull( 10 );
		$s = $typo->get_settings();
		$this->assertSame( 10, $s['dewidowMaxPull'] );

		$typo->set_max_dewidow_pull( 1 );
		$s = $typo->get_settings();
		$this->assertSame( 5, $s['dewidowMaxPull'] );

		$typo->set_max_dewidow_pull( 2 );
		$s = $typo->get_settings();
		$this->assertSame( 2, $s['dewidowMaxPull'] );
	}

	/**
	 * Test set_wrap_hard_hyphens.
	 *
	 * @covers ::set_wrap_hard_hyphens
	 */
	public function test_set_wrap_hard_hyphens() {
		$typo = $this->typo;

		$typo->set_wrap_hard_hyphens( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenHardWrap'] );

		$typo->set_wrap_hard_hyphens( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenHardWrap'] );
	}

	/**
	 * Test set_url_wrap.
	 *
	 * @covers ::set_url_wrap
	 */
	public function test_set_url_wrap() {
		$typo = $this->typo;

		$typo->set_url_wrap( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['urlWrap'] );

		$typo->set_url_wrap( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['urlWrap'] );
	}

	/**
	 * Test set_email_wrap.
	 *
	 * @covers ::set_email_wrap
	 */
	public function test_set_email_wrap() {
		$typo = $this->typo;

		$typo->set_email_wrap( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['emailWrap'] );

		$typo->set_email_wrap( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['emailWrap'] );
	}

	/**
	 * Test set_min_after_url_wrap.
	 *
	 * @covers ::set_min_after_url_wrap
	 */
	public function test_set_min_after_url_wrap() {
		$typo = $this->typo;

		$typo->set_min_after_url_wrap( 10 );
		$s = $typo->get_settings();
		$this->assertSame( 10, $s['urlMinAfterWrap'] );

		$typo->set_min_after_url_wrap( 0 );
		$s = $typo->get_settings();
		$this->assertSame( 5, $s['urlMinAfterWrap'] );

		$typo->set_min_after_url_wrap( 1 );
		$s = $typo->get_settings();
		$this->assertSame( 1, $s['urlMinAfterWrap'] );
	}

	/**
	 * Test set_style_ampersands.
	 *
	 * @covers ::set_style_ampersands
	 */
	public function test_set_style_ampersands() {
		$typo = $this->typo;

		$typo->set_style_ampersands( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['styleAmpersands'] );

		$typo->set_style_ampersands( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['styleAmpersands'] );
	}

	/**
	 * Test set_style_caps.
	 *
	 * @covers ::set_style_caps
	 */
	public function test_set_style_caps() {
		$typo = $this->typo;

		$typo->set_style_caps( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['styleCaps'] );

		$typo->set_style_caps( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['styleCaps'] );
	}

	/**
	 * Test set_style_initial_quotes.
	 *
	 * @covers ::set_style_initial_quotes
	 */
	public function test_set_style_initial_quotes() {
		$typo = $this->typo;

		$typo->set_style_initial_quotes( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['styleInitialQuotes'] );

		$typo->set_style_initial_quotes( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['styleInitialQuotes'] );
	}


	/**
	 * Test set_style_numbers.
	 *
	 * @covers ::set_style_numbers
	 */
	public function test_set_style_numbers() {
		$typo = $this->typo;

		$typo->set_style_numbers( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['styleNumbers'] );

		$typo->set_style_numbers( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['styleNumbers'] );
	}


	/**
	 * Test set_style_hanging_punctuation.
	 *
	 * @covers ::set_style_hanging_punctuation
	 */
	public function test_set_style_hanging_punctuation() {
		$typo = $this->typo;

		$typo->set_style_hanging_punctuation( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['styleHangingPunctuation'] );

		$typo->set_style_hanging_punctuation( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['styleHangingPunctuation'] );
	}

	/**
	 * Test set_initial_quote_tags.
	 *
	 * @covers ::set_initial_quote_tags
	 */
	public function test_set_initial_quote_tags() {
		$typo = $this->typo;

		$tags_as_array = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'div' ];
		$tags_as_string = implode( ', ', $tags_as_array );

		$typo->set_initial_quote_tags( $tags_as_array );
		$s = $typo->get_settings();
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $s['initialQuoteTags'] );
		}

		$typo->set_initial_quote_tags( [] );
		$s = $typo->get_settings();
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayNotHasKey( $tag, $s['initialQuoteTags'] );
		}

		$typo->set_initial_quote_tags( $tags_as_string );
		$s = $typo->get_settings();
		foreach ( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $s['initialQuoteTags'] );
		}
	}

	/**
	 * Test set_hyphenation.
	 *
	 * @covers ::set_hyphenation
	 */
	public function test_set_hyphenation() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenation'] );

		$typo->set_hyphenation( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenation'] );
	}

	/**
	 * Provide data for testing set_hyphenation language.
	 *
	 * @return array
	 */
	public function provide_hyphenation_language_data() {
		return [
			[ 'en-US',  true ],
			[ 'foobar', true ],
			[ 'no',     true ],
			[ 'de',     true ],
		];
	}


	/**
	 * Test set_hyphenation_language.
	 *
	 * @covers ::set_hyphenation_language
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_language
	 * @uses ReflectionClass
	 * @uses ReflectionProperty
	 *
	 * @dataProvider provide_hyphenation_language_data
	 *
	 * @param string $lang    Language code.
	 * @param bool   $success If the call should be successful.
	 */
	public function test_set_hyphenation_language( $lang, $success ) {
		$typo = $this->typo;
		$ref = new \ReflectionClass( get_class( $typo ) );
		$prop = $ref->getProperty( 'settings' );
		$prop->setAccessible( true );
		$s = $prop->getValue( $typo );
		$s['hyphenationExceptions'] = []; // necessary for full coverage.

		$typo->set_hyphenation_language( $lang );

		// If the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless.
		if ( $success || ! isset( $typo->hyphenator ) ) {
			$this->assertSame( $lang, $s['hyphenLanguage'] );
		} else {
			$this->assertFalse( isset( $s['hyphenLanguage'] ) );
		}
	}


	/**
	 * Test set_min_length_hyphenation.
	 *
	 * @covers ::set_min_length_hyphenation
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_length_hyphenation() {
		$typo = $this->typo;

		$typo->set_min_length_hyphenation( 1 ); // Too low, resets to default 5.
		$s = $typo->get_settings();
		$this->assertSame( 5, $s['hyphenMinLength'] );

		$typo->set_min_length_hyphenation( 2 );
		$s = $typo->get_settings();
		$this->assertSame( 2, $s['hyphenMinLength'] );

		$typo->get_hyphenator( $s );
		$typo->set_min_length_hyphenation( 66 );
		$s = $typo->get_settings();
		$this->assertSame( 66, $s['hyphenMinLength'] );
	}


	/**
	 * Test set_min_before_hyphenation.
	 *
	 * @covers ::set_min_before_hyphenation
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_before_hyphenation() {
		$typo = $this->typo;

		$typo->set_min_before_hyphenation( 0 ); // too low, resets to default 3.
		$s = $typo->get_settings();
		$this->assertSame( 3, $s['hyphenMinBefore'] );

		$typo->set_min_before_hyphenation( 1 );
		$s = $typo->get_settings();
		$this->assertSame( 1, $s['hyphenMinBefore'] );

		$typo->get_hyphenator( $s );
		$typo->set_min_before_hyphenation( 66 );
		$s = $typo->get_settings();
		$this->assertSame( 66, $s['hyphenMinBefore'] );
	}


	/**
	 * Test set_min_after_hyphenation.
	 *
	 * @covers ::set_min_after_hyphenation
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 */
	public function test_set_min_after_hyphenation() {
		$typo = $this->typo;

		$typo->set_min_after_hyphenation( 0 ); // too low, resets to default 2.
		$s = $typo->get_settings();
		$this->assertSame( 2, $s['hyphenMinAfter'] );

		$typo->set_min_after_hyphenation( 1 );
		$s = $typo->get_settings();
		$this->assertSame( 1, $s['hyphenMinAfter'] );

		$typo->get_hyphenator( $s );
		$typo->set_min_after_hyphenation( 66 );
		$s = $typo->get_settings();
		$this->assertSame( 66, $s['hyphenMinAfter'] );
	}


	/**
	 * Test set_hyphenate_headings.
	 *
	 * @covers ::set_hyphenate_headings
	 */
	public function test_set_hyphenate_headings() {
		$typo = $this->typo;

		$typo->set_hyphenate_headings( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenateTitle'] );

		$typo->set_hyphenate_headings( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenateTitle'] );
	}


	/**
	 * Test set_hyphenate_all_caps.
	 *
	 * @covers ::set_hyphenate_all_caps
	 */
	public function test_set_hyphenate_all_caps() {
		$typo = $this->typo;

		$typo->set_hyphenate_all_caps( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenateAllCaps'] );

		$typo->set_hyphenate_all_caps( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenateAllCaps'] );
	}


	/**
	 * Test set_hyphenate_title_case.
	 *
	 * @covers ::set_hyphenate_title_case
	 */
	public function test_set_hyphenate_title_case() {
		$typo = $this->typo;

		$typo->set_hyphenate_title_case( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenateTitleCase'] );

		$typo->set_hyphenate_title_case( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenateTitleCase'] );
	}


	/**
	 * Test set_hyphenate_compounds.
	 *
	 * @covers ::set_hyphenate_compounds
	 */
	public function test_set_hyphenate_compounds() {
		$typo = $this->typo;

		$typo->set_hyphenate_compounds( true );
		$s = $typo->get_settings();
		$this->assertTrue( $s['hyphenateCompounds'] );

		$typo->set_hyphenate_compounds( false );
		$s = $typo->get_settings();
		$this->assertFalse( $s['hyphenateCompounds'] );
	}


	/**
	 * Test set_hyphenation_exceptions.
	 *
	 * @covers ::set_hyphenation_exceptions
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\get_object_hash
	 */
	public function test_set_hyphenation_exceptions_array() {
		$typo = $this->typo;

		$exceptions = [ 'Hu-go', 'Fö-ba-ß' ];
		$typo->set_hyphenation_exceptions( $exceptions );
		$s = $typo->get_settings();
		$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
		$this->assertCount( 2, $s['hyphenationCustomExceptions'] );

		$typo->get_hyphenator( $s );
		$exceptions = [ 'bar-foo' ];
		$typo->set_hyphenation_exceptions( $exceptions );
		$s = $typo->get_settings();
		$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
		$this->assertCount( 1, $s['hyphenationCustomExceptions'] );
	}


	/**
	 * Test set_hyphenation_exceptions.
	 *
	 * @covers ::set_hyphenation_exceptions
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 */
	public function test_set_hyphenation_exceptions_string() {
		$typo = $this->typo;
		$exceptions = 'Hu-go, Fö-ba-ß';

		$typo->set_hyphenation_exceptions( $exceptions );
		$s = $typo->get_settings();
		$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
		$this->assertCount( 2, $s['hyphenationCustomExceptions'] );
	}


	/**
	 * Test get_hyphenation_languages.
	 *
	 * @covers ::get_hyphenation_languages
	 *
	 * @uses PHP_Typography\get_language_plugin_list
	 */
	public function test_get_hyphenation_languages() {
		$typo = $this->typo;

		$expected = [
			'af',
			'bg',
			'ca',
			'cs',
			'cy',
			'da',
			'de',
			'de-1901',
			'el-Mono',
			'el-Poly',
			'en-GB',
			'en-US',
			'es',
			'et',
			'eu',
			'fi',
			'fr',
			'ga',
			'gl',
			'grc',
			'hr',
			'hu',
			'hy',
			'ia',
			'id',
			'is',
			'it',
			'ka',
			'la',
			'la-classic',
			'la-liturgic',
			'lt',
			'lv',
			'mn-Cyrl',
			'nl',
			'no',
			'pl',
			'pt',
			'ro',
			'ru',
			'sa',
			'sh-Cyrl',
			'sh-Latn',
			'sk',
			'sl',
			'sr-Cyrl',
			'sv',
			'th',
			'tr',
			'uk',
			'zh-Latn',
		];
		$not_expected = [ 'klingon', 'de-DE' ];

		$actual = $typo->get_hyphenation_languages();
		foreach ( $expected as $lang_code ) {
			$this->assertArrayHasKey( $lang_code, $actual );
		}
		foreach ( $not_expected as $lang_code ) {
			$this->assertArrayNotHasKey( $lang_code, $actual );
		}
	}


	/**
	 * Test get_diacritic_languages.
	 *
	 * @covers ::get_diacritic_languages
	 *
	 * @uses PHP_Typography\get_language_plugin_list
	 */
	public function test_get_diacritic_languages() {
		$typo = $this->typo;

		$expected = [ 'de-DE', 'en-US' ];
		$not_expected = [
			'es',
			'et',
			'eu',
			'fi',
			'fr',
			'ga',
			'gl',
			'grc',
			'hr',
			'hu',
			'ia',
			'id',
			'is',
			'it',
			'la',
			'lt',
			'mn-Cyrl',
			'no',
			'pl',
			'pt',
			'ro',
			'ru',
			'sa',
			'sh-Cyrl',
			'sh-Latn',
			'sk',
			'sl',
			'sr-Cyrl',
			'sv',
			'tr',
			'uk',
			'zh-Latn',
		];

		$actual = $typo->get_diacritic_languages();
		foreach ( $expected as $lang_code ) {
			$this->assertArrayHasKey( $lang_code, $actual );
		}
		foreach ( $not_expected as $lang_code ) {
			$this->assertArrayNotHasKey( $lang_code, $actual );
		}
	}

	/**
	 * Provide data for testing the complete processing.
	 *
	 * @return array
	 */
	public function provide_process_data() {
		return [
			[ '3*3=3^2', '<span class="numbers">3</span>&times;<span class="numbers">3</span>=<span class="numbers">3</span><sup><span class="numbers">2</span></sup>', false ], // smart math.
			[ '"Hey there!"', '<span class="pull-double">&ldquo;</span>Hey there!&rdquo;', '&ldquo;Hey there!&rdquo;' ], // smart quotes.
			[ 'Hey - there', 'Hey&thinsp;&mdash;&thinsp;there', 'Hey &mdash; there' ], // smart dashes.
			[ 'Hey...', 'Hey&hellip;', true ], // smart ellipses.
			[ '(c)', '&copy;', true ], // smart marks.
			[ 'creme', 'cr&egrave;me', false ], // diacritics.
			[ 'a a a', 'a a&nbsp;a', false ], // single characgter word spacing.
			[ '3 cm', '<span class="numbers">3</span>&nbsp;cm', false ], // unit spacing without true no-break narrow space.
			[ 'a/b', 'a/&#8203;b', false ], // dash spacing.
			[ '<span class="numbers">5</span>', '<span class="numbers">5</span>', true ], // class present, no change.
			[ '1st', '<span class="numbers">1</span><sup class="ordinal">st</sup>', false ], // smart ordinal suffixes.
			[ '1^1', '<span class="numbers">1</span><sup><span class="numbers">1</span></sup>', false ], // smart exponents.
			[ 'a &amp; b', 'a <span class="amp">&amp;</span>&nbsp;b', false ], // wrap amps.
			[ 'a  b', 'a b', false ], // space collapse.
			[ 'NATO', '<span class="caps">NATO</span>', false ], // style caps.
			[ 'superfluous', 'super&shy;flu&shy;ous', false ], // hyphenate.
			[ 'http://example.org', 'http://&#8203;exam&#8203;ple&#8203;.org', false ], // wrap URLs.
			[ 'foo@example.org', 'foo@&#8203;example.&#8203;org', false ], // wrap emails.
			[ '<span> </span>', '<span> </span>', true ], // whitespace is ignored.
			[ '<span class="noTypo">123</span>', '<span class="noTypo">123</span>', true ], // skipped class.
			[
				'<section id="main-content" class="container">
				<!-- Start Page Content -->
				<div class="row-wrapper-x"></div></section><section class="blox aligncenter  page-title-x  " style=" padding-top:px; padding-bottom:px;  background: url( \'http://www.feinschliff.hamburg/wp-content/uploads/2014/09/nails_02.jpg\' ) no-repeat ; background-position: center center;background-size: cover; min-height:px; "></section>',
				'<section id="main-content" class="container">
				<!-- Start Page Content -->
				<div class="row-wrapper-x"></div></section><section class="blox aligncenter  page-title-x  " style=" padding-top:px; padding-bottom:px;  background: url( \'http://www.feinschliff.hamburg/wp-content/uploads/2014/09/nails_02.jpg\' ) no-repeat ; background-position: center center;background-size: cover; min-height:px; "></section>',
				true,
			],
			[ '<section id="main"></section>', '<section id="main"></section>', true ],
			[ '<section id="main"><!-- comment --></section>', '<section id="main"><!-- comment --></section>', true ],
			[ '<section id="main"><!-- comment --><div></div></section>', '<section id="main"><!-- comment --><div></div></section>', true ],
			[ 'ช่วยฉัน/ผมหน่อยได้ไหม คะ/ครับ333', 'ช่วยฉัน/ผมหน่อยได้ไหม คะ/ครับ<span class="numbers">333</span>', false ], // Unicode characters in regular expressions.
		];
	}


	/**
	 * Test process.
	 *
	 * @covers ::process
	 * @covers ::apply_fixes_to_html_node
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 * @uses ::process_textnodes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Entity-escaped output.
	 * @param bool   $feed   Use process_feed.
	 */
	public function test_process( $html, $result, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 * @covers ::apply_fixes_to_feed_node
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 * @uses ::process_textnodes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Entity-escaped output.
	 * @param bool|string $feed   Use process_feed. If $feed is a string, use instead of $result.
	 */
	public function test_process_feed( $html, $result, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		if ( is_string( $feed ) ) {
			$this->assertSame( $feed, clean_html( $typo->process_feed( $html ) ) );
		} elseif ( $feed ) {
			$this->assertSame( $result, clean_html( $typo->process_feed( $html ) ) );
		} else {
			$this->assertSame( $html, $typo->process_feed( $html ) );
		}
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
	 * Test process_textnodes.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Entity-escaped output.
	 * @param bool|string $feed   Use process_feed. If $feed is a string, use instead of $result.
	 */
	public function test_process_textnodes( $html, $result, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		$this->assertSame( $html, clean_html( $typo->process_textnodes( $html, function ( $node ) {
		}) ) );
	}

	/**
	 * Provide invalid data for testing process_textnodes.
	 *
	 * @return array
	 */
	public function provide_process_textnodes_invalid_html_data() {
		return [
			[ '<div>foo-bar</div></p>', false ],
			[ '<div>foo-bar</div></p>', true ],
		];
	}


	/**
	 * Test process_textnodes with invalid HTML.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_textnodes_invalid_html_data
	 *
	 * @param string $html HTML input.
	 * @param string $feed Ignored.
	 */
	public function test_process_textnodes_invalid_html( $html, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		$this->assertSame( $html, clean_html( $typo->process_textnodes( $html, function ( $node ) {
			return 'XXX';
		}) ) );
	}


	/**
	 * Test process_textnodes without a fixer instance.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Ignored.
	 * @param bool|string $feed   Ignored.
	 */
	public function test_process_textnodes_no_fixer( $html, $result, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		$typo->process_textnodes( $html, 'bar' );
	}


	/**
	 * Test process_textnodes without a fixer instance (and look at return value).
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Entity-escaped output.
	 * @param bool|string $feed   Use process_feed. If $feed is a string, use instead of $result.
	 */
	public function test_process_textnodes_no_fixer_return_value( $html, $result, $feed ) {
		$typo = $this->typo;
		$typo->set_defaults();

		$this->assertSame( $html, clean_html( @$typo->process_textnodes( $html, 'bar' ) ) );
	}


	/**
	 * Test process_textnodes with alternate settings.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Ignored.
	 * @param bool|string $feed   Ignored.
	 */
	public function test_process_textnodes_alternate_settings( $html, $result, $feed ) {
		$typo = $this->typo;
		$s    = new \PHP_Typography\Settings( true );

		$this->assertSame( $html, clean_html( $typo->process_textnodes( $html, function ( $node ) {
		}, false, $s ) ) );
	}


	/**
	 * Test process_textnodes with alternate settings for titles.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Ignored.
	 * @param bool|string $feed   Ignored.
	 */
	public function test_process_textnodes_alternate_settings_title( $html, $result, $feed ) {
		$typo = $this->typo;
		$s    = new \PHP_Typography\Settings( true );
		$s->set_tags_to_ignore( [ 'h1', 'h2' ] );

		$this->assertSame( $html, clean_html( $typo->process_textnodes( $html, function ( $node ) {
		}, true, $s ) ) );
	}


	/**
	 * Test process_words.
	 *
	 * @covers ::process_words
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_words_data
	 *
	 * @param string $text     The text to process.
	 * @param string $result   The expected result.
	 * @param bool   $is_title If $text should be processed as a title/heading.
	 */
	public function test_process_words( $text, $result, $is_title ) {
		$typo = $this->typo;
		$typo->set_defaults();
		$s = $typo->get_settings();

		$node = new \DOMText( $text );
		$typo->process_words( $node, $s, $is_title );

		$this->assertSame( $result, clean_html( $node->data ) );
	}

	/**
	 * Provide data for testing process with $is_title set to true.
	 *
	 * @return array
	 */
	public function provide_process_with_title_data() {
		return [
			[ 'Really...', 'Real&shy;ly&hellip;', 'Really&hellip;', '' ], // processed.
			[ 'Really...', 'Really...', true, [ 'h1' ] ], // skipped.
		];
	}


	/**
	 * Test process.
	 *
	 * @covers ::process
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_with_title_data
	 *
	 * @param string      $html      HTML input.
	 * @param string      $result    Expected entity-escaped result.
	 * @param bool|string $feed      Ignored.
	 * @param array       $skip_tags Tags to skip.
	 */
	public function test_process_with_title( $html, $result, $feed, $skip_tags ) {
		$typo = $this->typo;
		$typo->set_defaults();
		$typo->set_tags_to_ignore( $skip_tags );

		$this->assertSame( $result, clean_html( $typo->process( $html, true ) ) );
	}


	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_process_with_title_data
	 *
	 * @param string      $html      HTML input.
	 * @param string      $result    Expected entity-escaped result.
	 * @param bool|string $feed      Whether process_feed should be used. If string, use instead of $result.
	 * @param array       $skip_tags Tags to skip.
	 */
	public function test_process_feed_with_title( $html, $result, $feed, $skip_tags ) {
		$typo = $this->typo;
		$typo->set_defaults();
		$typo->set_tags_to_ignore( $skip_tags );

		if ( is_string( $feed ) ) {
			$this->assertSame( $feed, clean_html( $typo->process_feed( $html, true ) ) );
		} elseif ( $feed ) {
			$this->assertSame( $result, clean_html( $typo->process_feed( $html, true ) ) );
		} else {
			$this->assertSame( $html, $typo->process_feed( $html, true ) );
		}
	}

	/**
	 * Provide data for testing handle_parsing_errors.
	 *
	 * @return [ $errno, $errstr, $errfile, $errline, $errcontext, $result ]
	 */
	public function provide_handle_parsing_errors() {
		return [
			[ E_USER_WARNING, 'Fake error message', '/some/path/DOMTreeBuilder.php', '666', [], true ],
			[ E_USER_ERROR,   'Fake error message', '/some/path/DOMTreeBuilder.php', '666', [], false ],
			[ E_USER_WARNING, 'Fake error message', '/some/path/SomeFile.php',       '666', [], false ],
			[ E_USER_NOTICE,  'Fake error message', '/some/path/DOMTreeBuilder.php', '666', [], false ],
		];
	}

	/**
	 * Test handle_parsing_errors.
	 *
	 * @covers ::handle_parsing_errors
	 *
	 * @dataProvider provide_handle_parsing_errors
	 *
	 * @param  int    $errno      Error type constant.
	 * @param  string $errstr     Error message.
	 * @param  string $errfile    File path.
	 * @param  string $errline    Line number.
	 * @param  array  $errcontext Stack context.
	 * @param  bool   $result     The expected result.
	 */
	public function test_handle_parsing_errors( $errno, $errstr, $errfile, $errline, $errcontext, $result ) {
		$typo = $this->typo;

		if ( $result ) {
			$this->assertTrue( $typo->handle_parsing_errors( $errno, $errstr, $errfile, $errline, $errcontext ) );
		} else {
			$this->assertFalse( $typo->handle_parsing_errors( $errno, $errstr, $errfile, $errline, $errcontext ) );
		}

		// Try again when we are not interested.
		$old_level = error_reporting( 0 );
		$this->assertTrue( $typo->handle_parsing_errors( $errno, $errstr, $errfile, $errline, $errcontext ) );
		error_reporting( $old_level );
	}

	/**
	 * Test get_prev_chr.
	 *
	 * @covers ::get_prev_chr
	 * @covers ::get_previous_textnode
	 */
	public function test_get_prev_chr() {
		$typo = $this->typo;

		$html = '<p><span>A</span><span id="foo">new hope.</span></p><p><span id="bar">The empire</span> strikes back.</p<';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( "//*[@id='foo']/text()" ); // really only one.
		$prev_char = $typo->get_prev_chr( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $prev_char );

		$textnodes = $xpath->query( "//*[@id='bar']/text()" ); // really only one.
		$prev_char = $typo->get_prev_chr( $textnodes->item( 0 ) );
		$this->assertSame( '', $prev_char );
	}

	/**
	 * Test get_previous_textnode.
	 *
	 * @covers ::get_previous_textnode
	 */
	public function test_get_previous_textnode_null() {
		$typo = $this->typo;

		$typo->process( '' );

		$node = $typo->get_previous_textnode( null );
		$this->assertNull( $node );
	}

	/**
	 * Test get_next_chr.
	 *
	 * @covers ::get_next_chr
	 * @covers ::get_next_textnode
	 */
	public function test_get_next_chr() {
		$typo = $this->typo;

		$html = '<p><span id="foo">A</span><span id="bar">new hope.</span></p><p><span>The empire</span> strikes back.</p<';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( "//*[@id='foo']/text()" ); // really only one.
		$prev_char = $typo->get_next_chr( $textnodes->item( 0 ) );
		$this->assertSame( 'n', $prev_char );

		$textnodes = $xpath->query( "//*[@id='bar']/text()" ); // really only one.
		$prev_char = $typo->get_next_chr( $textnodes->item( 0 ) );
		$this->assertSame( '', $prev_char );
	}

	/**
	 * Test get_next_textnode.
	 *
	 * @covers ::get_next_textnode
	 */
	public function test_get_next_textnode_null() {
		$typo = $this->typo;
		$typo->process( '' );

		$node = $typo->get_next_textnode( null );
		$this->assertNull( $node );
	}


	/**
	 * Test get_first_textnode.
	 *
	 * @covers ::get_first_textnode
	 */
	public function test_get_first_textnode() {
		$typo = $this->typo;

		$html = '<p><span id="foo">A</span><span id="bar">new hope.</span></p>';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( "//*[@id='foo']/text()" ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $node->nodeValue );

		$textnodes = $xpath->query( "//*[@id='foo']" ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $node->nodeValue );

		$textnodes = $xpath->query( "//*[@id='bar']" ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'new hope.', $node->nodeValue );

		$textnodes = $xpath->query( '//p' ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $node->nodeValue );
	}

	/**
	 * Test get_first_textnode.
	 *
	 * @covers ::get_first_textnode
	 */
	public function test_get_first_textnode_null() {
		$typo = $this->typo;
		$typo->process( '' );

		// Passing null returns null.
		$this->assertNull( $typo->get_first_textnode( null ) );

		// Passing a DOMNode that is not a DOMElement or a DOMText returns null as well.
		$this->assertNull( $typo->get_first_textnode( new \DOMDocument() ) );
	}

	/**
	 * Test get_first_textnode.
	 *
	 * @covers ::get_first_textnode
	 */
	public function test_get_first_textnode_only_block_level() {
		$typo = $this->typo;

		$html = '<div><div id="foo">No</div><div id="bar">hope</div></div>';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( '//div' ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertNull( $node );
	}

	/**
	 * Test get_last_textnode.
	 *
	 * @covers ::get_last_textnode
	 */
	public function test_get_last_textnode() {
		$typo = $this->typo;

		$html = '<p><span id="foo">A</span><span id="bar">new hope.</span> Really.</p>';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( "//*[@id='foo']/text()" ); // really only one.
		$node = $typo->get_last_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $node->nodeValue );

		$textnodes = $xpath->query( "//*[@id='foo']" ); // really only one.
		$node = $typo->get_last_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'A', $node->nodeValue );

		$textnodes = $xpath->query( "//*[@id='bar']" ); // really only one.
		$node = $typo->get_first_textnode( $textnodes->item( 0 ) );
		$this->assertSame( 'new hope.', $node->nodeValue );

		$textnodes = $xpath->query( '//p' ); // really only one.
		$node = $typo->get_last_textnode( $textnodes->item( 0 ) );
		$this->assertSame( ' Really.', $node->nodeValue );
	}

	/**
	 * Test get_last_textnode.
	 *
	 * @covers ::get_last_textnode
	 */
	public function test_get_last_textnode_null() {
		$typo = $this->typo;
		$typo->process( '' );

		// Passing null returns null.
		$this->assertNull( $typo->get_last_textnode( null ) );

		// Passing a DOMNode that is not a DOMElement or a DOMText returns null as well.
		$this->assertNull( $typo->get_last_textnode( new \DOMDocument() ) );
	}


	/**
	 * Test get_last_textnode.
	 *
	 * @covers ::get_last_textnode
	 */
	public function test_get_last_textnode_only_block_level() {
		$typo = $this->typo;

		$html = '<div><div id="foo">No</div><div id="bar">hope</div></div>';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$textnodes = $xpath->query( '//div' ); // really only one.
		$node = $typo->get_last_textnode( $textnodes->item( 0 ) );
		$this->assertNull( $node );
	}

	/**
	 * Provide data for testing smart_quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_data() {
		return array(
			[ '<span>"Double", \'single\'</span>', '<span>&ldquo;Double&rdquo;, &lsquo;single&rsquo;</span>' ],
			[ '<p>"<em>This is nuts.</em>"</p>',   '<p>&ldquo;<em>This is nuts.</em>&rdquo;</p>' ],
			[ '"This is so 1996", he said.',       '&ldquo;This is so 1996&rdquo;, he said.' ],
			[ '6\'5"',                             '6&prime;5&Prime;' ],
			[ '6\' 5"',                            '6&prime; 5&Prime;' ],
			[ '6\'&nbsp;5"',                       '6&prime;&nbsp;5&Prime;' ],
			[ " 6'' ",                             ' 6&Prime; ' ], // nobody uses this for quotes, so it should be OK to keep the primes here.
			[ 'ein 32"-Fernseher',                 'ein 32&Prime;-Fernseher' ],
			[ "der 8'-Ölbohrer",                   'der 8&prime;-&Ouml;lbohrer' ],
			[ "der 1/4'-Bohrer",                   'der 1/4&prime;-Bohrer' ],
			[ 'Hier 1" "Typ 2" einsetzen',         'Hier 1&Prime; &ldquo;Typ 2&rdquo; einsetzen' ],
			[ "2/4'",                              '2/4&prime;' ],
			[ '3/44"',                             '3/44&Prime;' ],
			array( '("Some" word',                      '(&ldquo;Some&rdquo; word' ),
		);
	}

	/**
	 * Test smart_quotes.
	 *
	 * @covers ::smart_quotes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Entity-escaped result string.
	 */
	public function test_smart_quotes( $html, $result ) {
		$typo = $this->typo;
		$typo->set_smart_quotes( true );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test smart_quotes.
	 *
	 * @covers ::smart_quotes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Entity-escaped result string.
	 */
	public function test_smart_quotes_off( $html, $result ) {
		$typo = $this->typo;
		$typo->set_smart_quotes( false );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Provide data for testing smart quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_special_data() {
		return array(
			array( '("Some" word', '(&raquo;Some&laquo; word', 'doubleGuillemetsReversed', 'singleGuillemetsReversed' ),
		);
	}

	/**
	 * Test smart_quotes.
	 *
	 * @covers ::smart_quotes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_quotes_special_data
	 *
	 * @param string $html      HTML input.
	 * @param string $result    Expected entity-escaped result.
	 * @param string $primary   Primary quote style.
	 * @param string $secondary Secondard  quote style.
	 */
	public function test_smart_quotes_special( $html, $result, $primary, $secondary ) {
		$typo = $this->typo;
		$typo->set_smart_quotes( true );
		$typo->set_smart_quotes_primary( $primary );
		$typo->set_smart_quotes_secondary( $secondary );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test smart_dashes.
	 *
	 * @covers ::smart_dashes
	 * @covers ::dash_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_smart_dashes_with_dash_spacing_off( $input, $result_us, $result_int ) {
		$typo = $this->typo;
		$typo->set_smart_dashes( true );
		$typo->set_dash_spacing( false );

		$typo->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $result_us, clean_html( $typo->process( $input ) ) );

		$typo->set_smart_dashes_style( 'international' );
		$this->assertSame( $result_int, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test smart_dashes.
	 *
	 * @covers ::smart_dashes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_smart_dashes_off( $input, $result_us, $result_int ) {
		$typo = $this->typo;
		$typo->set_smart_dashes( false );
		$typo->set_dash_spacing( false );

		$typo->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( clean_html( $input ), clean_html( $typo->process( $input ) ) );

		$typo->set_smart_dashes_style( 'international' );
		$this->assertSame( clean_html( $input ), clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing smart_ellipses.
	 *
	 * @return array
	 */
	public function provide_smart_ellipses_data() {
		return [
			[ 'Where are we going... Really....?', 'Where are we going&hellip; Really.&hellip;?' ],
		];
	}

	/**
	 * Test smart_ellipses.
	 *
	 * @covers ::smart_ellipses
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_ellipses_data
	 *
	 * @param string $input  HTML intput.
	 * @param string $result Expected result.
	 */
	public function test_smart_ellipses( $input, $result ) {
		$typo = $this->typo;
		$typo->set_smart_ellipses( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Test smart_ellipses.
	 *
	 * @covers ::smart_ellipses
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_ellipses_data
	 *
	 * @param string $input  HTML intput.
	 * @param string $result Ignored.
	 */
	public function test_smart_ellipses_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_smart_ellipses( false );

		$this->assertSame( $input, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing smart_diacritics.
	 *
	 * @return array
	 */
	public function provide_smart_diacritics_data() {
		return [
			[ '<p>creme brulee</p>', '<p>crème brûlée</p>', 'en-US' ],
			[ 'no diacritics to replace, except creme', 'no diacritics to replace, except crème', 'en-US' ],
			[ 'ne vs. seine vs einzelne', 'né vs. seine vs einzelne', 'de-DE' ],
			[ 'ne vs. sei&shy;ne vs einzelne', 'né vs. sei&shy;ne vs einzelne', 'de-DE' ],
		];
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @covers ::smart_diacritics
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 * @param string $lang   Language code.
	 */
	public function test_smart_diacritics( $html, $result, $lang ) {
		$typo = $this->typo;
		$typo->set_smart_diacritics( true );
		$typo->set_diacritic_language( $lang );

		$this->assertSame( clean_html( $result ), clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @covers ::smart_diacritics
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Ignored.
	 * @param string $lang   Language code.
	 */
	public function test_smart_diacritics_off( $html, $result, $lang ) {
		$typo = $this->typo;
		$typo->set_smart_diacritics( false );
		$typo->set_diacritic_language( $lang );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Provide data for testing smart_diacritics.
	 *
	 * @return array
	 */
	public function provide_smart_diacritics_error_in_pattern_data() {
		return [
			[ 'no diacritics to replace, except creme', 'en-US', 'creme' ],
		];
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @covers ::smart_diacritics
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_diacritics_error_in_pattern_data
	 *
	 * @param string $html   HTML input.
	 * @param string $lang   Language code.
	 * @param string $unset  Replacement to unset.
	 */
	public function test_smart_diacritics_error_in_pattern( $html, $lang, $unset ) {
		$typo = $this->typo;

		$typo->set_smart_diacritics( true );
		$typo->set_diacritic_language( $lang );
		$s = $typo->get_settings();

		$replacements = $s['diacriticReplacement'];
		unset( $replacements['replacements'][ $unset ] );
		$s['diacriticReplacement'] = $replacements;

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html, false, $s ) ) );
	}

	/**
	 * Provide data for testing smart_marks.
	 *
	 * @return array
	 */
	public function provide_smart_marks_data() {
		return [
			[ '(c)',  '&copy;' ],
			[ '(C)',  '&copy;' ],
			[ '(r)',  '&reg;' ],
			[ '(R)',  '&reg;' ],
			[ '(p)',  '&#8471;' ],
			[ '(P)',  '&#8471;' ],
			[ '(sm)', '&#8480;' ],
			[ '(SM)', '&#8480;' ],
			[ '(tm)', '&trade;' ],
			[ '(TM)', '&trade;' ],
			[ '501(c)(1)', '501(c)(1)' ],      // protected.
			[ '501(c)(29)', '501(c)(29)' ],    // protected.
			[ '501(c)(30)', '501&copy;(30)' ], // not protected.
		];
	}

	/**
	 * Test smart_marks.
	 *
	 * @covers ::smart_marks
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_marks_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_smart_marks( $input, $result ) {
		$typo = $this->typo;
		$typo->set_smart_marks( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Test smart_marks.
	 *
	 * @covers ::smart_marks
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_marks_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Ignored.
	 */
	public function test_smart_marks_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_smart_marks( false );

		$this->assertSame( $input, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Data provider for smarth_math test.
	 */
	public function provide_smart_math_data() {
		return [
			[ 'xx 7&minus;3=4 xx',              'xx 7-3=4 xx',      true ],
			[ 'xx 3&times;3=5&divide;2 xx',     'xx 3*3=5/2 xx',    true ],
			[ 'xx 0815-4711 xx',                'xx 0815-4711 xx',  true ],
			[ 'xx 1/2 xx',                      'xx 1/2 xx',        true ],
			[ 'xx 2001-13-12 xx',               'xx 2001-13-12 xx', false ], // not a valid date.
			[ 'xx 2001-12-13 xx',               'xx 2001-12-13 xx', true ],
			[ 'xx 2001-13-13 xx',               'xx 2001-13-13 xx', false ], // not a valid date.
			[ 'xx 13-12-2002 xx',               'xx 13-12-2002 xx', true ],
			[ 'xx 12-13-2002 xx',               'xx 12-13-2002 xx', true ],
			[ 'xx 13-13-2002 xx',               'xx 13-13-2002 xx', false ], // not a valid date.
			[ 'xx 2001-12 xx',                  'xx 2001-12 xx',    true ],
			[ 'xx 2001-13 xx',                  'xx 2001-13 xx',    true ], // apparently a valid day count.
			[ 'xx 2001-100 xx',                 'xx 2001-100 xx',   true ],
			[ 'xx 12/13/2010 xx',               'xx 12/13/2010 xx', true ],
			[ 'xx 13/12/2010 xx',               'xx 13/12/2010 xx', true ],
			[ 'xx 13&divide;13&divide;2010 xx', 'xx 13/13/2010 xx', true ], // not a valid date.
		];
	}

	/**
	 * Test smart_math.
	 *
	 * @covers ::smart_math
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_math_data
	 *
	 * @param string $result Expected result.
	 * @param string $input  HTML input.
	 * @param bool   $same   Result expected to be the same or not the same.
	 */
	public function test_smart_math( $result, $input, $same ) {
		$typo = $this->typo;
		$typo->set_smart_math( true );

		if ( $same ) {
			$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
		} else {
			$this->assertNotSame( $result, clean_html( $typo->process( $input ) ) );
		}
	}

	/**
	 * Test smart_math.
	 *
	 * @covers ::smart_math
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_math_data
	 *
	 * @param string $result Ignored.
	 * @param string $input  HTML input.
	 * @param bool   $same   Ignored.
	 */
	public function test_smart_math_off( $result, $input, $same ) {
		$typo = $this->typo;
		$typo->set_smart_math( false );

		$this->assertSame( $input, $typo->process( $input ) );
	}

	/**
	 * Test smart_exponents.
	 *
	 * @covers ::smart_exponents
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_smart_exponents() {
		$typo = $this->typo;
		$typo->set_smart_exponents( true );

		$this->assertSame( '10<sup>12</sup>', $typo->process( '10^12' ) );
	}

	/**
	 * Test smart_exponents.
	 *
	 * @covers ::smart_exponents
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_smart_exponents_off() {
		$typo = $this->typo;
		$typo->set_smart_exponents( false );

		$this->assertSame( '10^12', $typo->process( '10^12' ) );
	}

	/**
	 * Provide data for testing smart_fractions.
	 *
	 * @return array
	 */
	public function provide_smart_fractions_data() {
		return [
			[
				'1/2 3/300 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub> <sup>3</sup>&frasl;<sub>300</sub> <sup>999</sup>&frasl;<sub>1000</sub>',
				'<sup>1</sup>&frasl;<sub>2</sub>&#8239;<sup>3</sup>&frasl;<sub>300</sub> <sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup>1</sup>&frasl;<sub>2</sub> 4/2015 1999/2000 <sup>999</sup>&frasl;<sub>1000</sub>',
				'<sup>1</sup>&frasl;<sub>2</sub>&#8239;4/2015 1999/2000&#8239;<sup>999</sup>&frasl;<sub>1000</sub>',
				'',
				'',
			],
			[
				'1/2 3/300 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub> <sup class="num">3</sup>&frasl;<sub class="denom">300</sub> <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&#8239;<sup class="num">3</sup>&frasl;<sub class="denom">300</sub> <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub> 4/2015 1999/2000 <sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&#8239;4/2015 1999/2000&#8239;<sup class="num">999</sup>&frasl;<sub class="denom">1000</sub>',
				'num',
				'denom',
			],
		];
	}

	/**
	 * Test smart_fractions.
	 *
	 * @covers ::smart_fractions
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_fractions_data
	 *
	 * @param string $input           HTML input.
	 * @param string $result          Expected result.
	 * @param string $result_spacing  Expected result with spacing enabled.
	 * @param string $num_css_class   CSS class for numerator.
	 * @param string $denom_css_class CSS class for denominator.
	 */
	public function test_smart_fractions( $input, $result, $result_spacing, $num_css_class, $denom_css_class ) {
		$typo = new PHP_Typography_CSS_Classes( false, 'now', [
			'numerator'   => $num_css_class,
			'denominator' => $denom_css_class,
		] );
		$typo->set_smart_fractions( true );
		$typo->set_true_no_break_narrow_space( true );

		$typo->set_fraction_spacing( false );
		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );

		$typo->set_fraction_spacing( true );
		$this->assertSame( $result_spacing, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test smart_fractions.
	 *
	 * @covers ::smart_fractions
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_fractions_data
	 *
	 * @param string $input           HTML input.
	 * @param string $result          Expected result.
	 * @param string $result_spacing  Expected result with spacing enabled.
	 * @param string $num_css_class   CSS class for numerator.
	 * @param string $denom_css_class CSS class for denominator.
	 */
	public function test_smart_fractions_off( $input, $result, $result_spacing, $num_css_class, $denom_css_class ) {
		$typo = new PHP_Typography_CSS_Classes( false, 'now', [
			'numerator'   => $num_css_class,
			'denominator' => $denom_css_class,
		] );
		$typo->set_smart_fractions( false );
		$typo->set_fraction_spacing( false );

		$this->assertSame( clean_html( $input ), clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing smart_fractions and smart_quotes together.
	 *
	 * @return array
	 */
	public function provide_smart_fractions_smart_quotes_data() {
		return [
			[
				'1/2" 1/2\' 3/4″',
				'<sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&Prime; <sup class="num">1</sup>&frasl;<sub class="denom">2</sub>&prime; <sup class="num">3</sup>&frasl;<sub class="denom">4</sub>&Prime;',
				'num',
				'denom',
			],
		];
	}

	/**
	 * Test smart_fractions.
	 *
	 * @covers ::smart_fractions
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_fractions_smart_quotes_data
	 *
	 * @param string $input           HTML input.
	 * @param string $result          Expected result.
	 * @param string $num_css_class   CSS class for numerator.
	 * @param string $denom_css_class CSS class for denominator.
	 */
	public function test_smart_fractions_with_smart_quotes( $input, $result, $num_css_class, $denom_css_class ) {
		$typo = new PHP_Typography_CSS_Classes( false, 'now', [
			'numerator'   => $num_css_class,
			'denominator' => $denom_css_class,
		] );
		$typo->set_smart_fractions( true );
		$typo->set_smart_quotes( true );
		$typo->set_true_no_break_narrow_space( true );
		$typo->set_fraction_spacing( false );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing fraction spacing.
	 *
	 * @return array
	 */
	public function provide_fraction_spacing_data() {
		return [
			[
				'1/2 3/300 999/1000',
				'1/2&nbsp;3/300 999/1000',
			],
			[
				'1/2 4/2015 1999/2000 999/1000',
				'1/2&nbsp;4/2015 1999/2000&nbsp;999/1000',
			],
		];
	}


	/**
	 * Test smart_fractions.
	 *
	 * @covers ::smart_fractions
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_fraction_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_smart_fractions_only_spacing( $input, $result ) {
		$typo = $this->typo;
		$typo->set_smart_fractions( false );
		$typo->set_fraction_spacing( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing ordinal suffixes.
	 *
	 * @return array
	 */
	public function provide_smart_ordinal_suffix() {
		return [
			[ 'in the 1st instance',      'in the 1<sup>st</sup> instance', '' ],
			[ 'in the 2nd degree',        'in the 2<sup>nd</sup> degree',   '' ],
			[ 'a 3rd party',              'a 3<sup>rd</sup> party',         '' ],
			[ '12th Night',               '12<sup>th</sup> Night',          '' ],
			[ 'in the 1st instance, we',  'in the 1<sup class="ordinal">st</sup> instance, we',  'ordinal' ],
			[ 'murder in the 2nd degree', 'murder in the 2<sup class="ordinal">nd</sup> degree', 'ordinal' ],
			[ 'a 3rd party',              'a 3<sup class="ordinal">rd</sup> party',              'ordinal' ],
			[ 'the 12th Night',           'the 12<sup class="ordinal">th</sup> Night',           'ordinal' ],
		];
	}

	/**
	 * Test smart_ordinal_suffix.
	 *
	 * @covers ::smart_ordinal_suffix
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_ordinal_suffix
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class CSS class for ordinal suffix.
	 */
	public function test_smart_ordinal_suffix( $input, $result, $css_class ) {
		$typo = new PHP_Typography_CSS_Classes( false, 'now', [
			'ordinal' => $css_class,
		] );
		$typo->set_smart_ordinal_suffix( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test smart_ordinal_suffix.
	 *
	 * @covers ::smart_ordinal_suffix
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_smart_ordinal_suffix
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class CSS class for ordinal suffix.
	 */
	public function test_smart_ordinal_suffix_off( $input, $result, $css_class ) {
		$typo = new PHP_Typography_CSS_Classes( false, 'now', [
			'ordinal' => $css_class,
		] );
		$typo->set_smart_ordinal_suffix( false );

		$this->assertSame( $input, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing single character word spacing.
	 *
	 * @return array
	 */
	public function provide_single_character_word_spacing_data() {
		return [
			[ 'A cat in a tree', 'A cat in a&nbsp;tree' ],
			[ 'Works with strange characters like ä too. But not Ä or does it?', 'Works with strange characters like &auml;&nbsp;too. But not &Auml;&nbsp;or does it?' ],
			[ 'Should work even here: <span>a word</span> does not want to be alone.', 'Should work even here: <span>a&nbsp;word</span> does not want to be alone.' ],
			[ 'And here:<span> </span>a word does not want to be alone.', 'And here:<span> </span>a&nbsp;word does not want to be alone.' ],
		];
	}

	/**
	 * Test single_character_word_spacing.
	 *
	 * @covers ::single_character_word_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_single_character_word_spacing( $input, $result ) {
		$typo = $this->typo;
		$typo->set_single_character_word_spacing( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test single_character_word_spacing.
	 *
	 * @covers ::single_character_word_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_single_character_word_spacing_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_single_character_word_spacing( false );

		$this->assertSame( $input, $typo->process( $input ) );
	}

	/**
	 * Provide data for testing dash spacing.
	 *
	 * @return array
	 */
	public function provide_dash_spacing_data() {
		return [
			[
				'Ein - mehr oder weniger - guter Gedanke, 1908-2008',
				'Ein&thinsp;&mdash;&thinsp;mehr oder weniger&thinsp;&mdash;&thinsp;guter Gedanke, 1908&thinsp;&ndash;&thinsp;2008',
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&#8202;&ndash;&#8202;2008',
			],
			[
				"We just don't know --- really---, but you know, --",
				"We just don't know&thinsp;&mdash;&thinsp;really&thinsp;&mdash;&thinsp;, but you know, &ndash;",
				"We just don't know&#8202;&mdash;&#8202;really&#8202;&mdash;&#8202;, but you know, &ndash;",
			],
			[
				'Auch 3.-8. März sollte die - richtigen - Gedankenstriche verwenden.',
				'Auch 3.&thinsp;&ndash;&thinsp;8. M&auml;rz sollte die&thinsp;&mdash;&thinsp;richtigen&thinsp;&mdash;&thinsp;Gedankenstriche verwenden.',
				'Auch 3.&#8202;&ndash;&#8202;8. M&auml;rz sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
			],
		];
	}

	/**
	 * Provide data for testing smart dashes.
	 *
	 * @return array
	 */
	public function provide_smart_dashes_data() {
		return [
			[
				'Ein - mehr oder weniger - guter Gedanke, 1908-2008',
				'Ein &mdash; mehr oder weniger &mdash; guter Gedanke, 1908&ndash;2008',
				'Ein &ndash; mehr oder weniger &ndash; guter Gedanke, 1908&ndash;2008',
			],
			[
				"We just don't know --- really---, but you know, --",
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
				"We just don't know &mdash; really&mdash;, but you know, &ndash;",
			],
			[
				'что природа жизни - это Блаженство',
				'что природа жизни &mdash; это Блаженство',
				'что природа жизни &ndash; это Блаженство',
			],
			[
				'Auch 3.-8. März sollte die - richtigen - Gedankenstriche verwenden.',
				'Auch 3.&ndash;8. M&auml;rz sollte die &mdash; richtigen &mdash; Gedankenstriche verwenden.',
				'Auch 3.&ndash;8. M&auml;rz sollte die &ndash; richtigen &ndash; Gedankenstriche verwenden.',
			],
			[
				'20.-30.',
				'20.&ndash;30.',
				'20.&ndash;30.',
			],
		];
	}

	/**
	 * Provide data for testing smart dashes (where hyphen should not be changed).
	 *
	 * @return array
	 */
	public function provide_dash_spacing_unchanged_data() {
		return [
			[ 'Vor- und Nachteile, i-Tüpfelchen, 100-jährig, Fritz-Walter-Stadion, 2015-12-03, 01-01-1999, 2012-04' ],
			[ 'Bananen-Milch und -Brot' ],
			[ 'pick-me-up' ],
			[ 'You may see a yield that is two-, three-, or fourfold.' ],
		];
	}


	/**
	 * Test dash_spacing.
	 *
	 * @covers ::dash_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_dash_spacing_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_dash_spacing( $input, $result_us, $result_int ) {
		$typo = $this->typo;
		$typo->set_smart_dashes( true );
		$typo->set_dash_spacing( true );

		$typo->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $result_us, clean_html( $typo->process( $input ) ) );

		$typo->set_smart_dashes_style( 'international' );
		$this->assertSame( $result_int, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test dash_spacing.
	 *
	 * @covers ::dash_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_dash_spacing_unchanged_data
	 *
	 * @param string $input  HTML input.
	 */
	public function test_dash_spacing_unchanged( $input ) {
		$typo = $this->typo;
		$typo->set_smart_dashes( true );
		$typo->set_dash_spacing( true );

		$typo->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $input, $typo->process( $input ) );

		$typo->set_smart_dashes_style( 'international' );
		$this->assertSame( $input, $typo->process( $input ) );
	}

	/**
	 * Provide data for special white space collapsing.
	 *
	 * @return array
	 */
	public function provide_space_collapse_data() {
		return [
			[ 'A  new hope&nbsp;  arises.', 'A new hope&nbsp;arises.' ],
			[ 'A &thinsp;new hope &nbsp;  arises.', 'A&thinsp;new hope&nbsp;arises.' ],
			[ '<p>  &nbsp;A  new hope&nbsp;  arises.</p>', '<p>A new hope&nbsp;arises.</p>' ],
		];
	}


	/**
	 * Test space_collapse.
	 *
	 * @covers ::space_collapse
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_space_collapse_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_space_collapse( $input, $result ) {
		$typo = $this->typo;
		$typo->set_space_collapse( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test space_collapse.
	 *
	 * @covers ::space_collapse
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_space_collapse_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_space_collapse_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_space_collapse( false );

		$this->assertSame( $input, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing unit_spacing.
	 *
	 * @return array
	 */
	public function provide_unit_spacing_data() {
		return [
			[ 'It was 2 m from', 'It was 2&#8239;m from' ],
			[ '3 km/h', '3&#8239;km/h' ],
			[ '5 sg 44 kg', '5 sg 44&#8239;kg' ],
			[ '100 &deg;C', '100&#8239;&deg;C' ],
		];
	}


	/**
	 * Test unit_spacing.
	 *
	 * @covers ::unit_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_unit_spacing( $input, $result ) {
		$typo = $this->typo;
		$typo->set_unit_spacing( true );
		$typo->set_true_no_break_narrow_space( true );

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test unit_spacing.
	 *
	 * @covers ::unit_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_unit_spacing_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_unit_spacing( false );

		$this->assertSame( $input, clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing French punctuation rules.
	 *
	 * @return array
	 */
	public function provide_french_punctuation_spacing_data() {
		return [
			[ "Je t'aime ; m'aimes-tu ?", "Je t'aime&#8239;; m'aimes-tu&#8239;?", false ],
			[ "Je t'aime; m'aimes-tu?", "Je t'aime&#8239;; m'aimes-tu&#8239;?", false ],
			[ 'Au secours !', 'Au secours&#8239;!', false ],
			[ 'Au secours!', 'Au secours&#8239;!', false ],
			[ 'Jean a dit : Foo', 'Jean a dit&nbsp;: Foo', false ],
			[ 'Jean a dit: Foo', 'Jean a dit&nbsp;: Foo', false ],
			[ 'http://example.org', 'http://example.org', false ],
			[ 'foo &Ouml; & ; bar', 'foo &Ouml; &amp; ; bar', false ],
			[ '5 > 3', '5 > 3', false ],
			[ 'Les « courants de bord ouest » du Pacifique ? Eh bien : ils sont "fabuleux".', 'Les &laquo;&#8239;courants de bord ouest&#8239;&raquo; du Pacifique&#8239;? Eh bien&nbsp;: ils sont "fabuleux".', false ],
			[ '"diabète de type 3"', '&laquo;&#8239;diab&egrave;te de type 3&#8239;&raquo;', true ],
			[ '« Hello, this is a sentence. »', '&laquo;&#8239;Hello, this is a sentence.&#8239;&raquo;', false ],
			[ 'À «programmer»?', '&Agrave; &laquo;&#8239;programmer&#8239;&raquo;&#8239;?', false ],
			[ 'À "programmer"?', '&Agrave; &laquo;&#8239;programmer&#8239;&raquo;&#8239;?', true ],
			[ 'À "programmer":', '&Agrave; &laquo;&#8239;programmer&#8239;&raquo;&nbsp;:', true ],
		];
	}


	/**
	 * Test french_punctuation_spacing.
	 *
	 * @covers ::french_punctuation_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses ::set_smart_quotes
	 * @uses ::set_smart_quotes_primary
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input             HTML input.
	 * @param string $result            Expected result.
	 * @param bool   $use_french_quotes Enable French primary quotes style.
	 */
	public function test_french_punctuation_spacing( $input, $result, $use_french_quotes ) {
		$typo = $this->typo;
		$typo->set_french_punctuation_spacing( true );
		$typo->set_true_no_break_narrow_space( true );

		if ( $use_french_quotes ) {
			$typo->set_smart_quotes_primary( 'doubleGuillemetsFrench' );
			$typo->set_smart_quotes( true );
		}

		$this->assertSame( $result, clean_html( $typo->process( $input ) ) );
	}


	/**
	 * Test french_punctuation_spacing.
	 *
	 * @covers ::french_punctuation_spacing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_french_punctuation_spacing_off( $input, $result ) {
		$typo = $this->typo;
		$typo->set_french_punctuation_spacing( false );

		$this->assertSame( clean_html( $input ), clean_html( $typo->process( $input ) ) );
	}

	/**
	 * Provide data for testing wrap_hard_hyphens.
	 *
	 * @return array
	 */
	public function provide_wrap_hard_hyphens_data() {
		return [
			[ 'This-is-a-hyphenated-word', 'This-&#8203;is-&#8203;a-&#8203;hyphenated-&#8203;word' ],
			[ 'This-is-a-hyphenated-', 'This-&#8203;is-&#8203;a-&#8203;hyphenated-' ],

		];
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @covers ::wrap_hard_hyphens
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_hard_hyphens( $input, $result ) {
		$typo = $this->typo;
		$typo->process( '' );
		$typo->set_wrap_hard_hyphens( true );
		$s = $typo->get_settings();

		$this->assertTokensSame( $result, $typo->wrap_hard_hyphens( $this->tokenize( $input ), $s ) );
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @covers ::wrap_hard_hyphens
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_hard_hyphens_with_smart_dashes( $input, $result ) {
		$typo = $this->typo;
		$typo->process( '' );
		$typo->set_wrap_hard_hyphens( true );
		$typo->set_smart_dashes( true );
		$s = $typo->get_settings();

		$this->assertTokensSame( $result, $typo->wrap_hard_hyphens( $this->tokenize( $input ), $s ) );
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @covers ::wrap_hard_hyphens
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_hard_hyphens_off( $input, $result ) {
		$typo = $this->typo;
		$typo->process( '' );
		$typo->set_wrap_hard_hyphens( false );
		$s = $typo->get_settings();

		$this->assertTokensSame( $input, $typo->wrap_hard_hyphens( $this->tokenize( $input ), $s ) );
	}

	/**
	 * Provide data for testing dewidowing.
	 *
	 * @return array
	 */
	public function provide_dewidow_data() {
		return [
			[ 'bla foo b', 'bla foo&nbsp;b', 3, 2 ],
			[ 'bla foo&thinsp;b', 'bla foo&thinsp;b', 3, 2 ], // don't replace thin space...
			[ 'bla foo&#8202;b', 'bla foo&#8202;b', 3, 2 ],   // ... or hair space.
			[ 'bla foo bar', 'bla foo bar', 2, 2 ],
			[ 'bla foo bär...', 'bla foo&nbsp;b&auml;r...', 3, 3 ],
			[ 'bla foo&nbsp;bär...', 'bla foo&nbsp;b&auml;r...', 3, 3 ],
			[ 'bla föö&#8203;bar s', 'bla f&ouml;&ouml;&#8203;bar&nbsp;s', 3, 2 ],
			[ 'bla foo&#8203;bar s', 'bla foo&#8203;bar s', 2, 2 ],
			[ 'bla foo&shy;bar', 'bla foo&shy;bar', 3, 3 ], // &shy; not matched.
			[ 'bla foo&shy;bar bar', 'bla foo&shy;bar&nbsp;bar', 3, 3 ], // &shy; not matched, but syllable after is.
			[ 'bla foo&#8203;bar bar', 'bla foo&#8203;bar&nbsp;bar', 3, 3 ],
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 3, 3 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
		];
	}

	/**
	 * Provide data for testing dewidowing.
	 *
	 * @return array
	 */
	public function provide_dewidow_with_hyphenation_data() {
		return [
			[ 'this is fucking ri...', 'this is fuck&shy;ing&nbsp;ri...', 4, 2 ],
			[ 'this is fucking fucking', 'this is fuck&shy;ing fuck&shy;ing', 4, 2 ],
		];
	}

	/**
	 * Test dewidow.
	 *
	 * @covers ::dewidow
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $max_pull   Maximum number of pulled characters.
	 * @param int    $max_length Maximum word length for dewidowing.
	 */
	public function test_dewidow( $html, $result, $max_pull, $max_length ) {
		$typo = $this->typo;
		$typo->set_dewidow( true );
		$typo->set_max_dewidow_pull( $max_pull );
		$typo->set_max_dewidow_length( $max_length );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test dewidow.
	 *
	 * @covers ::dewidow
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Hyphenator
	 *
	 * @dataProvider provide_dewidow_with_hyphenation_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $max_pull   Maximum number of pulled characters.
	 * @param int    $max_length Maximum word length for dewidowing.
	 */
	public function test_dewidow_with_hyphenation( $html, $result, $max_pull, $max_length ) {
		$typo = $this->typo;
		$typo->set_dewidow( true );
		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'en-US' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_max_dewidow_pull( $max_pull );
		$typo->set_max_dewidow_length( $max_length );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test dewidow.
	 *
	 * @covers ::dewidow
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $max_pull   Maximum number of pulled characters.
	 * @param int    $max_length Maximum word length for dewidowing.
	 */
	public function test_dewidow_off( $html, $result, $max_pull, $max_length ) {
		$typo = $this->typo;
		$typo->set_dewidow( false );
		$typo->set_max_dewidow_pull( $max_pull );
		$typo->set_max_dewidow_length( $max_length );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
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
	 * Test wrap_urls.
	 *
	 * @covers ::wrap_urls
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_urls_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $min_after  Minimum number of characters after URL wrapping.
	 */
	public function test_wrap_urls( $html, $result, $min_after ) {
		$typo = $this->typo;
		$typo->set_url_wrap( true );
		$typo->set_min_after_url_wrap( $min_after );
		$s = $typo->get_settings();

		$this->assertTokensSame( $result, $typo->wrap_urls( $this->tokenize( $html ), $s ) );
	}


	/**
	 * Test wrap_urls.
	 *
	 * @covers ::wrap_urls
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_urls_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $min_after  Minimum number of characters after URL wrapping.
	 */
	public function test_wrap_urls_off( $html, $result, $min_after ) {
		$typo = $this->typo;
		$typo->set_url_wrap( false );
		$typo->set_min_after_url_wrap( $min_after );
		$s = $typo->get_settings();

		$this->assertTokensSame( $html, $typo->wrap_urls( $this->tokenize( $html ), $s ) );
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
			[ 'funny123@summer1.org',     'funny1&#8203;2&#8203;3&#8203;@&#8203;summer1&#8203;.&#8203;org' ],
		];
	}

	/**
	 * Test wrap_emails.
	 *
	 * @covers ::wrap_emails
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_emails( $html, $result ) {
		$typo = $this->typo;
		$typo->set_email_wrap( true );
		$s = $typo->get_settings();

		$this->assertTokensSame( $result, $typo->wrap_emails( $this->tokenize( $html ), $s ) );
	}


	/**
	 * Test wrap_emails.
	 *
	 * @covers ::wrap_emails
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_emails_off( $html, $result ) {
		$typo = $this->typo;
		$typo->set_email_wrap( false );
		$s = $typo->get_settings();

		$this->assertTokensSame( $html, $typo->wrap_emails( $this->tokenize( $html ), $s ) );
	}

	/**
	 * Provide data for testing caps styling.
	 *
	 * @return array
	 */
	public function provide_style_caps_data() {
		return [
			[ 'foo BAR bar', 'foo <span class="caps">BAR</span> bar' ],
			[ 'foo BARbaz', 'foo BARbaz' ],
			[ 'foo BAR123 baz', 'foo <span class="caps">BAR123</span> baz' ],
			[ 'foo 123BAR baz', 'foo <span class="caps">123BAR</span> baz' ],
		];
	}

	/**
	 * Test style_caps.
	 *
	 * @covers ::style_caps
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_caps( true );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test style_caps.
	 *
	 * @covers ::style_caps
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps_off( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_caps( false );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test replace_node_with_html.
	 *
	 * @covers ::replace_node_with_html
	 */
	public function test_replace_node_with_html() {
		$typo = $this->typo;
		$s = $typo->get_settings();
		$dom = $typo->parse_html( $typo->get_html5_parser(), '<p>foo</p>', $s );

		$this->assertInstanceOf( '\DOMDocument', $dom );
		$original_node = $dom->getElementsByTagName( 'p' )->item( 0 );
		$parent        = $original_node->parentNode;
		$new_nodes     = $typo->replace_node_with_html( $original_node, '<div><span>bar</span></div>' );

		$this->assertTrue( is_array( $new_nodes ) );
		$this->assertContainsOnlyInstancesOf( '\DOMNode', $new_nodes );
		foreach ( $new_nodes as $node ) {
			$this->assertSame( $parent, $node->parentNode );
		}
	}


	/**
	 * Test replace_node_with_html.
	 *
	 * @covers ::replace_node_with_html
	 */
	public function test_replace_node_with_html_invalid() {
		$typo = $this->typo;

		$node = new \DOMText( 'foo' );
		$new_node = $typo->replace_node_with_html( $node, 'bar' );

		// Without a parent node, it's not possible to replace anything.
		$this->assertSame( $node, $new_node );
	}

	/**
	 * Provide data for testing style_numbers.
	 *
	 * @return array
	 */
	public function provide_style_numbers_data() {
		return [
			[ 'foo 123 bar', 'foo <span class="numbers">123</span> bar' ],
			[ 'foo 123bar baz', 'foo <span class="numbers">123</span>bar baz' ],
			[ 'foo bar123 baz', 'foo bar<span class="numbers">123</span> baz' ],
			[ 'foo 123BAR baz', 'foo <span class="numbers">123</span>BAR baz' ],
		];
	}


	/**
	 * Test style_numbers.
	 *
	 * @covers ::style_numbers
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_numbers( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_numbers( true );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test style_numbers.
	 *
	 * @covers ::style_numbers
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_numbers_off( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_numbers( false );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Provide data for testing the injection of CSS hooks.
	 *
	 * @return array
	 */
	public function provide_style_caps_and_numbers_data() {
		return [
			[ 'foo 123 BAR', 'foo <span class="numbers">123</span> <span class="caps">BAR</span>' ],
			[ 'FOO-BAR', '<span class="caps">FOO-BAR</span>' ],
			[ 'foo 123-BAR baz', 'foo <span class="caps"><span class="numbers">123</span>-BAR</span> baz' ],
			[ 'foo BAR123 baz', 'foo <span class="caps">BAR<span class="numbers">123</span></span> baz' ],
			[ 'foo 123BAR baz', 'foo <span class="caps"><span class="numbers">123</span>BAR</span> baz' ],
		];
	}

	/**
	 * Test styling caps and numbers at the same time.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_caps_and_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps_and_numbers( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_caps( true );
		$typo->set_style_numbers( true );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Provide data for testing stye_hanging_punctuation.
	 *
	 * @return array
	 */
	public function provide_style_hanging_punctuation_data() {
		return [
			[ '"First "second "third.', '<span class="pull-double">"</span>First <span class="push-double"></span>&#8203;<span class="pull-double">"</span>second <span class="push-double"></span>&#8203;<span class="pull-double">"</span>third.' ],
			[ '<span>"only pull"</span><span>"push & pull"</span>', '<span><span class="pull-double">"</span>only pull"</span><span><span class="push-double"></span>&#8203;<span class="pull-double">"</span>push &amp; pull"</span>' ],
			[ '<p><span>"Pull"</span> <span>\'Single Push\'</span></p>', '<p><span><span class="pull-double">"</span>Pull"</span> <span><span class="push-single"></span>&#8203;<span class="pull-single">\'</span>Single Push\'</span></p>' ],
		];
	}

	/**
	 * Test style_hanging_punctuation.
	 *
	 * @covers ::style_hanging_punctuation
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_hanging_punctuation( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_hanging_punctuation( true );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test style_hanging_punctuation.
	 *
	 * @covers ::style_hanging_punctuation
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_hanging_punctuation_off( $html, $result ) {
		$typo = $this->typo;
		$typo->set_style_hanging_punctuation( false );

		$this->assertSame( clean_html( $html ), clean_html( $typo->process( $html ) ) );
	}


	/**
	 * Test style_ampersands.
	 *
	 * @covers ::style_ampersands
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_style_ampersands() {
		$typo = $this->typo;
		$typo->set_style_ampersands( true );

		$this->assertSame( 'foo <span class="amp">&amp;</span> bar', clean_html( $typo->process( 'foo & bar' ) ) );
	}


	/**
	 * Test style_ampersands.
	 *
	 * @covers ::style_ampersands
	 *
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_style_ampersands_off() {
		$typo = $this->typo;
		$typo->set_style_ampersands( false );

		$this->assertSame( 'foo &amp; bar', clean_html( $typo->process( 'foo & bar' ) ) );
	}

	/**
	 * Provide data for testing initial quotes' styling.
	 *
	 * @return array
	 */
	public function provide_style_initial_quotes_data() {
		return [
			[ '<p>no quote</p>', '<p>no quote</p>', false ],
			[ '<p>"double quote"</p>', '<p><span class="dquo">"</span>double quote"</p>', false ],
			[ "<p>'single quote'</p>", "<p><span class=\"quo\">'</span>single quote'</p>", false ],
			[ '"no title quote"', '"no title quote"', false ],
			[ '"title quote"', '<span class="dquo">"</span>title quote"', true ],
		];
	}

	/**
	 * Test style_initial_quotes.
	 *
	 * @covers ::style_initial_quotes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $html     HTML input.
	 * @param string $result   Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_style_initial_quotes( $html, $result, $is_title ) {
		$typo = $this->typo;
		$typo->set_style_initial_quotes( true );
		$typo->set_initial_quote_tags();

		$this->assertSame( $result, clean_html( $typo->process( $html, $is_title ) ) );
	}


	/**
	 * Test style_initial_quotes.
	 *
	 * @covers ::style_initial_quotes
	 *
	 * @uses PHP_Typography\Text_Parser
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $html     HTML input.
	 * @param string $result   Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_style_initial_quotes_off( $html, $result, $is_title ) {
		$typo = $this->typo;
		$typo->set_style_initial_quotes( false );
		$typo->set_initial_quote_tags();

		$this->assertSame( $html, $typo->process( $html, $is_title ) );
	}

	/**
	 * Provide data for testing hyphenation.
	 *
	 * @return array
	 */
	public function provide_hyphenate_data() {
		return [
			[ 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', 'A few words to hy&shy;phen&shy;ate, like KING&shy;desk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!', 'en-US', true, true, true, false ],
			[ 'Sauerstofffeldflasche', 'Sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche', 'de', true, true, true, false ],
			[ 'Sauerstoff-Feldflasche', 'Sau&shy;er&shy;stoff-Feld&shy;fla&shy;sche', 'de', true, true, true, true ],
			[ 'Sauerstoff-Feldflasche', 'Sauerstoff-Feldflasche', 'de', true, true, true, false ],
			[ 'Geschäftsübernahme', 'Ge&shy;sch&auml;fts&shy;&uuml;ber&shy;nah&shy;me', 'de', true, true, true, false ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', 'de', true, true, true, false ],
		];
	}


	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Hyphenator
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $html                 HTML input.
	 * @param string $result               Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 * @param bool   $hyphenate_compunds   Hyphenate compound-words.
	 */
	public function test_hyphenate_off( $html, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds ) {
		$typo = $this->typo;
		$typo->set_hyphenation( false );
		$typo->set_hyphenation_language( $lang );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( $hyphenate_headings );
		$typo->set_hyphenate_all_caps( $hyphenate_all_caps );
		$typo->set_hyphenate_title_case( $hyphenate_title_case );
		$typo->set_hyphenate_compounds( $hyphenate_compunds );
		$typo->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertSame( $html, $typo->process( $html ) );
	}


	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::do_hyphenate
	 * @covers ::hyphenate_compounds
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\get_object_hash
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $html                 HTML input.
	 * @param string $result               Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 * @param bool   $hyphenate_compunds   Hyphenate compound-words.
	 */
	public function test_hyphenate( $html, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds ) {
		$typo = $this->typo;
		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( $lang );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( $hyphenate_headings );
		$typo->set_hyphenate_all_caps( $hyphenate_all_caps );
		$typo->set_hyphenate_title_case( $hyphenate_title_case );
		$typo->set_hyphenate_compounds( $hyphenate_compunds );
		$typo->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Provide data for testing hyphenation with custom exceptions.
	 *
	 * @return array
	 */
	public function provide_hyphenate_with_exceptions_data() {
		return [
			[ 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', 'A few words to hy&shy;phen&shy;ate, like KING&shy;desk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!', [ 'KING-desk' ], 'en-US', true, true, true, false ],
			[ 'Geschäftsübernahme', 'Ge&shy;sch&auml;fts&shy;&uuml;ber&shy;nah&shy;me', [], 'de', true, true, true, false ],
			[ 'Geschäftsübernahme', 'Ge&shy;sch&auml;fts&shy;&uuml;ber&shy;nah&shy;me', [ 'Ge-schäfts-über-nah-me' ], 'de', true, true, true, false ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', [], 'de', true, true, true, false ],
			[ 'Trinkwasserinstallation', 'Trink&shy;wasser&shy;in&shy;stal&shy;la&shy;tion', [ 'Trink-wasser-in-stal-la-tion' ], 'de', true, true, true, false ],
		];
	}


	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::do_hyphenate
	 * @covers ::hyphenate_compounds
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\get_object_hash
	 *
	 * @dataProvider provide_hyphenate_with_exceptions_data
	 *
	 * @param string $html                 HTML input.
	 * @param string $result               Expected result.
	 * @param array  $exceptions           Custom hyphenation exceptions.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 * @param bool   $hyphenate_compunds   Hyphenate compound-words.
	 */
	public function test_hyphenate_with_exceptions( $html, $result, $exceptions, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds ) {
		$typo = $this->typo;
		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( $lang );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( $hyphenate_headings );
		$typo->set_hyphenate_all_caps( $hyphenate_all_caps );
		$typo->set_hyphenate_title_case( $hyphenate_title_case );
		$typo->set_hyphenate_compounds( $hyphenate_compunds );
		$typo->set_hyphenation_exceptions( $exceptions );

		$this->assertSame( $result, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_hyphenate_headings_disabled() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'en-US' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( false );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( true );
		$typo->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$html = '<h2>A few words to hyphenate, like KINGdesk. Really, there should be no hyphenation here!</h2>';
		$this->assertSame( $html, clean_html( $typo->process( $html ) ) );
	}

	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_do_hyphenate() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'de' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( false );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( true );
		$s = $typo->get_settings();

		$tokens = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
		$hyphenated = $typo->do_hyphenate( $tokens, $s );
		$this->assertEquals( $hyphenated, $tokens );

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $typo->do_hyphenate( $tokens, $s );
		$this->assertNotEquals( $hyphenated, $tokens );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_do_hyphenate_no_title_case() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'de' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( false );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( false );
		$s = $typo->get_settings();

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated  = $typo->do_hyphenate( $tokens, $s );
		$this->assertEquals( $tokens, $hyphenated );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_do_hyphenate_invalid() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'de' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( false );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( false );
		$s = $typo->get_settings();

		$s['hyphenMinBefore'] = 0; // invalid value.

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated  = $typo->do_hyphenate( $tokens, $s );
		$this->assertEquals( $tokens, $hyphenated );
	}


	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::do_hyphenate
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_hyphenate_no_custom_exceptions() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'en-US' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( true );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( true );

		$this->assertSame('A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!',
						   clean_html( $typo->process( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!' ) ) );
	}


	/**
	 * Test hyphenate.
	 *
	 * @covers ::hyphenate
	 * @covers ::do_hyphenate
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Text_Parser
	 */
	public function test_hyphenate_no_exceptions_at_all() {
		$typo = $this->typo;

		$typo->set_hyphenation( true );
		$typo->set_hyphenation_language( 'en-US' );
		$typo->set_min_length_hyphenation( 2 );
		$typo->set_min_before_hyphenation( 2 );
		$typo->set_min_after_hyphenation( 2 );
		$typo->set_hyphenate_headings( true );
		$typo->set_hyphenate_all_caps( true );
		$typo->set_hyphenate_title_case( true );
		$s = $typo->get_settings();

		$s['hyphenationPatternExceptions'] = [];
		unset( $s['hyphenationExceptions'] );

		$this->assertSame( 'A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!',
						   clean_html( $typo->process( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', false, $s ) ) );
	}

	/**
	 * Test get_settings_hash.
	 *
	 * @covers ::get_settings_hash
	 */
	public function test_get_settings_hash() {
		$typo = $this->typo;

		$typo->set_smart_quotes( true );
		$hash1 = $typo->get_settings_hash( 10 );
		$this->assertEquals( 10, strlen( $hash1 ) );

		$typo->set_smart_quotes( false );
		$hash2 = $typo->get_settings_hash( 10 );
		$this->assertEquals( 10, strlen( $hash2 ) );

		$this->assertNotEquals( $hash1, $hash2 );
	}


	/**
	 * Test init.
	 *
	 * @covers ::init
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_init() {
		$second_typo = new \PHP_Typography\PHP_Typography( false, 'lazy' );
		$this->assertAttributeEmpty( 'settings', $second_typo );

		$second_typo->init();

		$this->assertAttributeNotEmpty( 'settings', $second_typo );
	}


	/**
	 * Test set_defaults.
	 *
	 * @covers ::set_defaults
	 *
	 * @uses PHP_Typography\Hyphenator
	 */
	public function test_init_no_default() {
		$second_typo = new \PHP_Typography\PHP_Typography( false, 'lazy' );
		$second_typo->init( false );
		$s = $second_typo->get_settings();

		$this->assertFalse( isset( $s['smartQuotes'] ) );
		$second_typo->set_defaults();
		$s = $second_typo->get_settings();
		$this->assertTrue( $s['smartQuotes'] );
	}


	/**
	 * Test get_html.
	 *
	 * @covers ::get_html5_parser
	 */
	public function test_get_html5_parser() {
		$typo = $this->typo;

		$this->assertAttributeEmpty( 'html5_parser', $typo );

		$parser1 = $typo->get_html5_parser();
		$this->assertInstanceOf( '\Masterminds\HTML5', $parser1 );

		$parser2 = $typo->get_html5_parser();
		$this->assertInstanceOf( '\Masterminds\HTML5', $parser2 );

		$this->assertSame( $parser1, $parser2 );
		$this->assertAttributeInstanceOf( '\Masterminds\HTML5', 'html5_parser', $typo );
	}


	/**
	 * Test get_text_parser.
	 *
	 * @covers ::get_text_parser
	 *
	 * @uses PHP_Typography\Text_Parser::__construct
	 */
	public function test_get_text_parser() {
		$typo = $this->typo;

		$this->assertAttributeEmpty( 'text_parser', $typo );

		$parser1 = $typo->get_text_parser();
		$this->assertInstanceOf( '\PHP_Typography\Text_Parser', $parser1 );

		$parser2 = $typo->get_text_parser();
		$this->assertInstanceOf( '\PHP_Typography\Text_Parser', $parser2 );

		$this->assertSame( $parser1, $parser2 );
		$this->assertAttributeInstanceOf( '\PHP_Typography\Text_Parser', 'text_parser', $typo );
	}


	/**
	 * Test parse_html.
	 *
	 * @covers ::parse_html
	 */
	public function test_parse_html() {
		$typo = $this->typo;
		$dom = $typo->parse_html( $typo->get_html5_parser(), '<p>some text</p>', $typo->get_settings() );

		$this->assertInstanceOf( '\DOMDocument', $dom );
		$this->assertEquals( 1, $dom->getElementsByTagName( 'p' )->length );
	}


	/**
	 * Test parse_html.
	 *
	 * @covers ::parse_html
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string $html    HTML input.
	 * @param string $ignore1 Ignored.
	 * @param string $ignore2 Ignored.
	 */
	public function test_parse_html_extended( $html, $ignore1, $ignore2 ) {
		$typo = $this->typo;
		$p    = $typo->get_html5_parser();
		$dom  = $typo->parse_html( $p, $html, $typo->get_settings() );

		$this->assertInstanceOf( '\DOMDocument', $dom );

		// Serialize the stuff again.
		$xpath     = new \DOMXPath( $dom );
		$body_node = $xpath->query( '/html/body' )->item( 0 );

		$this->assertEquals( $html, $p->saveHTML( $body_node->childNodes ) );
	}

	/**
	 * Provide data for testing parsing HTML containing markup errors.
	 *
	 * @return array
	 */
	public function provide_parse_html_with_errors_data() {
		return [
			[ '<div>foobar</div></p>', 'Line 0, Col 0: Could not find closing tag for p' ],
			[ '<a href="http://example.org?foo=xx&bar=yy">foobar</a>', "Line 1, Col 65: No match in entity table for 'bar'" ],
		];
	}

	/**
	 * Test parse_html.
	 *
	 * @covers ::parse_html
	 *
	 * @dataProvider provide_parse_html_with_errors_data
	 *
	 * @param  string $html      HTML input.
	 * @param  string $error_msg Expected error message.
	 */
	public function test_parse_html_with_errors( $html, $error_msg ) {
		$typo = $this->typo;
		$s = $typo->get_settings();

		// Without an error handler.
		$dom = $typo->parse_html( $typo->get_html5_parser(), $html, $s );
		$this->assertNull( $dom );

		// With error handler.
		$s->set_parser_errors_handler(function ( $errors ) {
			foreach ( $errors as $error ) {
				echo $error; // WPCS: XSS ok.
			}

			return [];
		});

		$this->expectOutputString( $error_msg );
		$dom = $typo->parse_html( $typo->get_html5_parser(), $html, $s );
		$this->assertInstanceOf( 'DOMDocument', $dom );
	}

	/**
	 * Test get_block_parent.
	 *
	 * @covers ::get_block_parent
	 */
	public function test_get_block_parent() {
		$typo = $this->typo;

		$html = '<div id="outer"><p id="para"><span>A</span><span id="foo">new hope.</span></p><span><span id="bar">blabla</span></span></div>';
		$doc = $typo->get_html5_parser()->loadHTML( $html );
		$xpath = new \DOMXPath( $doc );

		$outer_div  = $xpath->query( "//*[@id='outer']" )->item( 0 ); // really only one.
		$paragraph  = $xpath->query( "//*[@id='para']" )->item( 0 );  // really only one.
		$span_foo   = $xpath->query( "//*[@id='foo']" )->item( 0 );   // really only one.
		$span_bar   = $xpath->query( "//*[@id='bar']" )->item( 0 );   // really only one.
		$textnode_a = $xpath->query( "//*[@id='para']//text()" )->item( 0 ); // we don't care which one.
		$textnode_b = $xpath->query( "//*[@id='bar']//text()" )->item( 0 );  // we don't care which one.
		$textnode_c = $xpath->query( "//*[@id='foo']//text()" )->item( 0 );  // we don't care which one.

		$this->assertSame( $paragraph, $typo->get_block_parent( $span_foo ) );
		$this->assertSame( $paragraph, $typo->get_block_parent( $textnode_a ) );
		$this->assertSame( $outer_div, $typo->get_block_parent( $paragraph ) );
		$this->assertSame( $outer_div, $typo->get_block_parent( $span_bar ) );
		$this->assertSame( $outer_div, $typo->get_block_parent( $textnode_b ) );
		$this->assertSame( $paragraph, $typo->get_block_parent( $textnode_c ) );
	}


	/**
	 * Test set_true_no_break_narrow_space.
	 *
	 * @covers ::set_true_no_break_narrow_space
	 */
	public function test_set_true_no_break_narrow_space() {
		$typo = $this->typo;

		$typo->set_true_no_break_narrow_space(); // defaults to false.
		$s = $typo->get_settings();
		$this->assertSame( $s->chr( 'noBreakNarrowSpace' ), Strings::uchr( 160 ) );
		$this->assertAttributeContains( [
			'open'  => Strings::uchr( 171 ) . Strings::uchr( 160 ),
			'close' => Strings::uchr( 160 ) . Strings::uchr( 187 ),
		], 'quote_styles', $s );

		$typo->set_true_no_break_narrow_space( true ); // defaults to false.
		$s = $typo->get_settings();
		$this->assertSame( $s->chr( 'noBreakNarrowSpace' ), Strings::uchr( 8239 ) );
		$this->assertAttributeContains( [
			'open'  => Strings::uchr( 171 ) . Strings::uchr( 8239 ),
			'close' => Strings::uchr( 8239 ) . Strings::uchr( 187 ),
		], 'quote_styles', $s );
	}

	/**
	 * Test get_hyphenator.
	 *
	 * @covers ::get_hyphenator()
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::build_trie
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 * @uses PHP_Typography\get_object_hash
	 */
	public function test_get_hyphenator() {
		$typo = $this->typo;
		$s    = $typo->get_settings();

		$s['hyphenMinLength']             = 2;
		$s['hyphenMinBefore']             = 2;
		$s['hyphenMinAfter']              = 2;
		$s['hyphenationCustomExceptions'] = [ 'foo-bar' ];
		$s['hyphenLanguage']              = 'en-US';
		$h = $typo->get_hyphenator( $s );

		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );

		$s['hyphenationCustomExceptions'] = [ 'bar-foo' ];
		$h = $typo->get_hyphenator( $s );

		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );
	}

	/**
	 * Test set_hyphenator.
	 *
	 * @covers ::set_hyphenator()
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 */
	public function test_set_hyphenator() {

		// Initial set-up.
		$typo = $this->typo;
		$s    = $typo->get_settings();
		$h1   = $typo->get_hyphenator( $s );

		// Create external Hyphenator.
		$h2 = new \PHP_Typography\Hyphenator();
		$typo->set_hyphenator( $h2 );

		// Retrieve Hyphenator and assert results.
		$this->assertEquals( $h2, $typo->get_hyphenator( $s ) );
		$this->assertNotEquals( $h1, $typo->get_hyphenator( $s ) );
	}

	/**
	 * Provide data for testing arrays_intersect.
	 *
	 * @return array
	 */
	public function provide_arrays_intersect_data() {
		return [
			[ [], [], false ],
			[ [ 1, 2, 3 ], [ 2, 4, 1 ], true ],
			[ [ 1, 2, 3 ], [], false ],
			[ [], [ 1, 2, 3 ], false ],
		];
	}

	/**
	 * $a1 and $a2 need to be arrays of object indexes < 10
	 *
	 * @covers \PHP_Typography\arrays_intersect
	 * @dataProvider provide_arrays_intersect_data
	 *
	 * @param  array $a1     First array.
	 * @param  array $a2     Second array.
	 * @param  bool  $result Expected result.
	 */
	public function test_arrays_intersect( array $a1, array $a2, $result ) {
		$nodes = [];
		for ( $i = 0; $i < 10; ++$i ) {
			$nodes[] = new \DOMText( "foo $i" );
		}

		$array1 = [];
		foreach ( $a1 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array1[] = $nodes[ $index ];
			}
		}

		$array2 = [];
		foreach ( $a2 as $index ) {
			if ( isset( $nodes[ $index ] ) ) {
				$array2[ spl_object_hash( $nodes[ $index ] ) ] = $nodes[ $index ];
			}
		}

		$this->assertSame( $result, $this->invokeStaticMethod( \PHP_Typography\PHP_Typography::class, 'arrays_intersect', [ $array1, $array2 ] ) );
	}
}
