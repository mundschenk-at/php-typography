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

namespace PHP_Typography\Tests;

use PHP_Typography\DOM;
use PHP_Typography\PHP_Typography;
use PHP_Typography\Settings;
use PHP_Typography\Strings;
use PHP_Typography\U;

use PHP_Typography\Settings\Quote_Style;

use PHP_Typography\Fixes\Default_Registry;
use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Registry;

use PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;

use Mockery as m;

/**
 * PHP_Typography unit test.
 *
 * @coversDefaultClass PHP_Typography\PHP_Typography
 * @usesDefaultClass PHP_Typography\PHP_Typography
 *
 * @uses PHP_Typography\PHP_Typography
 * @uses PHP_Typography\Hyphenator\Cache
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\RE
 * @uses PHP_Typography\Fixes\Registry
 * @uses PHP_Typography\Fixes\Default_Registry
 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Classes_Dependent_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Style_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Process_Words_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Fractions_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Ordinal_Suffix_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Style_Hanging_Punctuation_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Style_Initial_Quotes_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Compounds_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Wrap_Emails_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Wrap_Hard_Hyphens_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Smart_Dashes_Hyphen_Fix
 * @uses PHP_Typography\Fixes\Token_Fixes\Wrap_URLs_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Dash_Spacing_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Dewidow_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\French_Punctuation_Spacing_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Numbered_Abbreviation_Spacing_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Simple_Regex_Replacement_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Single_Character_Word_Spacing_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Dashes_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Diacritics_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Ellipses_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Exponents_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Marks_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Maths_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Quotes_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Space_Collapse_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Smart_Area_Units_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Style_Ampersands_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Style_Caps_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Style_Numbers_Fix
 * @uses PHP_Typography\Fixes\Node_Fixes\Unit_Spacing_Fix
 */
class PHP_Typography_Test extends Testcase {

	/**
	 * The PHP_Typography instance.
	 *
	 * @var PHP_Typography
	 */
	protected $typo;

	/**
	 * The Settings instance.
	 *
	 * @var Settings
	 */
	protected $s;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->typo = new PHP_Typography();
		$this->s    = new Settings( false );
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$typo = new PHP_Typography();
		$this->assertNull( $this->get_value( $typo, 'registry' ) );
		$this->assertFalse( $this->get_value( $typo, 'update_registry_cache' ) );

		$typo = new PHP_Typography( m::mock( Registry::class ) );
		$this->assertNotNull( $this->get_value( $typo, 'registry' ) );
		$this->assertTrue( $this->get_value( $typo, 'update_registry_cache' ) );
	}

	/**
	 * Test set_tags_to_ignore.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_set_tags_to_ignore() {
		// Syntax shortening.
		$s = $this->s;

		// Constants.
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
			'svg',
			'math',
		];

		// Input.
		$tags_to_ignore = [
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
		];

		// Default tags.
		$s->set_tags_to_ignore( $tags_to_ignore );

		// Inspect settings.
		foreach ( $tags_to_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		// Auto-close tag and something else.
		$s->set_tags_to_ignore( [ 'img', 'foo' ] );
		$this->assertContains( 'foo', $s['ignoreTags'] );
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		$s->set_tags_to_ignore( 'img foo  \	' ); // Should not result in an error.
		$s->set_smart_quotes( true );
		$s->set_smart_quotes_primary();
		$s->set_smart_quotes_secondary();
		$html     = '<p><foo>Ignore this "quote",</foo><span class="other"> but not "this" one.</span></p>';
		$expected = '<p><foo>Ignore this "quote",</foo><span class="other"> but not &ldquo;this&rdquo; one.</span></p>';
		$this->assertSame( $expected, $this->clean_html( $this->typo->process( $html, $s ) ) );
	}

	/**
	 * Test set_classes_to_ignore.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_set_classes_to_ignore() {
		$s = $this->s;

		$s->set_classes_to_ignore( 'foo bar' );

		$this->assertContains( 'foo', $s['ignoreClasses'] );
		$this->assertContains( 'bar', $s['ignoreClasses'] );

		$html = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';

		$expected = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';

		$s->set_smart_quotes( true );
		$s->set_smart_quotes_primary();
		$s->set_smart_quotes_secondary();
		$this->assertSame( $expected, $this->clean_html( $this->typo->process( $html, $s ) ) );
	}

	/**
	 * Test set_ids_to_ignore.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_set_ids_to_ignore() {
		$s = $this->s;

		$s->set_ids_to_ignore( 'foobar barfoo' );

		$this->assertContains( 'foobar', $s['ignoreIDs'] );
		$this->assertContains( 'barfoo', $s['ignoreIDs'] );

		$html = '<p><span id="foobar">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p id="barfoo">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';

		$expected = '<p><span id="foobar">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p id="barfoo">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';

		$s->set_smart_quotes( true );
		$s->set_smart_quotes_primary();
		$s->set_smart_quotes_secondary();
		$this->assertSame( $expected, $this->clean_html( $this->typo->process( $html, $s ) ) );
	}

	/**
	 * Integrate all three "ignore" variants.
	 *
	 * @covers ::query_tags_to_ignore
	 *
	 * @depends test_set_ids_to_ignore
	 * @depends test_set_classes_to_ignore
	 * @depends test_set_tags_to_ignore
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_complete_ignore() {
		$s = $this->s;

		$s->set_ids_to_ignore( 'foobar barfoo' );
		$s->set_classes_to_ignore( 'foo bar' );
		$s->set_tags_to_ignore( [ 'img', 'foo' ] );

		$html = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not "this" one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>"But" not this.</span></p>';

		$expected = '<p><span class="foo">Ignore this "quote",</span><span class="other"> but not &ldquo;this&rdquo; one.</span></p>
				 <p class="bar">"This" should also be ignored. <span>And "this".</span></p>
				 <p><span>&ldquo;But&rdquo; not this.</span></p>';

		$s->set_smart_quotes( true );
		$s->set_smart_quotes_primary();
		$s->set_smart_quotes_secondary();
		$this->assertSame( $expected, $this->clean_html( $this->typo->process( $html, $s ) ) );
	}

	/**
	 * Test get_hyphenation_languages.
	 *
	 * @covers ::get_hyphenation_languages
	 * @covers ::get_language_plugin_list
	 */
	public function test_get_hyphenation_languages() {

		$expected     = [
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

		$actual = $this->typo->get_hyphenation_languages();
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
	 * @covers ::get_language_plugin_list
	 */
	public function test_get_diacritic_languages() {

		$expected     = [ 'de-DE', 'en-US' ];
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

		$actual = $this->typo->get_diacritic_languages();
		foreach ( $expected as $lang_code ) {
			$this->assertArrayHasKey( $lang_code, $actual );
		}
		foreach ( $not_expected as $lang_code ) {
			$this->assertArrayNotHasKey( $lang_code, $actual );
		}
	}

	/**
	 * Test get_language_plugin_list (called with an invalid path).
	 *
	 * @covers ::get_language_plugin_list
	 */
	public function test_get_language_plugin_list_incorrect_path() {
		$this->expect_warning( \PHPUnit\Framework\Error\Warning::class );

		$this->invoke_static_method( PHP_Typography::class, 'get_language_plugin_list', [ '/does/not/exist' ] );

		$this->assertEmpty( @$this->invoke_static_method( PHP_Typography::class, 'get_language_plugin_list', [ '/does/not/exist' ] ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
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
			[ 'open 10-5', 'open <span class="numbers">10</span>&thinsp;&ndash;&thinsp;<span class="numbers">5</span>', 'open 10&ndash;5' ], // More smart dashes.
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
			[ '&lt;<a href="http://example.org">test</a>&gt;', '<<a href="http://example.org">test</a>>', false ],
			[ '3 &lt; 4 &gt; 5', '<span class="numbers">3</span> < <span class="numbers">4</span> >&nbsp;<span class="numbers">5</span>', false ],
			[ 'Årø Bilsenter', '&Aring;r&oslash; Bilsen&shy;ter', false ],
			[ '<p>Line One,<br>Line Two,<br><span>Line Three.</span></p>', '<p>Line One,<br>Line Two,<br><span>Line Three.</span></p>', false ],
			[ '<p>Line One<br>Line Two<br><span>Line Three.</span></p>', '<p>Line One<br>Line Two<br><span>Line Three.</span></p>', false ],
			[ '<p>Line One,<br><br><span></span></p>', '<p>Line One,<br><br><span></span></p>', false ],
			[ '<p>Line One<br>Line Two<br><span>Line Three.</span></p>', '<p>Line One<br>Line Two<br><span>Line Three.</span></p>', false ],
			[ '<p>Line One <br>Line Two,<br><span>Line Three.</span></p>', '<p>Line One <br>Line Two,<br><span>Line Three.</span></p>', false ],
			[ '3/4 of 10/12/89', '<sup class="numerator"><span class="numbers">3</span></sup>&frasl;<sub class="denominator"><span class="numbers">4</span></sub> of <span class="numbers">10</span>/<span class="numbers">12</span>/<span class="numbers">89</span>', false ],
			[ 'Certain HTML entities', 'Cer&shy;tain <span class="caps">HTML</span> entities', false ],
			[ 'during WP-CLI commands', 'dur&shy;ing <span class="caps">WP-CLI</span> commands', false ],
			[ 'from the early \'60s, American engagement', 'from the ear&shy;ly <span class="push-single"></span>&#8203;<span class="pull-single">&rsquo;</span><span class="numbers">60</span>s, Amer&shy;i&shy;can engagement', 'from the early &rsquo;60s, American engagement' ],
			[ 'Warenein- und -ausgang', 'Warenein- und &#8209;aus&shy;gang', 'Warenein- und &#8209;ausgang' ],
			[ 'Fugen-s', 'Fugen&#8209;s', true ],
			[ 'ein-, zweimal', 'ein&#8209;, zweimal', true ],
			[ 'В зависимости от региона, может выращиватся на зерно и силос. После колосовых может выращиватся на второй посев.', 'В зависимости от региона, может выращиватся на зерно и&nbsp;силос. После колосовых может выращиватся на второй посев.', false ],
		];
	}


	/**
	 * Test process.
	 *
	 * @covers ::process
	 *
	 * @uses ::process_textnodes
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Entity-escaped output.
	 * @param bool   $feed   Use process_feed.
	 */
	public function test_process( $html, $result, $feed ) {
		$s = $this->s;
		$s->set_defaults();

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $s ) ) );
	}


	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 *
	 * @uses ::process_textnodes
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Entity-escaped output.
	 * @param bool|string $feed   Use process_feed. If $feed is a string, use instead of $result.
	 */
	public function test_process_feed( $html, $result, $feed ) {
		$s = $this->s;
		$s->set_defaults();

		if ( is_string( $feed ) ) {
			$this->assertSame( $feed, $this->clean_html( $this->typo->process_feed( $html, $s ) ) );
		} elseif ( $feed ) {
			$this->assertSame( $result, $this->clean_html( $this->typo->process_feed( $html, $s ) ) );
		} else {
			$this->assertSame( $html, $this->typo->process_feed( $html, $s ) );
		}
	}

	/**
	 * Test process_textnodes.
	 *
	 * @covers ::process_textnodes
	 * @covers ::process_textnodes_internal
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Entity-escaped output.
	 * @param bool|string $feed   Use process_feed. If $feed is a string, use instead of $result.
	 */
	public function test_process_textnodes( $html, $result, $feed ) {
		$s = $this->s;
		$s->set_defaults();

		$this->assertSame(
			$this->clean_html( $html ),
			$this->clean_html( $this->typo->process_textnodes( $html, function ( $node ) {}, $s ) )
		);
	}

	/**
	 * Test process_textnodes.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 */
	public function test_process_textnodes_with_result() {
		$s = $this->s;
		$s->set_defaults();

		// We don't really care about the result, so we make sotmhing up.
		$this->assertSame(
			'fake result',
			$this->typo->process_textnodes(
				'some input',
				function ( $node ) {
					$node->data = 'fake result';
				},
				$s
			)
		);
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
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_textnodes_invalid_html_data
	 *
	 * @param string $html HTML input.
	 * @param string $feed Ignored.
	 */
	public function test_process_textnodes_invalid_html( $html, $feed ) {
		$s = $this->s;
		$s->set_defaults();

		$this->assertSame(
			$html,
			$this->clean_html(
				$this->typo->process_textnodes(
					$html,
					function ( $node ) {
						return 'XXX';
					},
					$s
				)
			)
		);
	}


	/**
	 * Test process_textnodes without a fixer instance.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Ignored.
	 * @param bool|string $feed   Ignored.
	 */
	public function test_process_textnodes_no_fixer( $html, $result, $feed ) {
		$s = $this->s;
		$s->set_defaults();

		$this->expect_exception( \TypeError::class );

		$this->typo->process_textnodes( $html, 'bar', $s );
	}

	/**
	 * Test process_textnodes with alternate settings for titles.
	 *
	 * @covers ::process_textnodes
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string      $html   HTML input.
	 * @param string      $result Ignored.
	 * @param bool|string $feed   Ignored.
	 */
	public function test_process_textnodes_alternate_settings_title( $html, $result, $feed ) {
		$s = new Settings( true );
		$s->set_tags_to_ignore( [ 'h1', 'h2' ] );

		$this->assertSame(
			$this->clean_html( $html ),
			$this->clean_html( $this->typo->process_textnodes( $html, function ( $node ) {}, $s, true ) )
		);
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
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_with_title_data
	 *
	 * @param string      $html      HTML input.
	 * @param string      $result    Expected entity-escaped result.
	 * @param bool|string $feed      Ignored.
	 * @param array       $skip_tags Tags to skip.
	 */
	public function test_process_with_title( $html, $result, $feed, $skip_tags ) {
		$s = $this->s;
		$s->set_defaults();

		$s->set_tags_to_ignore( $skip_tags );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $s, true ) ) );
	}


	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Settings\Dash_Style::get_styled_dashes
	 * @uses PHP_Typography\Settings\Quote_Style::get_styled_quotes
	 *
	 * @dataProvider provide_process_with_title_data
	 *
	 * @param string      $html      HTML input.
	 * @param string      $result    Expected entity-escaped result.
	 * @param bool|string $feed      Whether process_feed should be used. If string, use instead of $result.
	 * @param array       $skip_tags Tags to skip.
	 */
	public function test_process_feed_with_title( $html, $result, $feed, $skip_tags ) {
		$s = $this->s;
		$s->set_defaults();

		$s->set_tags_to_ignore( $skip_tags );

		if ( is_string( $feed ) ) {
			$this->assertSame( $feed, $this->clean_html( $this->typo->process_feed( $html, $s, true ) ) );
		} elseif ( $feed ) {
			$this->assertSame( $result, $this->clean_html( $this->typo->process_feed( $html, $s, true ) ) );
		} else {
			$this->assertSame( $html, $this->typo->process_feed( $html, $s, true ) );
		}
	}

	/**
	 * Provide data for testing handle_parsing_errors.
	 *
	 * @return array{ errno : int, errstr : string, errfile : string, errline : int, errcontext : array, result : bool }
	 */
	public function provide_handle_parsing_errors() {
		return [
			[ E_USER_WARNING, 'Fake error message', '/some/path/DOMTreeBuilder.php', 666, [], true ],
			[ E_USER_ERROR,   'Fake error message', '/some/path/DOMTreeBuilder.php', 666, [], false ],
			[ E_USER_WARNING, 'Fake error message', '/some/path/SomeFile.php',       666, [], false ],
			[ E_USER_NOTICE,  'Fake error message', '/some/path/DOMTreeBuilder.php', 666, [], false ],
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
	 * @param  int    $errline    Line number.
	 * @param  array  $errcontext Stack context.
	 * @param  bool   $result     The expected result.
	 */
	public function test_handle_parsing_errors( $errno, $errstr, $errfile, $errline, $errcontext, $result ) {

		if ( $result ) {
			$this->assertTrue( $this->typo->handle_parsing_errors( $errno, $errstr, $errfile ) );
		} else {
			$this->assertFalse( $this->typo->handle_parsing_errors( $errno, $errstr, $errfile ) );
		}

		// Try again when we are not interested.
		$old_level = error_reporting( 0 );
		$this->assertTrue( $this->typo->handle_parsing_errors( $errno, $errstr, $errfile ) );
		error_reporting( $old_level );
	}

	/**
	 * Provide data for testing smart_quotes.
	 *
	 * @return array
	 */
	public function provide_smart_quotes_data() {
		return [
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
			[ '("Some" word',                      '(&ldquo;Some&rdquo; word' ],
			[ '"So \'this\'", she said',           '&ldquo;So &lsquo;this&rsquo;&nbsp;&rdquo;, she said' ],
			[ '"\'This\' is it?"',                 '&ldquo;&nbsp;&lsquo;This&rsquo; is it?&rdquo;' ],
			[ '"this is a sentence."',             '&laquo;&nbsp;this is a sentence.&nbsp;&raquo;', Quote_Style::DOUBLE_GUILLEMETS_FRENCH ],
			[ '("Some" word',                      '(&raquo;Some&laquo; word', Quote_Style::DOUBLE_GUILLEMETS_REVERSED, Quote_Style::SINGLE_GUILLEMETS_REVERSED ],
		];
	}

	/**
	 * Test smart_quotes.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html      HTML input.
	 * @param string $result    Entity-escaped result string.
	 * @param string $primary   Optional. The primary quote style. Default DOUBLE_CURLED.
	 * @param string $secondary Optional. The secondary quote style. Default SINGLE_CURLED.
	 */
	public function test_smart_quotes( $html, $result, $primary = Quote_Style::DOUBLE_CURLED, $secondary = Quote_Style::SINGLE_CURLED ) {
		$this->s->set_smart_quotes( true );
		$this->s->set_smart_quotes_primary( $primary );
		$this->s->set_smart_quotes_secondary( $secondary );
		$this->s->set_true_no_break_narrow_space();

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test smart_quotes.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_quotes_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Entity-escaped result string.
	 */
	public function test_smart_quotes_off( $html, $result ) {
		$this->s->set_smart_quotes( false );
		$this->s->set_smart_quotes_primary();
		$this->s->set_smart_quotes_secondary();

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test smart_quotes with French quotes (two characters!).
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_smart_quotes_french_should_not_apply() {
		$html   = 'attributs <code>role="group"</code> et <code>aria-labelledby</code>';
		$result = 'attributs <code>role="group"</code> et <code>aria-labelledby</code>';

		$this->s->set_tags_to_ignore( [ 'code' ] );
		$this->s->set_smart_quotes( true );
		$this->s->set_smart_quotes_primary( Settings\Quote_Style::DOUBLE_GUILLEMETS_FRENCH );
		$this->s->set_smart_quotes_secondary();

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test smart_dashes.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_smart_dashes_with_dash_spacing_off( $input, $result_us, $result_int ) {
		$this->s->set_smart_dashes( true );
		$this->s->set_dash_spacing( false );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $result_us, $this->clean_html( $this->typo->process( $input, $this->s ) ) );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertSame( $result_int, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test smart_dashes.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_dashes_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_smart_dashes_off( $input, $result_us, $result_int ) {
		$this->s->set_smart_dashes( false );
		$this->s->set_dash_spacing( false );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $this->typo->process( $input, $this->s ) ) );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $this->typo->process( $input, $this->s ) ) );

		$this->s->set_smart_dashes_style( 'internationalNoHairSpaces' );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_ellipses_data
	 *
	 * @param string $input  HTML intput.
	 * @param string $result Expected result.
	 */
	public function test_smart_ellipses( $input, $result ) {
		$this->s->set_smart_ellipses( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Test smart_ellipses.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_ellipses_data
	 *
	 * @param string $input  HTML intput.
	 * @param string $result Ignored.
	 */
	public function test_smart_ellipses_off( $input, $result ) {
		$this->s->set_smart_ellipses( false );

		$this->assertSame( $input, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
			[ 'ne vs. seine vs einzelne', 'né vs. seine vs einzelne', 'en-US' ],
			[ 'ne vs. sei&shy;ne vs einzelne', 'né vs. sei&shy;ne vs einzelne', 'en-US' ],
			[ 'Weiterhin müssen außenpolitische Experten raus aus ihrer Berliner Blase. In der genannten Umfrage', 'Weiterhin müssen außenpolitische Experten raus aus ihrer Berliner Blase. In der genannten Umfrage', 'de-DE' ],
		];
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 * @param string $lang   Language code.
	 */
	public function test_smart_diacritics( $html, $result, $lang ) {
		$this->s->set_smart_diacritics( true );
		$this->s->set_diacritic_language( $lang );

		$this->assertSame( $this->clean_html( $result ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test smart_diacritics.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_diacritics_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Ignored.
	 * @param string $lang   Language code.
	 */
	public function test_smart_diacritics_off( $html, $result, $lang ) {
		$this->s->set_smart_diacritics( false );
		$this->s->set_diacritic_language( $lang );

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_diacritics_error_in_pattern_data
	 *
	 * @param string $html   HTML input.
	 * @param string $lang   Language code.
	 * @param string $unset  Replacement to unset.
	 */
	public function test_smart_diacritics_error_in_pattern( $html, $lang, $unset ) {

		$this->s->set_smart_diacritics( true );
		$this->s->set_diacritic_language( $lang );
		$s = $this->s;

		$replacements = $s[ Settings::DIACRITIC_REPLACEMENT_DATA ];
		unset( $replacements['replacements'][ $unset ] );
		$s[ Settings::DIACRITIC_REPLACEMENT_DATA ] = $replacements;

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $s, false ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_marks_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_smart_marks( $input, $result ) {
		$this->s->set_smart_marks( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Test smart_marks.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_marks_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Ignored.
	 */
	public function test_smart_marks_off( $input, $result ) {
		$this->s->set_smart_marks( false );

		$this->assertSame( $input, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_math_data
	 *
	 * @param string $result Expected result.
	 * @param string $input  HTML input.
	 * @param bool   $same   Result expected to be the same or not the same.
	 */
	public function test_smart_math( $result, $input, $same ) {
		$this->s->set_smart_math( true );

		if ( $same ) {
			$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
		} else {
			$this->assertNotSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
		}
	}

	/**
	 * Test smart_math.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_math_data
	 *
	 * @param string $result Ignored.
	 * @param string $input  HTML input.
	 * @param bool   $same   Ignored.
	 */
	public function test_smart_math_off( $result, $input, $same ) {
		$this->s->set_smart_math( false );

		$this->assertSame( $input, $this->typo->process( $input, $this->s ) );
	}

	/**
	 * Test smart_exponents.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_smart_exponents() {
		$this->s->set_smart_exponents( true );

		$this->assertSame( '10<sup>12</sup>', $this->typo->process( '10^12', $this->s ) );
	}

	/**
	 * Test smart_exponents.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_smart_exponents_off() {
		$this->s->set_smart_exponents( false );

		$this->assertSame( '10^12', $this->typo->process( '10^12', $this->s ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
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
		$typo = new PHP_Typography_CSS_Classes(
			[
				'numerator'   => $num_css_class,
				'denominator' => $denom_css_class,
			]
		);
		$this->s->set_smart_fractions( true );
		$this->s->set_true_no_break_narrow_space( true );

		$this->s->set_fraction_spacing( false );
		$this->assertSame( $result, $this->clean_html( $typo->process( $input, $this->s ) ) );

		$this->s->set_fraction_spacing( true );
		$this->assertSame( $result_spacing, $this->clean_html( $typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test smart_fractions.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
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
		$typo = new PHP_Typography_CSS_Classes(
			[
				'numerator'   => $num_css_class,
				'denominator' => $denom_css_class,
			]
		);
		$this->s->set_smart_fractions( false );
		$this->s->set_fraction_spacing( false );

		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $typo->process( $input, $this->s ) ) );
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
			[
				'3 1/4 works',
				'3 <sup class="num">1</sup>&frasl;<sub class="denom">4</sub> works',
				'num',
				'denom',
			],
			[
				'3 1/4" works',
				'3 <sup class="num">1</sup>&frasl;<sub class="denom">4</sub>&Prime; works',
				'num',
				'denom',
			],
			[
				'3 1/4". works',
				'3 <sup class="num">1</sup>&frasl;<sub class="denom">4</sub>&Prime;. works',
				'num',
				'denom',
			],
			[
				'3 1/4", works',
				'3 <sup class="num">1</sup>&frasl;<sub class="denom">4</sub>&Prime;, works',
				'num',
				'denom',
			],
		];
	}

	/**
	 * Test smart_fractions.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_fractions_smart_quotes_data
	 *
	 * @param string $input           HTML input.
	 * @param string $result          Expected result.
	 * @param string $num_css_class   CSS class for numerator.
	 * @param string $denom_css_class CSS class for denominator.
	 */
	public function test_smart_fractions_with_smart_quotes( $input, $result, $num_css_class, $denom_css_class ) {
		$typo = new PHP_Typography_CSS_Classes(
			[
				'numerator'   => $num_css_class,
				'denominator' => $denom_css_class,
			]
		);
		$this->s->set_smart_fractions( true );
		$this->s->set_smart_quotes( true );
		$this->s->set_smart_quotes_primary();
		$this->s->set_smart_quotes_secondary();
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_fraction_spacing( false );

		$this->assertSame( $result, $this->clean_html( $typo->process( $input, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_fraction_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_smart_fractions_only_spacing( $input, $result ) {
		$this->s->set_smart_fractions( false );
		$this->s->set_fraction_spacing( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_ordinal_suffix
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class CSS class for ordinal suffix.
	 */
	public function test_smart_ordinal_suffix( $input, $result, $css_class ) {
		$typo = new PHP_Typography_CSS_Classes( [ 'ordinal' => $css_class ] );
		$this->s->set_smart_ordinal_suffix( true );

		$this->assertSame( $result, $this->clean_html( $typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test smart_ordinal_suffix.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_smart_ordinal_suffix
	 *
	 * @param string $input     HTML input.
	 * @param string $result    Expected result.
	 * @param string $css_class CSS class for ordinal suffix.
	 */
	public function test_smart_ordinal_suffix_off( $input, $result, $css_class ) {
		$typo = new PHP_Typography_CSS_Classes( [ 'ordinal' => $css_class ] );
		$this->s->set_smart_ordinal_suffix( false );

		$this->assertSame( $input, $this->clean_html( $typo->process( $input, $this->s ) ) );
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
			[ 'He is a robot, am I&amp;nbsp;too?', 'He is a&nbsp;robot, am I&amp;nbsp;too?' ],
		];
	}

	/**
	 * Test single_character_word_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_single_character_word_spacing( $input, $result ) {
		$this->s->set_single_character_word_spacing( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test single_character_word_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_single_character_word_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_single_character_word_spacing_off( $input, $result ) {
		$this->s->set_single_character_word_spacing( false );

		$this->assertSame( $input, $this->typo->process( $input, $this->s ) );
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
			[
				"We just don't know - really--, but you-know-who",
				"We just don't know &mdash; really&ndash;, but you&#8208;know&#8208;who",
				"We just don't know &ndash; really&ndash;, but you&#8208;know&#8208;who",
			],
			[
				'ein-, zweimal',
				'ein&#8209;, zweimal',
				'ein&#8209;, zweimal',
			],
			[
				'What-?',
				'What&#8208;?',
				'What&#8208;?',
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
			[ 'Vor- und Nachteile, 100-jährig, Fritz-Walter-Stadion, 2015-12-03, 01-01-1999, 2012-04' ],
			[ 'pick-me-up' ],
			[ 'You may see a yield that is two- or three- or fourfold.' ],
		];
	}


	/**
	 * Test dash_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_dash_spacing_data
	 *
	 * @param string $input      HTML input.
	 * @param string $result_us  Entity-escaped result with US dash style.
	 * @param string $result_int Entity-escaped result with international dash style.
	 */
	public function test_dash_spacing( $input, $result_us, $result_int ) {
		$this->s->set_smart_dashes( true );
		$this->s->set_dash_spacing( true );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$this->assertSame( $result_us, $this->clean_html( $this->typo->process( $input, $this->s ) ) );

		$this->s->set_smart_dashes_style( 'international' );
		$this->assertSame( $result_int, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test dash_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_dash_spacing_unchanged_data
	 *
	 * @param string $input  HTML input.
	 */
	public function test_dash_spacing_unchanged( $input ) {
		$this->s->set_smart_dashes( true );
		$this->s->set_dash_spacing( true );

		$this->s->set_smart_dashes_style( 'traditionalUS' );
		$result = \str_replace( U::HYPHEN, U::HYPHEN_MINUS, $this->typo->process( $input, $this->s ) );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $result ) );

		$this->s->set_smart_dashes_style( 'international' );
		$result = \str_replace( U::HYPHEN, U::HYPHEN_MINUS, $this->typo->process( $input, $this->s ) );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $result ) );

		$this->s->set_smart_dashes_style( 'internationalNoHairSpaces' );
		$result = \str_replace( U::HYPHEN, U::HYPHEN_MINUS, $this->typo->process( $input, $this->s ) );
		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $result ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_space_collapse_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_space_collapse( $input, $result ) {
		$this->s->set_space_collapse( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test space_collapse.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_space_collapse_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_space_collapse_off( $input, $result ) {
		$this->s->set_space_collapse( false );

		$this->assertSame( $input, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_unit_spacing( $input, $result ) {
		$this->s->set_unit_spacing( true );
		$this->s->set_true_no_break_narrow_space( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Test unit_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_unit_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_unit_spacing_off( $input, $result ) {
		$this->s->set_unit_spacing( false );

		$this->assertSame( $input, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Provide data for testing unit_spacing.
	 *
	 * @return array
	 */
	public function provide_unit_spacing_dewidow_data() {
		return [
			[ 'It was 2 m.', 'It was&nbsp;2&#8239;m.' ],
			[ 'Bis zu 3 km/h', 'Bis zu 3&#8239;km/h' ],
			[ '5 sg 44 kg', '5 sg 44&#8239;kg' ],
			[ 'Es hat 100 &deg;C', 'Es hat 100&#8239;&deg;C' ],
		];
	}

	/**
	 * Test unit_spacing with true narrow no-break space and dewidowing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_unit_spacing_dewidow_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_unit_spacing_dewidow( $input, $result ) {
		$this->s->set_unit_spacing( true );
		$this->s->set_true_no_break_narrow_space( true );
		$this->s->set_dewidow( true );
		$this->s->set_max_dewidow_pull( 10 );
		$this->s->set_max_dewidow_length( 3 );
		$this->s->set_dewidow_word_number( 2 );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Provide data for testing unit_spacing.
	 *
	 * @return array
	 */
	public function provide_numbered_abbreviation_spacing_data() {
		return [
			[ 'ÖNORM A 1080:2007', '&Ouml;NORM A&nbsp;1080:2007' ],
			[ 'Das steht in der ÖNORM EN ISO 13920!', 'Das steht in der &Ouml;NORM EN ISO&nbsp;13920!' ],
			[ 'ONR 191160:2010', 'ONR&nbsp;191160:2010' ],
			[ 'DIN ISO 2936', 'DIN ISO&nbsp;2936' ],
			[ 'DIN ISO/IEC 10561', 'DIN ISO/IEC&nbsp;10561' ],
			[ 'VG 96936', 'VG&nbsp;96936' ],
			[ 'LN 9118-2', 'LN&nbsp;9118-2' ],
			[ 'DIN 5032 Lichtmessung', 'DIN&nbsp;5032 Lichtmessung' ],
			[ 'DIN EN 118 Holzschutzmittel', 'DIN EN&nbsp;118 Holzschutzmittel' ],
			[ 'DIN EN ISO 9001 Qualitätsmanagementsysteme', 'DIN EN ISO&nbsp;9001 Qualit&auml;tsmanagementsysteme' ],
			[ 'Enthält E 100.', 'Enth&auml;lt E&nbsp;100.' ],
			[ 'E 160a', 'E&nbsp;160a' ],
			[ 'ISO/IEC 13818', 'ISO/IEC&nbsp;13818' ],
		];
	}

	/**
	 * Test numbered_abbreviation_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_numbered_abbreviation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_numbered_abbreviation_spacing( $input, $result ) {
		$this->s->set_numbered_abbreviation_spacing( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Test numbered_abbreviation_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_numbered_abbreviation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_numbered_abbreviation_spacing_off( $input, $result ) {
		$this->s->set_numbered_abbreviation_spacing( false );

		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $this->typo->process( $input, $this->s ) ) );
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
			[ '<a href="#">foo</a>: bar', '<a href="#">foo</a>&nbsp;: bar', true ],
			[ 'À «<a href="#">programmer</a>»:', '&Agrave; &laquo;&#8239;<a href="#">programmer</a>&#8239;&raquo;&nbsp;:', true ],
		];
	}


	/**
	 * Test french_punctuation_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input             HTML input.
	 * @param string $result            Expected result.
	 * @param bool   $use_french_quotes Enable French primary quotes style.
	 */
	public function test_french_punctuation_spacing( $input, $result, $use_french_quotes ) {
		$this->s->set_french_punctuation_spacing( true );
		$this->s->set_true_no_break_narrow_space( true );

		if ( $use_french_quotes ) {
			$this->s->set_smart_quotes_primary( 'doubleGuillemetsFrench' );
			$this->s->set_smart_quotes_secondary();
			$this->s->set_smart_quotes( true );
		}

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test french_punctuation_spacing.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_french_punctuation_spacing_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_french_punctuation_spacing_off( $input, $result ) {
		$this->s->set_french_punctuation_spacing( false );

		$this->assertSame( $this->clean_html( $input ), $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}

	/**
	 * Provide data for testing wrap_hard_hyphens.
	 *
	 * @return array
	 */
	public function provide_wrap_hard_hyphens_data() {
		return [
			[ 'This-is-a-hyphenated-word', 'This-&#8203;is-&#8203;a-&#8203;hyphenated-&#8203;word', 'This&#8208;&#8203;is&#8208;&#8203;a&#8208;&#8203;hyphenated&#8208;&#8203;word' ],
			[ 'This-is-a-hyphenated-', 'This-&#8203;is-&#8203;a-&#8203;hyphenated-', 'This&#8208;&#8203;is&#8208;&#8203;a&#8208;&#8203;hyphenated&#8208;' ],

		];
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_hard_hyphens( $input, $result ) {
		$this->typo->process( '', $this->s );
		$this->s->set_wrap_hard_hyphens( true );
		$s = $this->s;

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input   HTML input.
	 * @param string $ignored Ignored.
	 * @param string $result  Expected result.
	 */
	public function test_wrap_hard_hyphens_with_smart_dashes( $input, $ignored, $result ) {
		$this->typo->process( '', $this->s );
		$this->s->set_wrap_hard_hyphens( true );
		$this->s->set_smart_dashes( true );
		$this->s->set_smart_dashes_style();

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test wrap_hard_hyphens.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_hard_hyphens_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_hard_hyphens_off( $input, $result ) {
		$this->typo->process( '', $this->s );
		$this->s->set_wrap_hard_hyphens( false );

		$this->assertSame( $input, $this->typo->process( $input, $this->s ) );
	}

	/**
	 * Provide data for testing dewidowing.
	 *
	 * @return array
	 */
	public function provide_dewidow_data() {
		return [
			[ 'bla foo b', 'bla foo&nbsp;b', 3, 2, 1 ],
			[ 'bla foo&thinsp;b', 'bla foo&thinsp;b', 3, 2, 1 ], // don't replace thin space...
			[ 'bla foo&#8202;b', 'bla foo&#8202;b', 3, 2, 1 ],   // ... or hair space.
			[ 'bla foo bar', 'bla foo bar', 2, 2, 1 ],
			[ 'bla foo bär...', 'bla foo&nbsp;b&auml;r...', 3, 3, 1 ],
			[ 'bla foo&nbsp;bär...', 'bla foo&nbsp;b&auml;r...', 3, 3, 1 ],
			[ 'bla föö&#8203;bar s', 'bla f&ouml;&ouml;&#8203;bar&nbsp;s', 3, 2, 1 ],
			[ 'bla foo&#8203;bar s', 'bla foo&#8203;bar s', 2, 2, 1 ],
			[ 'bla foo&shy;bar', 'bla foo&shy;bar', 3, 3, 1 ], // &shy; not matched.
			[ 'bla foo&shy;bar bar', 'bla foo&shy;bar&nbsp;bar', 3, 3, 1 ], // &shy; not matched, but syllable after is.
			[ 'bla foo&#8203;bar bar', 'bla foo&#8203;bar&nbsp;bar', 3, 3, 1 ],
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 3, 3, 1 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 3, 3, 2 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 3, 7, 1 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 8, 4, 1 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar&nbsp;bar', 7, 7, 2 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 1 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 2 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 3 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 7, 1 ],
			[ 'bla foo bar bar', 'bla foo&nbsp;bar&nbsp;bar', 3, 7, 2 ],
			[ 'bla foo bar bar', 'bla foo&nbsp;bar&nbsp;bar', 3, 7, 3 ],
			[ 'bla bla foo bar bar', 'bla bla foo bar&nbsp;bar', 3, 11, 1 ],
			[ 'bla bla foo bar bar', 'bla bla foo&nbsp;bar&nbsp;bar', 3, 11, 2 ],
			[ 'bla bla foo bar bar', 'bla bla&nbsp;foo&nbsp;bar&nbsp;bar', 3, 11, 3 ],
		];
	}

	/**
	 * Provide data for testing dewidowing.
	 *
	 * @return array
	 */
	public function provide_dewidow_with_hyphenation_data() {
		return [
			[ 'this is riding ri...', 'this is rid&shy;ing&nbsp;ri...', 4, 2, 1 ],
			[ 'this is riding riding', 'this is rid&shy;ing riding', 4, 2, 1 ], // No soft hyphens in widows.
		];
	}

	/**
	 * Test dewidow.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html        HTML input.
	 * @param string $result      Expected result.
	 * @param int    $max_pull    Maximum number of pulled characters.
	 * @param int    $max_length  Maximum word length for dewidowing.
	 * @param int    $word_number Maximum number of words in widow.
	 */
	public function test_dewidow( $html, $result, $max_pull, $max_length, $word_number ) {
		$this->s->set_dewidow( true );
		$this->s->set_max_dewidow_pull( $max_pull );
		$this->s->set_max_dewidow_length( $max_length );
		$this->s->set_dewidow_word_number( $word_number );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test dewidow.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 *
	 * @dataProvider provide_dewidow_with_hyphenation_data
	 *
	 * @param string $html        HTML input.
	 * @param string $result      Expected result.
	 * @param int    $max_pull    Maximum number of pulled characters.
	 * @param int    $max_length  Maximum word length for dewidowing.
	 * @param int    $word_number Maximum number of words in widow.
	 */
	public function test_dewidow_with_hyphenation( $html, $result, $max_pull, $max_length, $word_number ) {
		$this->s->set_dewidow( true );
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'en-US' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_max_dewidow_pull( $max_pull );
		$this->s->set_max_dewidow_length( $max_length );
		$this->s->set_dewidow_word_number( $word_number );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test dewidow.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html        HTML input.
	 * @param string $result      Expected result.
	 * @param int    $max_pull    Maximum number of pulled characters.
	 * @param int    $max_length  Maximum word length for dewidowing.
	 * @param int    $word_number Maximum number of words in widow.
	 */
	public function test_dewidow_off( $html, $result, $max_pull, $max_length, $word_number ) {
		$this->s->set_dewidow( false );
		$this->s->set_max_dewidow_pull( $max_pull );
		$this->s->set_max_dewidow_length( $max_length );
		$this->s->set_dewidow_word_number( $word_number );

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
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
	 * @coversNothing
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
	public function test_wrap_urls( $input, $result, $min_after ) {
		$this->s->set_url_wrap( true );
		$this->s->set_min_after_url_wrap( $min_after );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $input, $this->s ) ) );
	}


	/**
	 * Test wrap_urls.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_urls_data
	 *
	 * @param string $html       HTML input.
	 * @param string $result     Expected result.
	 * @param int    $min_after  Minimum number of characters after URL wrapping.
	 */
	public function test_wrap_urls_off( $html, $result, $min_after ) {
		$this->s->set_url_wrap( false );
		$this->s->set_min_after_url_wrap( $min_after );

		$this->assertSame( $html, $this->typo->process( $html, $this->s ) );
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
	 * Test wrap_emails.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_emails( $html, $result ) {
		$this->s->set_email_wrap( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test wrap_emails.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_wrap_emails_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_wrap_emails_off( $html, $result ) {
		$this->s->set_email_wrap( false );

		$this->assertSame( $html, $this->typo->process( $html, $this->s ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps( $html, $result ) {
		$this->s->set_style_caps( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test style_caps.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_caps_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps_off( $html, $result ) {
		$this->s->set_style_caps( false );

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test replace_node_with_html.
	 *
	 * @covers ::replace_node_with_html
	 */
	public function test_replace_node_with_html() {
		$s   = $this->s;
		$dom = $this->typo->parse_html( $this->typo->get_html5_parser(), '<p>foo</p>', $s );

		$this->assertInstanceOf( '\DOMDocument', $dom );
		$original_node = $dom->getElementsByTagName( 'p' )->item( 0 );
		$parent        = $original_node->parentNode;
		$new_nodes     = (array) $this->typo->replace_node_with_html( $original_node, '<div><span>bar</span></div>' );

		$this->assertTrue( is_array( $new_nodes ) );
		$this->assertContainsOnlyInstancesOf( '\DOMNode', $new_nodes );
		foreach ( $new_nodes as $node ) {
			$this->assertSame( $parent, $node->parentNode );
		}
	}

	/**
	 * Tests get_registry.
	 *
	 * @covers ::get_registry
	 *
	 * @uses ::__construct
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_registry_default_registry() {

		m::mock( 'alias:' . Default_Registry::class );
		$typo = m::mock( PHP_Typography::class, [] )->makePartial();

		$this->assertInstanceOf( Default_Registry::class, $typo->get_registry() );
	}

	/**
	 * Tests get_registry.
	 *
	 * @covers ::get_registry
	 *
	 * @uses ::__construct
	 */
	public function test_get_registry_update_hyhpenator_cache() {
		$reg  = m::mock( Registry::class )->makePartial();
		$typo = m::mock( PHP_Typography::class, [ $reg ] )->makePartial();
		$typo->shouldReceive( 'get_hyphenator_cache' )->once()->andReturn( m::mock( Hyphenator_Cache::class ) );
		$reg->shouldReceive( 'update_hyphenator_cache' )->once()->with( m::type( Hyphenator_Cache::class ) );

		$this->assertInstanceOf( Registry::class, $typo->get_registry() );
	}

	/**
	 * Test replace_node_with_html.
	 *
	 * @covers ::replace_node_with_html
	 */
	public function test_replace_node_with_html_invalid() {

		$node     = new \DOMText( 'foo' );
		$new_node = $this->typo->replace_node_with_html( $node, 'bar' );

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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_numbers( $html, $result ) {
		$this->s->set_style_numbers( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test style_numbers.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_numbers_off( $html, $result ) {
		$this->s->set_style_numbers( false );

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
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
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_caps_and_numbers_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_caps_and_numbers( $html, $result ) {
		$this->s->set_style_caps( true );
		$this->s->set_style_numbers( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_hanging_punctuation( $html, $result ) {
		$this->s->set_style_hanging_punctuation( true );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test style_hanging_punctuation.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_hanging_punctuation_data
	 *
	 * @param string $html   HTML input.
	 * @param string $result Expected result.
	 */
	public function test_style_hanging_punctuation_off( $html, $result ) {
		$this->s->set_style_hanging_punctuation( false );

		$this->assertSame( $this->clean_html( $html ), $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}


	/**
	 * Test style_ampersands.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_style_ampersands() {
		$this->s->set_style_ampersands( true );

		$this->assertSame( 'foo <span class="amp">&amp;</span> bar', $this->clean_html( $this->typo->process( 'foo & bar', $this->s ) ) );
	}


	/**
	 * Test style_ampersands.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_style_ampersands_off() {
		$this->s->set_style_ampersands( false );

		$this->assertSame( 'foo &amp; bar', $this->clean_html( $this->typo->process( 'foo & bar', $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $html     HTML input.
	 * @param string $result   Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_style_initial_quotes( $html, $result, $is_title ) {
		$this->s->set_style_initial_quotes( true );
		$this->s->set_initial_quote_tags();

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s, $is_title ) ) );
	}


	/**
	 * Test style_initial_quotes.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_style_initial_quotes_data
	 *
	 * @param string $html     HTML input.
	 * @param string $result   Expected result.
	 * @param bool   $is_title Treat as heading tag.
	 */
	public function test_style_initial_quotes_off( $html, $result, $is_title ) {
		$this->s->set_style_initial_quotes( false );
		$this->s->set_initial_quote_tags();

		$this->assertSame( $html, $this->typo->process( $html, $this->s, $is_title ) );
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
			[ 'В зависимости от региона, может выращиватся на зерно и силос. После колосовых может выращиватся на второй посев.', 'В за&shy;ви&shy;си&shy;мо&shy;сти от ре&shy;ги&shy;о&shy;на, мо&shy;жет вы&shy;ра&shy;щи&shy;ват&shy;ся на зер&shy;но и си&shy;лос. По&shy;сле ко&shy;ло&shy;со&shy;вых мо&shy;жет вы&shy;ра&shy;щи&shy;ват&shy;ся на вто&shy;рой по&shy;сев.', 'ru', true, true, true, false ],
			[ 'В зависимости от Geschäftsübernahme, может выращиватся на зерно и силос. После колосовых может выращиватся на второй посев.', 'В за&shy;ви&shy;си&shy;мо&shy;сти от Gesch&auml;fts&uuml;bernahme, мо&shy;жет вы&shy;ра&shy;щи&shy;ват&shy;ся на зер&shy;но и си&shy;лос. По&shy;сле ко&shy;ло&shy;со&shy;вых мо&shy;жет вы&shy;ра&shy;щи&shy;ват&shy;ся на вто&shy;рой по&shy;сев.', 'ru', true, true, true, false ],
			[ 'Diözesankönigspaar', 'Di&ouml;&shy;ze&shy;san&shy;k&ouml;&shy;nigs&shy;paar', 'de', true, true, true, true ],
			[ 'Schützenbruderschaften', 'Sch&uuml;t&shy;zen&shy;bru&shy;der&shy;schaf&shy;ten', 'de', true, true, true, true ],
			[ 'Bundesjungschützentag', 'Bun&shy;des&shy;jung&shy;sch&uuml;t&shy;zen&shy;tag', 'de', true, true, true, true ],
		];
	}


	/**
	 * Test hyphenate.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
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
		$this->s->set_hyphenation( false );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenate_compounds( $hyphenate_compunds );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertSame( $html, $this->typo->process( $html, $this->s ) );
	}


	/**
	 * Test hyphenate.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
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
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenate_compounds( $hyphenate_compunds );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
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
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
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
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenate_compounds( $hyphenate_compunds );
		$this->s->set_hyphenation_exceptions( $exceptions );

		$this->assertSame( $result, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test hyphenate.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 */
	public function test_hyphenate_headings_disabled() {

		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'en-US' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( true );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$html = '<h2>A few words to hyphenate, like KINGdesk. Really, there should be no hyphenation here!</h2>';
		$this->assertSame( $html, $this->clean_html( $this->typo->process( $html, $this->s ) ) );
	}

	/**
	 * Test hyphenate.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_hyphenate_no_custom_exceptions() {

		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'en-US' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( true );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( true );

		$this->assertSame(
			'A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!',
			$this->clean_html( $this->typo->process( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', $this->s ) )
		);
	}


	/**
	 * Test hyphenate.
	 *
	 * @coversNothing
	 *
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_hyphenate_no_exceptions_at_all() {

		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'en-US' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( true );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( true );
		$s = $this->s;

		$s['hyphenationPatternExceptions'] = [];
		unset( $s['hyphenationExceptions'] );

		$this->assertSame(
			'A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!',
			$this->clean_html( $this->typo->process( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', $s, false ) )
		);
	}

	/**
	 * Test get_html.
	 *
	 * @covers ::get_html5_parser
	 */
	public function test_get_html5_parser() {

		$this->assert_attribute_empty( 'html5_parser', $this->typo );

		$parser1 = $this->typo->get_html5_parser();
		$this->assertInstanceOf( '\Masterminds\HTML5', $parser1 );

		$parser2 = $this->typo->get_html5_parser();
		$this->assertInstanceOf( '\Masterminds\HTML5', $parser2 );

		$this->assertSame( $parser1, $parser2 );
		$this->assert_attribute_instance_of( '\Masterminds\HTML5', 'html5_parser', $this->typo );
	}

	/**
	 * Test parse_html.
	 *
	 * @covers ::parse_html
	 */
	public function test_parse_html() {
		$dom = $this->typo->parse_html( $this->typo->get_html5_parser(), '<p>some text</p>', $this->s );

		$this->assertInstanceOf( \DOMDocument::class, $dom );
		$this->assertEquals( 1, $dom->getElementsByTagName( 'p' )->length );
	}


	/**
	 * Test parse_html.
	 *
	 * @covers ::parse_html
	 *
	 * @uses PHP_Typography\DOM::has_class
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string $html    HTML input.
	 * @param string $ignore1 Ignored.
	 * @param string $ignore2 Ignored.
	 */
	public function test_parse_html_extended( $html, $ignore1, $ignore2 ) {
		$p   = $this->typo->get_html5_parser();
		$dom = $this->typo->parse_html( $p, $html, $this->s );

		$this->assertInstanceOf( \DOMDocument::class, $dom );

		// Serialize the stuff again.
		$xpath     = new \DOMXPath( $dom );
		$body_node = $xpath->query( '/html/body' )->item( 0 );

		$this->assertFalse( DOM::has_class( $body_node, 'bar' ) );
		$this->assertFalse( DOM::has_class( $body_node, 'foo' ) );
		$this->assertEquals( $html, $p->saveHTML( $body_node->childNodes ) );
	}

	/**
	 * Test parse_html with injected classes.
	 *
	 * @covers ::parse_html
	 *
	 * @uses PHP_Typography\DOM::has_class
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param string $html    HTML input.
	 * @param string $ignore1 Ignored.
	 * @param string $ignore2 Ignored.
	 */
	public function test_parse_html_extended_with_classes( $html, $ignore1, $ignore2 ) {
		$p   = $this->typo->get_html5_parser();
		$dom = $this->typo->parse_html( $p, $html, $this->s, [ 'foo', 'bar' ] );

		$this->assertInstanceOf( \DOMDocument::class, $dom );

		// Serialize the stuff again.
		$xpath     = new \DOMXPath( $dom );
		$body_node = $xpath->query( '/html/body' )->item( 0 );

		$this->assertTrue( DOM::has_class( $body_node, 'bar' ) );
		$this->assertTrue( DOM::has_class( $body_node, 'foo' ) );
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
			[ '<a href="http://example.org?foo=xx&+dark">foobar</a>', "Line 1, Col 62: No match in entity table for ''" ],
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
		$s = $this->s;

		// Without an error handler.
		$dom = $this->typo->parse_html( $this->typo->get_html5_parser(), $html, $s );
		$this->assertNull( $dom );

		// With error handler.
		$s->set_parser_errors_handler(
			function ( $errors ) {
				foreach ( $errors as $error ) {
					echo $error;
				}

				return [];
			}
		);

		$this->expectOutputString( $error_msg );
		$dom = $this->typo->parse_html( $this->typo->get_html5_parser(), $html, $s );
		$this->assertInstanceOf( 'DOMDocument', $dom );
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
	 * @covers ::arrays_intersect
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

		$this->assertSame( $result, $this->invoke_static_method( PHP_Typography::class, 'arrays_intersect', [ $array1, $array2 ] ) );
	}

	/**
	 * Tests get_hyphenator_cache.
	 *
	 * @covers ::get_hyphenator_cache
	 */
	public function test_get_hyphenator_cache() {
		$cache = $this->typo->get_hyphenator_cache();

		$this->assertInstanceOf( Hyphenator_Cache::class, $cache );
	}

	/**
	 * Tests set_hyphenator_cache.
	 *
	 * @covers ::set_hyphenator_cache
	 *
	 * @uses ::get_hyphenator_cache
	 */
	public function test_set_hyphenator_cache() {
		$new_cache = m::mock( Hyphenator_Cache::class );
		$old_cache = $this->typo->get_hyphenator_cache();

		$this->assertNotSame( $old_cache, $new_cache );

		$this->typo->set_hyphenator_cache( $new_cache );
		$this->assertSame( $new_cache, $this->typo->get_hyphenator_cache() );
	}

	/**
	 * Tests set_hyphenator_cache with registry.
	 *
	 * @covers ::set_hyphenator_cache
	 */
	public function test_set_hyphenator_cache_with_registry() {
		$reg  = m::mock( Registry::class )->makePartial();
		$typo = m::mock( PHP_Typography::class, [ $reg ] )->makePartial();

		$new_cache = m::mock( Hyphenator_Cache::class );

		$reg->shouldReceive( 'update_hyphenator_cache' )->once()->with( $new_cache );

		$this->assertNull( $typo->set_hyphenator_cache( $new_cache ) ); // @phpstan-ignore-line
	}
}
