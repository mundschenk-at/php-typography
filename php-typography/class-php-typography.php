<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
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
 *  ***
 *
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

/**
 * A few utility functions.
 */
require_once __DIR__ . '/php-typography-functions.php'; // @codeCoverageIgnore

/**
 * HTML5-PHP - a DOM-based HTML5 parser
 */
require_once dirname( __DIR__ ) . '/vendor/Masterminds/HTML5.php';          // @codeCoverageIgnore
require_once dirname( __DIR__ ) . '/vendor/Masterminds/HTML5/autoload.php'; // @codeCoverageIgnore

/**
 * Parses HTML5 (or plain text) and applies various typographic fixes to the text.
 *
 * If used with multibyte language, UTF-8 encoding is required.
 *
 * Portions of this code have been inspired by:
 *  - typogrify (https://code.google.com/p/typogrify/)
 *  - WordPress code for wptexturize (https://developer.wordpress.org/reference/functions/wptexturize/)
 *  - PHP SmartyPants Typographer (https://michelf.ca/projects/php-smartypants/typographer/)
 *
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
 */
class PHP_Typography {

	/**
	 * A hashmap of settings for the various typographic options.
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * A custom parser for \DOMText to separate words, whitespace etc. for HTML injection.
	 *
	 * @var Parse_Text
	 */
	private $text_parser;

	/**
	 * A DOM-based HTML5 parser.
	 *
	 * @var \Masterminds\HTML5
	 */
	private $html5_parser;

	/**
	 * An array of ( $tag => true ) for quick checking with `isset`.
	 *
	 * @var array
	 */
	private $heading_tags = array( 'h1' => true, 'h2' => true, 'h3' => true, 'h4' => true, 'h5' => true, 'h6' => true );

	/**
	 * An array of encodings in detection order.
	 *
	 * @var array
	 */
	private $encodings = array( 'ASCII', 'UTF-8' );

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @var array $encoding => array( 'strlen' => $function_name, ... ).
	 */
	private $str_functions = array(
		'UTF-8' => array(
			'strlen'     => 'mb_strlen',
			'str_split'  => '\PHP_Typography\mb_str_split',
			'strtolower' => 'mb_strtolower',
			'substr'     => 'mb_substr',
			'u'          => 'u', // unicode flag for regex.
		),
		'ASCII' => array(
			'strlen'     => 'strlen',
			'str_split'  => 'str_split',
			'strtolower' => 'strtolower',
			'substr'     => 'substr',
			'u'          => '', // no regex flag needed.
		),
		false   => array(),
	);

	/**
	 * An array in the form of [ '$style' => [ 'open' => $chr, 'close' => $chr ] ]
	 *
	 * @var array
	 */
	private $quote_styles = array();

	/**
	 * An array in the form of [ '$style' => [ 'parenthetical' => $chr, 'interval' => $chr ] ]
	 *
	 * @var array
	 */
	private $dash_styles = array();

	/**
	 * An array in the form of [ '$tag' => true ]
	 *
	 * @var array
	 */
	private $block_tags = array();

	/**
	 * An array of CSS classes that are added for ampersands, numbers etc that can be overridden in a subclass.
	 *
	 * @var array
	 */
	protected $css_classes = array(
		'caps'        => 'caps',
		'numbers'     => 'numbers',
		'amp'         => 'amp',
		'quo'         => 'quo',
		'dquo'        => 'dquo',
		'pull-single' => 'pull-single',
		'pull-double' => 'pull-double',
		'push-single' => 'push-single',
		'push-double' => 'push-double',
		'numerator'   => 'numerator',
		'denominator' => 'denominator',
		'ordinal'     => 'ordinal',
	);

	/**
	 * Set up a new PHP_Typography object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 * @param string  $init         Flag to control initialization. Valid inputs are 'now' and 'lazy'. Optional. Default 'now'.
	 */
	function __construct( $set_defaults = true, $init = 'now' ) {

		// ASCII has to be first to have chance at detection.
		mb_detect_order( $this->encodings );

		// Not sure if this is necessary - but error_log seems to have problems with the strings.
		// Used as the default encoding for mb_* functions.
		$encoding_set = mb_internal_encoding( 'UTF-8' );

		if ( 'now' === $init ) {
			$this->init( $set_defaults );
		}
	}

	/**
	 * Initialize the PHP_Typography object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	function init( $set_defaults = true ) {
		$this->block_tags = array_flip( array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ), function( $tag ) { return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::BLOCK_TAG ); } )
										+ array( 'li', 'td', 'dt' ) ); // not included as "block tags" in current HTML5-PHP version.

		$this->settings = new Settings( $set_defaults );
	}

	/**
	 * (Re)set various options to their default values.
	 */
	function set_defaults() {
		// General attributes.
		$this->set_tags_to_ignore();
		$this->set_classes_to_ignore();
		$this->set_ids_to_ignore();

		// Smart characters.
		$this->set_smart_quotes();
		$this->set_smart_quotes_primary();   // added in version 1.15.
		$this->set_smart_quotes_secondary(); // added in version 1.15.
		$this->set_smart_dashes();
		$this->set_smart_dashes_style();
		$this->set_smart_ellipses();
		$this->set_smart_diacritics();
		$this->set_diacritic_language();
		$this->set_diacritic_custom_replacements();
		$this->set_smart_marks();
		$this->set_smart_ordinal_suffix();
		$this->set_smart_math();
		$this->set_smart_fractions();
		$this->set_smart_exponents();

		// Smart spacing.
		$this->set_single_character_word_spacing();
		$this->set_fraction_spacing();
		$this->set_unit_spacing();
		$this->set_french_punctuation_spacing();
		$this->set_units();
		$this->set_dash_spacing();
		$this->set_dewidow();
		$this->set_max_dewidow_length();
		$this->set_max_dewidow_pull();
		$this->set_wrap_hard_hyphens();
		$this->set_url_wrap();
		$this->set_email_wrap();
		$this->set_min_after_url_wrap();
		$this->set_space_collapse();
		$this->set_true_no_break_narrow_space();

		// Character styling.
		$this->set_style_ampersands();
		$this->set_style_caps();
		$this->set_style_initial_quotes();
		$this->set_style_numbers();
		$this->set_style_hanging_punctuation();
		$this->set_initial_quote_tags();

		// Hyphenation.
		$this->set_hyphenation();
		$this->set_hyphenation_language();
		$this->set_min_length_hyphenation();
		$this->set_min_before_hyphenation();
		$this->set_min_after_hyphenation();
		$this->set_hyphenate_headings();
		$this->set_hyphenate_all_caps();
		$this->set_hyphenate_title_case();   // added in version 1.5.
		$this->set_hyphenate_compounds();
		$this->set_hyphenation_exceptions();
	}

	/**
	 * Enable usage of true "no-break narrow space" (&#8239;) instead of the normal no-break space (&nbsp;).
	 *
	 * @param boolean $on Optional. Default false.
	 */
	function set_true_no_break_narrow_space( $on = false ) {
		$this->settings->set_true_no_break_narrow_space( $on );
	}

	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_tags_to_ignore( $tags = array( 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ) ) {
		$this->settings->set_tags_to_ignore( $tags );
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|array $classes A comma separated list or an array of class names.
	 */
	 function set_classes_to_ignore( $classes = array( 'vcard', 'noTypo' ) ) {
		$this->settings->set_classes_to_ignore( $classes );
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|array $ids A comma separated list or an array of tag names.
	 */
	function set_ids_to_ignore( $ids = array() ) {
		$this->settings->set_ids_to_ignore( $ids );
	}

	/**
	 * Enable/disable typographic quotes.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_quotes( $on = true ) {
		$this->settings->set_smart_quotes( $on );
	}

	/**
	 * Set the style for primary ('double') quotemarks.
	 *
	 * Allowed values for $style:
	 * "doubleCurled" => "&ldquo;foo&rdquo;",
	 * "doubleCurledReversed" => "&rdquo;foo&rdquo;",
	 * "doubleLow9" => "&bdquo;foo&rdquo;",
	 * "doubleLow9Reversed" => "&bdquo;foo&ldquo;",
	 * "singleCurled" => "&lsquo;foo&rsquo;",
	 * "singleCurledReversed" => "&rsquo;foo&rsquo;",
	 * "singleLow9" => "&sbquo;foo&rsquo;",
	 * "singleLow9Reversed" => "&sbquo;foo&lsquo;",
	 * "doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
	 * "doubleGuillemets" => "&laquo;foo&raquo;",
	 * "doubleGuillemetsReversed" => "&raquo;foo&laquo;",
	 * "singleGuillemets" => "&lsaquo;foo&rsaquo;",
	 * "singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
	 * "cornerBrackets" => "&#x300c;foo&#x300d;",
	 * "whiteCornerBracket" => "&#x300e;foo&#x300f;"
	 *
	 * @param string $style Defaults to 'doubleCurled.
	 */
	function set_smart_quotes_primary( $style = 'doubleCurled' ) {
		$this->settings->set_smart_quotes_primary( $style );
	}

	/**
	 * Set the style for secondary ('single') quotemarks.
	 *
	 * Allowed values for $style:
	 * "doubleCurled" => "&ldquo;foo&rdquo;",
	 * "doubleCurledReversed" => "&rdquo;foo&rdquo;",
	 * "doubleLow9" => "&bdquo;foo&rdquo;",
	 * "doubleLow9Reversed" => "&bdquo;foo&ldquo;",
	 * "singleCurled" => "&lsquo;foo&rsquo;",
	 * "singleCurledReversed" => "&rsquo;foo&rsquo;",
	 * "singleLow9" => "&sbquo;foo&rsquo;",
	 * "singleLow9Reversed" => "&sbquo;foo&lsquo;",
	 * "doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
	 * "doubleGuillemets" => "&laquo;foo&raquo;",
	 * "doubleGuillemetsReversed" => "&raquo;foo&laquo;",
	 * "singleGuillemets" => "&lsaquo;foo&rsaquo;",
	 * "singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
	 * "cornerBrackets" => "&#x300c;foo&#x300d;",
	 * "whiteCornerBracket" => "&#x300e;foo&#x300f;"
	 *
	 * @param string $style Defaults to 'singleCurled'.
	 */
	function set_smart_quotes_secondary( $style = 'singleCurled' ) {
		$this->settings->set_smart_quotes_secondary( $style );
	}

	/**
	 * Enable/disable replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_dashes( $on = true ) {
		$this->settings->set_smart_dashes( $on );
	}

	/**
	 * Sets the typographical conventions used by smart_dashes.
	 *
	 * Allowed values for $style:
	 * - "traditionalUS"
	 * - "international"
	 *
	 * @param string $style Optional. Default "englishTraditional".
	 */
	function set_smart_dashes_style( $style = 'traditionalUS' ) {
		$this->settings->set_smart_dashes_style( $style );
	}

	/**
	 * Enable/disable replacement of "..." with "…".
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_ellipses( $on = true ) {
		$this->settings->set_smart_ellipses( $on );
	}

	/**
	 * Enable/disable replacement "creme brulee" with "crème brûlée".
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_diacritics( $on = true ) {
		$this->settings->set_smart_diacritics( $on );
	}

	/**
	 * Set the language used for diacritics replacements.
	 *
	 * @param string $lang Has to correspond to a filename in 'diacritics'. Optional. Default 'en-US'.
	 */
	function set_diacritic_language( $lang = 'en-US' ) {
		$this->settings->set_diacritic_language( $lang );
	}

	/**
	 * Set up custom diacritics replacements.
	 *
	 * @param string|array $replacements An array formatted array(needle=>replacement, needle=>replacement...),
	 *                                   or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 */
	function set_diacritic_custom_replacements( $replacements = array() ) {
		$this->settings->set_diacritic_custom_replacements( $replacements );
	}

	/**
	 * Enable/disable replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_marks( $on = true ) {
		$this->settings->set_smart_marks( $on );
	}

	/**
	 * Enable/disable proper mathematical symbols.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_math( $on = true ) {
		$this->settings->set_smart_math( $on );
	}

	/**
	 * Enable/disable replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_exponents( $on = true ) {
		$this->settings->set_smart_exponents( $on );
	}

	/**
	 * Enable/disable replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_fractions( $on = true ) {
		$this->settings->set_smart_fractions( $on );
	}

	/**
	 * Enable/disable replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_ordinal_suffix( $on = true ) {
		$this->settings->set_smart_ordinal_suffix( $on );
	}

	/**
	 * Enable/disable forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_single_character_word_spacing( $on = true ) {
		$this->settings->set_single_character_word_spacing( $on );
	}

	/**
	 * Enable/disable fraction spacing.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_fraction_spacing( $on = true ) {
		$this->settings->set_fraction_spacing( $on );
	}

	/**
	 * Enable/disable keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_unit_spacing( $on = true ) {
		$this->settings->set_unit_spacing( $on );
	}

	/**
	 * Enable/disable extra whitespace before certain punction marks, as is the French custom.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_french_punctuation_spacing( $on = true ) {
		$this->settings->set_french_punctuation_spacing( $on );
	}

	/**
	 * Set the list of units to keep together with their values.
	 *
	 * @param string|array $units A comma separated list or an array of units.
	 */
	function set_units( $units = array() ) {
		$this->settings->set_units( $units );
	}

	/**
	 * Enable/disable wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_dash_spacing( $on = true ) {
		$this->settings->set_dash_spacing( $on );
	}

	/**
	 * Enable/disable removal of extra whitespace characters.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_space_collapse( $on = true ) {
		$this->settings->set_space_collapse( $on );
	}

	/**
	 * Enable/disable widow handling.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_dewidow( $on = true ) {
		$this->settings->set_dewidow( $on );
	}

	/**
	 * Set the maximum length of widows that will be protected.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_length( $length = 5 ) {
		$this->settings->set_max_dewidow_length( $length );
	}

	/**
	 * Set the maximum length of pulled text to keep widows company.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_pull( $length = 5 ) {
		$this->settings->set_max_dewidow_pull( $length );
	}

	/**
	 * Enable/disable wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_wrap_hard_hyphens( $on = true ) {
		$this->settings->set_wrap_hard_hyphens( $on );
	}

	/**
	 * Enable/disable wrapping of urls.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_url_wrap( $on = true ) {
		$this->settings->set_url_wrap( $on );
	}

	/**
	 * Enable/disable wrapping of email addresses.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_email_wrap( $on = true ) {
		$this->settings->set_email_wrap( $on );
	}

	/**
	 * Set the minimum character requirement after an URL wrapping point.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_url_wrap( $length = 5 ) {
		$this->settings->set_min_after_url_wrap( $length );
	}

	/**
	 * Enable/disable wrapping of ampersands in <span class="amp">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_ampersands( $on = true ) {
		$this->settings->set_style_ampersands( $on );
	}

	/**
	 * Enable/disable wrapping caps in <span class="caps">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_caps( $on = true ) {
		$this->settings->set_style_caps( $on );
	}

	/**
	 * Enable/disable wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_initial_quotes( $on = true ) {
		$this->settings->set_style_initial_quotes( $on );
	}

	/**
	 * Enable/disable wrapping of numbers in <span class="numbers">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_numbers( $on = true ) {
		$this->settings->set_style_numbers( $on );
	}

	/**
	 * Enable/disable wrapping of punctiation and wide characters in <span class="pull-*">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_hanging_punctuation( $on = true ) {
		$this->settings->set_style_hanging_punctuation( $on );
	}

	/**
	 * Set the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_initial_quote_tags( $tags = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ) ) {
		$this->settings->set_initial_quote_tags( $tags );
	}

	/**
	 * Enable/disable hyphenation.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenation( $on = true ) {
		$this->settings->set_hyphenation( $on );
	}

	/**
	 * Set the hyphenation pattern language.
	 *
	 * @param string $lang Has to correspond to a filename in 'lang'. Optional. Default 'en-US'.
	 */
	function set_hyphenation_language( $lang = 'en-US' ) {
		$this->settings->set_hyphenation_language( $lang );
	}

	/**
	 * Set the minimum length of a word that may be hyphenated.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_min_length_hyphenation( $length = 5 ) {
		$this->settings->set_min_length_hyphenation( $length );
	}

	/**
	 * Set the minimum character requirement before a hyphenation point.
	 *
	 * @param number $length Defaults to 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_before_hyphenation( $length = 3 ) {
		$this->settings->set_min_before_hyphenation( $length );
	}

	/**
	 * Set the minimum character requirement after a hyphenation point.
	 *
	 * @param number $length Defaults to 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_hyphenation( $length = 2 ) {
		$length = ( $length > 0 ) ? $length : 2;

		$this->settings->set_min_after_hyphenation( $length );
	}

	/**
	 * Enable/disable hyphenation of titles and headings.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_headings( $on = true ) {
		$this->settings->set_hyphenate_headings( $on );
	}

	/**
	 * Enable/disable hyphenation of words set completely in capital letters.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_all_caps( $on = true ) {
		$this->settings->set_hyphenate_all_caps( $on );
	}

	/**
	 * Enable/disable hyphenation of words starting with a capital letter.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_title_case( $on = true ) {
		$this->settings->set_hyphenate_title_case( $on );
	}

	/**
	 * Enable/disable hyphenation of compound words (e.g. "editor-in-chief").
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_compounds( $on = true ) {
		$this->settings->set_hyphenate_compounds( $on );
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param string|array $exceptions An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
	 *        In the latter case, only alphanumeric characters and hyphens are recognized. The default is empty.
	 */
	function set_hyphenation_exceptions( $exceptions = array() ) {
		$this->settings->set_hyphenation_exceptions( $exceptions );
	}

	/**
	 * Modifies $html according to the defined settings.
	 *
	 * @param string   $html      A HTML fragment.
	 * @param string   $is_title  If the HTML fragment is a title. Optional. Default false.
	 * @param Settings $settings  Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	function process( $html, $is_title = false, Settings $settings = null ) {
		return $this->process_textnodes( $html, array( $this, 'apply_fixes_to_html_node' ), $is_title, $settings );
	}

	/**
	 * Modifies $html according to the defined settings, in a way that is appropriate for RSS feeds
	 * (i.e. excluding processes that may not display well with limited character set intelligence).
	 *
	 * @param string   $html     A HTML fragment.
	 * @param string   $is_title If the HTML fragment is a title. Optional. Default false.
	 * @param Settings $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	function process_feed( $html, $is_title = false, Settings $settings = null ) {
		return $this->process_textnodes( $html, array( $this, 'apply_fixes_to_feed_node' ), $is_title, $settings );
	}

	/**
	 * Applies specific fixes to all textnodes of the HTML fragment.
	 *
	 * @param string   $html     A HTML fragment.
	 * @param callable $fixer    A callback that applies typography fixes to a single textnode.
	 * @param string   $is_title If the HTML fragment is a title. Optional. Default false.
	 * @param Settings $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	public function process_textnodes( $html, $fixer, $is_title = false, Settings $settings = null ) {
		// Don't do aynthing if there is no valid callback.
		if ( ! is_callable( $fixer ) ) {
			trigger_error( 'PHP_Typography::process_textnodes called without a valid callback.', E_USER_WARNING );

			return $html;
		}

		// Use internal settings if necessary.
		if ( empty( $settings ) ) {
			$settings = $this->settings;
		}

		if ( isset( $settings['ignoreTags'] ) && $is_title && ( in_array( 'h1', $settings['ignoreTags'], true ) || in_array( 'h2', $settings['ignoreTags'], true ) ) ) {
			return $html;
		}

		// Lazy-load our parser (the text parser is not needed for feeds).
		$html5_parser = $this->get_html5_parser();

		// Parse the HTML.
		$dom = $this->parse_html( $html5_parser, $html );
		$xpath = new \DOMXPath( $dom );

		// Query some nodes in the DOM.
		$body_node = $xpath->query( '/html/body' )->item( 0 );
		$all_textnodes = $xpath->query( '//text()', $body_node );
		$tags_to_ignore = $this->query_tags_to_ignore( $xpath, $body_node, $settings );

		// Start processing.
		foreach ( $all_textnodes as $textnode ) {
			if ( arrays_intersect( get_ancestors( $textnode ), $tags_to_ignore ) ) {
				continue;
			}

			// We won't be doing anything with spaces, so we can jump ship if that is all we have.
			if ( $textnode->isWhitespaceInElementContent() ) {
				continue;
			}

			// Decode all characters except < > &.
			$textnode->data = htmlspecialchars( $textnode->data, ENT_NOQUOTES, 'UTF-8' ); // returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively).

			// Apply fixes.
			call_user_func( $fixer, $textnode, $settings, $is_title );

			// Until now, we've only been working on a textnode: HTMLify result.
			$this->replace_node_with_html( $textnode, $textnode->data );
		}

		return $html5_parser->saveHTML( $body_node->childNodes );  // @codingStandardsIgnoreLine.
	}

	/**
	 * Apply standard typography fixes to a textnode.
	 *
	 * @param \DOMText $textnode The node to process.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_fixes_to_html_node( \DOMText $textnode, Settings $settings, $is_title = false ) {
		// Nodify anything that requires adjacent text awareness here.
		$this->smart_math( $textnode, $settings );
		$this->smart_diacritics( $textnode, $settings );
		$this->smart_quotes( $textnode, $settings );
		$this->smart_dashes( $textnode, $settings );
		$this->smart_ellipses( $textnode, $settings );
		$this->smart_marks( $textnode, $settings );

		// Keep spacing after smart character replacement.
		$this->single_character_word_spacing( $textnode, $settings );
		$this->dash_spacing( $textnode, $settings );
		$this->unit_spacing( $textnode, $settings );
		$this->french_punctuation_spacing( $textnode, $settings );

		// Parse and process individual words.
		$this->process_words( $textnode, $settings, $is_title );

		// Some final space manipulation.
		$this->dewidow( $textnode, $settings );
		$this->space_collapse( $textnode, $settings );

		// Everything that requires HTML injection occurs here (functions above assume tag-free content)
		// pay careful attention to functions below for tolerance of injected tags.
		$this->smart_ordinal_suffix( $textnode, $settings ); // call before "style_numbers" and "smart_fractions".
		$this->smart_exponents( $textnode, $settings );      // call before "style_numbers".
		$this->smart_fractions( $textnode, $settings );      // call before "style_numbers" and after "smart_ordinal_suffix".
		if ( ! has_class( $textnode, $this->css_classes['caps'] ) ) {
			// Call before "style_numbers".
			$this->style_caps( $textnode, $settings );
		}
		if ( ! has_class( $textnode, $this->css_classes['numbers'] ) ) {
			// Call after "smart_ordinal_suffix", "smart_exponents", "smart_fractions", and "style_caps".
			$this->style_numbers( $textnode, $settings );
		}
		if ( ! has_class( $textnode, $this->css_classes['amp'] ) ) {
			$this->style_ampersands( $textnode, $settings );
		}
		if ( ! has_class( $textnode, array( $this->css_classes['quo'], $this->css_classes['dquo'] ) ) ) {
			$this->style_initial_quotes( $textnode, $settings, $is_title );
		}
		if ( ! has_class( $textnode, array( $this->css_classes['pull-single'], $this->css_classes['pull-double'] ) ) ) {
			$this->style_hanging_punctuation( $textnode, $settings );
		}
	}

	/**
	 * Apply typography fixes specific to RSS feeds to a textnode.
	 *
	 * @param \DOMText $textnode The node to process.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_fixes_to_feed_node( \DOMText $textnode, Settings $settings, $is_title = false ) {
		// Modify anything that requires adjacent text awareness here.
		$this->smart_quotes( $textnode, $settings );
		$this->smart_dashes( $textnode, $settings );
		$this->smart_ellipses( $textnode, $settings );
		$this->smart_marks( $textnode, $settings );
	}

	/**
	 * Tokenize the content of a textnode and process the individual words separately.
	 *
	 * Currently this functions applies the following enhancements:
	 *   - wrapping hard hyphens
	 *   - hyphenation
	 *   - wrapping URLs
	 *   - wrapping email addresses
	 *
	 * @param \DOMText $textnode The textnode to process.
	 * @param Settings $settings The settings to apply.
	 * @param boolean  $is_title If the HTML fragment is a title. Defaults to false.
	 */
	function process_words( \DOMText $textnode, Settings $settings, $is_title = false ) {
		// Lazy-load text parser.
		$text_parser  = $this->get_text_parser();

		// Set up parameters for word categories.
		$mixed_caps       = empty( $settings['hyphenateAllCaps'] ) ? 'allow-all-caps' : 'no-all-caps';
		$letter_caps      = empty( $settings['hyphenateAllCaps'] ) ? 'no-all-caps' : 'allow-all-caps';
		$mixed_compounds  = empty( $settings['hyphenateCompounds'] ) ? 'allow-compounds' : 'no-compounds';
		$letter_compounds = empty( $settings['hyphenateCompounds'] ) ? 'no-compounds' : 'allow-compounds';

		// Break text down for a bit more granularity.
		$text_parser->load( $textnode->data );
		$parsed_mixed_words    = $text_parser->get_words( 'no-all-letters', $mixed_caps, $mixed_compounds );  // prohibit letter-only words, allow caps, allow compounds (or not).
		$parsed_compound_words = ! empty( $settings['hyphenateCompounds'] ) ? $text_parser->get_words( 'no-all-letters', $letter_caps, 'require-compounds' ) : array();
		$parsed_words          = $text_parser->get_words( 'require-all-letters', $letter_caps, $letter_compounds ); // require letter-only words allow/prohibit caps & compounds vice-versa.
		$parsed_other          = $text_parser->get_other();

		// Process individual text parts here.
		$parsed_mixed_words    = $this->wrap_hard_hyphens( $parsed_mixed_words, $settings );
		$parsed_compound_words = $this->hyphenate_compounds( $parsed_compound_words, $settings, $is_title, $textnode );
		$parsed_words          = $this->hyphenate( $parsed_words, $settings, $is_title, $textnode );
		$parsed_other          = $this->wrap_urls( $parsed_other, $settings );
		$parsed_other          = $this->wrap_emails( $parsed_other, $settings );

		// Apply updates to our text.
		$text_parser->update( $parsed_mixed_words + $parsed_compound_words + $parsed_words + $parsed_other );
		$textnode->data = $text_parser->unload();
	}

	/**
	 * Parse HTML5 fragment while ignoring certain warnings for invalid HTML code (e.g. duplicate IDs).
	 *
	 * @param \Masterminds\HTML5 $parser An intialized parser object.
	 * @param string             $html The HTML fragment to parse (not a complete document).
	 *
	 * @return \DOMDocument The encoding has already been set to UTF-8.
	 */
	function parse_html( \Masterminds\HTML5 $parser, $html ) {
		// Silence some parsing errors for invalid HTML.
		set_error_handler( array( $this, 'handle_parsing_errors' ) );
		$xml_error_handling = libxml_use_internal_errors( true );

		// Do the actual parsing.
		$dom = $parser->loadHTML( '<body>' . $html . '</body>' );
		$dom->encoding = 'UTF-8';

		// Restore original error handling.
		libxml_clear_errors();
		libxml_use_internal_errors( $xml_error_handling );
		restore_error_handler();

		return $dom;
	}

	/**
	 * Silently handle certain HTML parsing errors.
	 *
	 * @param int    $errno      Error number.
	 * @param string $errstr     Error message.
	 * @param string $errfile    The file in which the error occurred.
	 * @param int    $errline    The line in which the error occurred.
	 * @param array  $errcontext Calling context.
	 *
	 * @return boolean Returns true if the error was handled, false otherwise.
	 */
	public function handle_parsing_errors( $errno, $errstr, $errfile, $errline, array $errcontext ) {
		if ( ! ( error_reporting() & $errno ) ) { // @codingStandardsIgnoreLine.
			return true; // not interesting.
		}

		if ( $errno & E_USER_WARNING && 0 === substr_compare( $errfile, 'DOMTreeBuilder.php', -18 ) ) {
			// Ignore warnings from parser.
			return true;
		}

		// Let PHP handle the rest.
		return false;
	}

	/**
	 * Retrieve an array of nodes that should be skipped during processing.
	 *
	 * @param \DOMXPath $xpath        A valid XPath instance for the DOM to be queried.
	 * @param \DOMNode  $initial_node The starting node of the XPath query.
	 * @param Settings  $settings     The settings to apply.
	 *
	 * @return array An array of \DOMNode (can be empty).
	 */
	function query_tags_to_ignore( \DOMXPath $xpath, \DOMNode $initial_node, Settings $settings ) {
		$elements = array();
		$query_parts = array();
		if ( ! empty( $settings['ignoreTags'] ) ) {
			$query_parts[] = '//' . implode( ' | //', $settings['ignoreTags'] );
		}
		if ( ! empty( $settings['ignoreClasses'] ) ) {
			$query_parts[] = "//*[contains(concat(' ', @class, ' '), ' " . implode( " ') or contains(concat(' ', @class, ' '), ' ", $settings['ignoreClasses'] ) . " ')]";
		}
		if ( ! empty( $settings['ignoreIDs'] ) ) {
			$query_parts[] = '//*[@id=\'' . implode( '\' or @id=\'', $settings['ignoreIDs'] ) . '\']';
		}

		if ( ! empty( $query_parts ) ) {
			$ignore_query = implode( ' | ', $query_parts );

			if ( false !== ( $nodelist = $xpath->query( $ignore_query, $initial_node ) ) ) {
				$elements = nodelist_to_array( $nodelist );
			}
		}

		return $elements;
	}

	/**
	 * Retrieve the last character of the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element	The content node.
	 * @return string A single character (or the empty string).
	 */
	function get_prev_chr( \DOMNode $element ) {
		$previous_textnode = $this->get_previous_textnode( $element );

		if ( isset( $previous_textnode ) && isset( $previous_textnode->data ) ) {
			// First determine encoding.
			$func = $this->str_functions[ mb_detect_encoding( $previous_textnode->data, $this->encodings, true ) ];

			if ( ! empty( $func ) && ! empty( $func['substr'] ) ) {
				return preg_replace( '/\p{C}/Su', '', $func['substr']( $previous_textnode->data, - 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}

	/**
	 * Retrieve the first character of the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element	The content node.
	 * @return string A single character (or the empty string).
	 */
	function get_next_chr( \DOMNode $element ) {
		$next_textnode = $this->get_next_textnode( $element );

		if ( isset( $next_textnode ) && isset( $next_textnode->data ) ) {
			// First determine encoding.
			$func = $this->str_functions[ mb_detect_encoding( $next_textnode->data, $this->encodings, true ) ];

			if ( ! empty( $func ) && ! empty( $func['substr'] ) ) {
				return preg_replace( '/\p{C}/Su', '', $func['substr']( $next_textnode->data, 0, 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}

	/**
	 * Retrieve the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element	The content node. Optional. Default null.
	 * @return \DOMText Null if $element is a block-level element or no text sibling exists.
	 */
	function get_previous_textnode( \DOMNode $element = null ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		$previous_textnode = null;
		$node = $element;

		if ( $node instanceof \DOMElement && isset( $this->block_tags[ $node->tagName ] ) ) { // @codingStandardsIgnoreLine.
			return null;
		}

		while ( ( $node = $node->previousSibling ) && empty( $previous_textnode ) ) { // @codingStandardsIgnoreLine.
			$previous_textnode = $this->get_last_textnode( $node );
		}

		if ( ! $previous_textnode ) {
			$previous_textnode = $this->get_previous_textnode( $element->parentNode ); // @codingStandardsIgnoreLine.
		}

		return $previous_textnode;
	}

	/**
	 * Retrieve the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element	The content node. Optional. Default null.
	 *
	 * @return \DOMText Null if $element is a block-level element or no text sibling exists.
	 */
	function get_next_textnode( \DOMNode $element = null ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		$next_textnode = null;
		$node = $element;

		if ( $node instanceof \DOMElement && isset( $this->block_tags[ $node->tagName ] ) ) { // @codingStandardsIgnoreLine.
			return null;
		}

		while ( ( $node = $node->nextSibling ) && empty( $next_textnode ) ) { // @codingStandardsIgnoreLine.
			$next_textnode = $this->get_first_textnode( $node );
		}

		if ( ! $next_textnode ) {
			$next_textnode = $this->get_next_textnode( $element->parentNode ); // @codingStandardsIgnoreLine.
		}

		return $next_textnode;
	}

	/**
	 * Retrieve the first \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode $element   Optional. Default null.
	 * @param boolean  $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMNode The first child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	function get_first_textnode( \DOMNode $element = null, $recursive = false ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		if ( $element instanceof \DOMText ) {
			return $element;
		} elseif ( ! $element instanceof \DOMElement ) {
			// Return null if $element is neither \DOMText nor \DOMElement.
			return null;
		} elseif ( $recursive && isset( $this->block_tags[ $element->tagName ] ) ) { // @codingStandardsIgnoreLine.
			return null;
		}

		$first_textnode = null;

		if ( $element->hasChildNodes() ) {
			$children = $element->childNodes; // @codingStandardsIgnoreLine.
			$i = 0;

			while ( $i < $children->length && empty( $first_textnode ) ) {
				$first_textnode = $this->get_first_textnode( $children->item( $i ), true );
				$i++;
			}
		}

		return $first_textnode;
	}

	/**
	 * Retrieve the last \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode $element   Optional. Default null.
	 * @param boolean  $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMNode The last child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	function get_last_textnode( \DOMNode $element = null, $recursive = false ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		if ( $element instanceof \DOMText ) {
			return $element;
		} elseif ( ! $element instanceof \DOMElement ) {
			// Return null if $element is neither \DOMText nor \DOMElement.
			return null;
		} elseif ( $recursive && isset( $this->block_tags[ $element->tagName ] ) ) { // @codingStandardsIgnoreLine.
			return null;
		}

		$last_textnode = null;

		if ( $element->hasChildNodes() ) {
			$children = $element->childNodes; // @codingStandardsIgnoreLine.
			$i = $children->length - 1;

			while ( $i >= 0 && empty( $last_textnode ) ) {
				$last_textnode = $this->get_last_textnode( $children->item( $i ), true );
				$i--;
			}
		}

		return $last_textnode;
	}

	/**
	 * Apply smart quotes (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_quotes( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartQuotes'] ) ) {
			return;
		}

		// Need to get context of adjacent characters outside adjacent inline tags or HTML comment
		// if we have adjacent characters add them to the text.
		$previous_character = $this->get_prev_chr( $textnode );
		if ( '' !== $previous_character ) {
			$textnode->data = $previous_character . $textnode->data;
		}
		$next_character = $this->get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data = $textnode->data . $next_character;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();
		$components = $settings->get_components();

		// Before primes, handle quoted numbers (and quotes ending in numbers).
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuotedNumbers'], $chr['singleQuoteOpen'] . '$1' . $chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuotedNumbers'], $chr['doubleQuoteOpen'] . '$1' . $chr['doubleQuoteClose'], $textnode->data );

		// Guillemets.
		$textnode->data = str_replace( '<<',       $chr['guillemetOpen'],  $textnode->data );
		$textnode->data = str_replace( '&lt;&lt;', $chr['guillemetOpen'],  $textnode->data );
		$textnode->data = str_replace( '>>',       $chr['guillemetClose'], $textnode->data );
		$textnode->data = str_replace( '&gt;&gt;', $chr['guillemetClose'], $textnode->data );

		// Primes.
		$textnode->data = preg_replace( $regex['smartQuotesSingleDoublePrime'],         '$1' . $chr['singlePrime'] . '$2$3' . $chr['doublePrime'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleDoublePrime1Glyph'],   '$1' . $chr['singlePrime'] . '$2$3' . $chr['doublePrime'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime'],               '$1' . $chr['doublePrime'],                                      $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( $regex['smartQuotesSinglePrime'],               '$1' . $chr['singlePrime'],                                      $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSinglePrimeCompound'],       '$1' . $chr['singlePrime'],                                      $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrimeCompound'],       '$1' . $chr['doublePrime'],                                      $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime1Glyph'],         '$1' . $chr['doublePrime'],                                      $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime1GlyphCompound'], '$1' . $chr['doublePrime'],                                      $textnode->data );

		// Backticks.
		$textnode->data = str_replace( '``', $chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = str_replace( '`',  $chr['singleQuoteOpen'],  $textnode->data );
		$textnode->data = str_replace( "''", $chr['doubleQuoteClose'], $textnode->data );

		// Comma quotes.
		$textnode->data = str_replace( ',,', $chr['doubleLow9Quote'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesCommaQuote'], $chr['singleLow9Quote'], $textnode->data ); // like _,¿hola?'_.

		// Apostrophes.
		$textnode->data = preg_replace( $regex['smartQuotesApostropheWords'],   $chr['apostrophe'],      $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesApostropheDecades'], $chr['apostrophe'] . '$1', $textnode->data ); // decades: '98.
		$textnode->data = str_replace( $components['smartQuotesApostropheExceptionMatches'], $components['smartQuotesApostropheExceptionReplacements'], $textnode->data );

		// Quotes.
		$textnode->data = str_replace( $components['smartQuotesBracketMatches'], $components['smartQuotesBracketReplacements'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteOpen'],         $chr['singleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteClose'],        $chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteOpenSpecial'],  $chr['singleQuoteOpen'],  $textnode->data ); // like _'¿hola?'_.
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteCloseSpecial'], $chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteOpen'],         $chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteClose'],        $chr['doubleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteOpenSpecial'],  $chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteCloseSpecial'], $chr['doubleQuoteClose'], $textnode->data );

		// Quote catch-alls - assume left over quotes are closing - as this is often the most complicated position, thus most likely to be missed.
		$textnode->data = str_replace( "'", $chr['singleQuoteClose'], $textnode->data );
		$textnode->data = str_replace( '"', $chr['doubleQuoteClose'], $textnode->data );

		// If we have adjacent characters remove them from the text.
		$func = $this->str_functions[ mb_detect_encoding( $textnode->data, $this->encodings, true ) ];

		if ( '' !== $previous_character ) {
			$textnode->data = $func['substr']( $textnode->data, 1, $func['strlen']( $textnode->data ) );
		}
		if ( '' !== $next_character ) {
			$textnode->data = $func['substr']( $textnode->data, 0, $func['strlen']( $textnode->data ) - 1 );
		}
	}

	/**
	 * Apply smart dashes (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_dashes( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartDashes'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();

		$textnode->data = str_replace( '---', $chr['emDash'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesParentheticalDoubleDash'], "\$1{$chr['parentheticalDash']}\$2", $textnode->data );
		$textnode->data = str_replace( '--', $chr['enDash'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesParentheticalSingleDash'], "\$1{$chr['parentheticalDash']}\$2", $textnode->data );

		$textnode->data = preg_replace( $regex['smartDashesEnDashAll'],          '$1' . $chr['enDash'] . '$2',        $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesEnDashWords'] ,       '$1' . $chr['enDash'] . '$2',        $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesEnDashNumbers'],      '$1' . $chr['intervalDash'] . '$2',  $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesEnDashPhoneNumbers'], '$1' . $chr['noBreakHyphen'] . '$2', $textnode->data ); // phone numbers.
		$textnode->data = str_replace( "xn{$chr['enDash']}",                     'xn--',                              $textnode->data ); // revert messed-up punycode.

		// Revert dates back to original formats
		// YYYY-MM-DD.
		$textnode->data = preg_replace( $regex['smartDashesYYYY-MM-DD'], '$1-$2-$3',     $textnode->data );
		// MM-DD-YYYY or DD-MM-YYYY.
		$textnode->data = preg_replace( $regex['smartDashesMM-DD-YYYY'], '$1$3-$2$4-$5', $textnode->data );
		// YYYY-MM or YYYY-DDDD next.
		$textnode->data = preg_replace( $regex['smartDashesYYYY-MM'],    '$1-$2',        $textnode->data );
	}

	/**
	 * Apply smart ellipses (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	 function smart_ellipses( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartEllipses'] ) ) {
			return;
		}

		$ellipses = $settings->chr( 'ellipses' );

		$textnode->data = str_replace( array( '....', '. . . .' ), '.' . $ellipses, $textnode->data );
		$textnode->data = str_replace( array( '...', '. . .' ),          $ellipses, $textnode->data );
	}

	/**
	 * Apply smart diacritics (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_diacritics( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartDiacritics'] ) ) {
			return; // abort.
		}

		if ( ! empty( $settings['diacriticReplacement'] ) &&
			 ! empty( $settings['diacriticReplacement']['patterns'] ) &&
			 ! empty( $settings['diacriticReplacement']['replacements'] ) ) {

			// Uses "word" => "replacement" pairs from an array to make fast preg_* replacements.
			$replacements = $settings['diacriticReplacement']['replacements'];
			$textnode->data = preg_replace_callback( $settings['diacriticReplacement']['patterns'], function( $match ) use ( $replacements ) {
		 		if ( isset( $replacements[ $match[0] ] ) ) {
		 			return $replacements[ $match[0] ];
		 		} else {
		 			return $match[0];
		 		}
		 	}, $textnode->data );
		}
	}

	/**
	 * Apply smart marks (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_marks( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartMarks'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();
		$components = $settings->get_components();

		// Escape usage of "501(c)(1...29)" (US non-profit).
		$textnode->data = preg_replace( $regex['smartMarksEscape501(c)'], '$1' . $components['escapeMarker'] . '$2' . $components['escapeMarker'] . '$3', $textnode->data );

		// Replace marks.
		$textnode->data = str_replace( array( '(c)', '(C)' ),   $chr['copyright'],      $textnode->data );
		$textnode->data = str_replace( array( '(r)', '(R)' ),   $chr['registeredMark'], $textnode->data );
		$textnode->data = str_replace( array( '(p)', '(P)' ),   $chr['soundCopyMark'],  $textnode->data );
		$textnode->data = str_replace( array( '(sm)', '(SM)' ), $chr['serviceMark'],    $textnode->data );
		$textnode->data = str_replace( array( '(tm)', '(TM)' ), $chr['tradeMark'],      $textnode->data );

		// Un-escape escaped sequences.
		$textnode->data = str_replace( $components['escapeMarker'], '', $textnode->data );
	}

	/**
	 * Apply smart math (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_math( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartMath'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		// First, let's find math equations.
		$textnode->data = preg_replace_callback( $regex['smartMathEquation'], function( array $matches ) use ( $chr ) {
			$matches[0] = str_replace( '-', $chr['minus'],          $matches[0] );
			$matches[0] = str_replace( '/', $chr['division'],       $matches[0] );
			$matches[0] = str_replace( 'x', $chr['multiplication'], $matches[0] );
			$matches[0] = str_replace( '*', $chr['multiplication'], $matches[0] );

			return $matches[0];
		}, $textnode->data );

		// Revert 4-4 to plain minus-hyphen so as to not mess with ranges of numbers (i.e. pp. 46-50).
		$textnode->data = preg_replace( $regex['smartMathRevertRange'], '$1-$2', $textnode->data );

		// Revert fractions to basic slash.
		// We'll leave styling fractions to smart_fractions.
		$textnode->data = preg_replace( $regex['smartMathRevertFraction'], '$1/$2', $textnode->data );

		// Revert date back to original formats.
		// YYYY-MM-DD.
		$textnode->data = preg_replace( $regex['smartMathRevertDateYYYY-MM-DD'], '$1-$2-$3',     $textnode->data );
		// MM-DD-YYYY or DD-MM-YYYY.
		$textnode->data = preg_replace( $regex['smartMathRevertDateMM-DD-YYYY'], '$1$3-$2$4-$5', $textnode->data );
		// YYYY-MM or YYYY-DDD next.
		$textnode->data = preg_replace( $regex['smartMathRevertDateYYYY-MM'],    '$1-$2',        $textnode->data );
		// MM/DD/YYYY or DD/MM/YYYY.
		$textnode->data = preg_replace( $regex['smartMathRevertDateMM/DD/YYYY'], '$1$3/$2$4/$5', $textnode->data );
	}

	/**
	 * Apply smart exponents (if enabled).
	 * Purposefully seperated from smart_math because of HTML code injection.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_exponents( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartExponents'] ) ) {
			return;
		}

		// Handle exponents (ie. 4^2).
		$textnode->data = preg_replace( $settings->regex( 'smartExponents' ), '$1<sup>$2</sup>', $textnode->data );
	}

	/**
	 * Apply smart fractions (if enabled).
	 *
	 * Call before style_numbers, but after smart_ordinal_suffix.
	 * Purposefully seperated from smart_math because of HTML code injection.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_fractions( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartFractions'] ) && empty( $settings['fractionSpacing'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();
		$components = $settings->get_components();

		if ( ! empty( $settings['fractionSpacing'] ) && ! empty( $settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $regex['smartFractionsSpacing'], '$1' . $chr['noBreakNarrowSpace'] . '$2', $textnode->data );
		} elseif ( ! empty( $settings['fractionSpacing'] ) && empty( $settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $regex['smartFractionsSpacing'], '$1' . $chr['noBreakSpace'] . '$2', $textnode->data );
		}

		if ( ! empty( $settings['smartFractions'] ) ) {
			// Escape sequences we don't want fractionified.
			$textnode->data = preg_replace( $regex['smartFractionsEscapeYYYY/YYYY'], '$1' . $components['escapeMarker'] . '$2$3$4', $textnode->data );
			$textnode->data = preg_replace( $regex['smartFractionsEscapeMM/YYYY'],   '$1' . $components['escapeMarker'] . '$2$3$4', $textnode->data );

			// Replace fractions.
			$numerator_class   = empty( $this->css_classes['numerator'] )   ? '' : ' class="' . $this->css_classes['numerator'] . '"';
			$denominator_class = empty( $this->css_classes['denominator'] ) ? '' : ' class="' . $this->css_classes['denominator'] . '"';
			$textnode->data    = preg_replace( $regex['smartFractionsReplacement'], "<sup{$numerator_class}>\$1</sup>" . $chr['fractionSlash'] . "<sub{$denominator_class}>\$2</sub>\$3", $textnode->data );

			// Unescape escaped sequences.
			$textnode->data = str_replace( $components['escapeMarker'], '', $textnode->data );
		}
	}

	/**
	 * Apply smart ordinal suffix (if enabled).
	 *
	 * Call before style_numbers.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function smart_ordinal_suffix( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['smartOrdinalSuffix'] ) ) {
			return;
		}

		$ordinal_class = empty( $this->css_classes['ordinal'] ) ? '' : ' class="' . $this->css_classes['ordinal'] . '"';
		$textnode->data = preg_replace( $settings->regex( 'smartOrdinalSuffix' ), '$1' . "<sup{$ordinal_class}>$2</sup>", $textnode->data );
	}

	/**
	 * Prevent single character words from being alone (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function single_character_word_spacing( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['singleCharacterWordSpacing'] ) ) {
			return;
		}

		// Add $next_character and $previous_character for context.
		$previous_character = $this->get_prev_chr( $textnode );
		if ( '' !== $previous_character ) {
			$textnode->data = $previous_character . $textnode->data;
		}

		$next_character = $this->get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data = $textnode->data . $next_character;
		}

		$textnode->data = preg_replace( $settings->regex( 'singleCharacterWordSpacing' ), '$1$2' . $settings->chr( 'noBreakSpace' ), $textnode->data );

		// If we have adjacent characters remove them from the text.
		$func = $this->str_functions[ mb_detect_encoding( $textnode->data, $this->encodings, true ) ];

		if ( '' !== $previous_character ) {
			$textnode->data = $func['substr']( $textnode->data, 1, $func['strlen']( $textnode->data ) );
		}
		if ( '' !== $next_character ) {
			$textnode->data = $func['substr']( $textnode->data, 0, $func['strlen']( $textnode->data ) - 1 );
		}
	}

	/**
	 * Apply spacing around dashes (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function dash_spacing( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['dashSpacing'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();

		$textnode->data = preg_replace( $regex['dashSpacingEmDash'],            $chr['intervalDashSpace'] . '$1$2' . $chr['intervalDashSpace'],           $textnode->data );
		$textnode->data = preg_replace( $regex['dashSpacingParentheticalDash'], $chr['parentheticalDashSpace'] . '$1$2' . $chr['parentheticalDashSpace'], $textnode->data );
		$textnode->data = preg_replace( $regex['dashSpacingIntervalDash'],      $chr['intervalDashSpace'] . '$1$2' . $chr['intervalDashSpace'],           $textnode->data );
	}

	/**
	 * Collapse spaces (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function space_collapse( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['spaceCollapse'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr        = $settings->get_named_characters();
		$regex      = $settings->get_regular_expressions();

		// Normal spacing.
		$textnode->data = preg_replace( $regex['spaceCollapseNormal'], ' ', $textnode->data );

		// Non-breakable space get's priority. If non-breakable space exists in a string of spaces, it collapses to a single non-breakable space.
		$textnode->data = preg_replace( $regex['spaceCollapseNonBreakable'], $chr['noBreakSpace'], $textnode->data );

		// For any other spaceing, replace with the first occurance of an unusual space character.
		$textnode->data = preg_replace( $regex['spaceCollapseOther'], '$1', $textnode->data );

		// Remove all spacing at beginning of block level elements.
		if ( '' === $this->get_prev_chr( $textnode ) ) { // we have the first text in a block level element.
			$textnode->data = preg_replace( $regex['spaceCollapseBlockStart'], '', $textnode->data );
		}
	}

	/**
	 * Prevent values being split from their units (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function unit_spacing( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['unitSpacing'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $settings->regex( 'unitSpacingUnitPattern' ), '$1' . $settings->chr( 'noBreakNarrowSpace' ) . '$2', $textnode->data );
	}

	/**
	 * Add a narrow no-break space before
	 * - exclamation mark (!)
	 * - question mark (?)
	 * - semicolon (;)
	 * - colon (:)
	 *
	 * If there already is a space there, it is replaced.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function french_punctuation_spacing( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['frenchPunctuationSpacing'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingNarrow'],       '$1' . $chr['noBreakNarrowSpace'] . '$3$4', $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingFull'],         '$1' . $chr['noBreakSpace'] . '$3$4',       $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingSemicolon'],    '$1' . $chr['noBreakNarrowSpace'] . '$3$4', $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingOpeningQuote'], '$1$2' . $chr['noBreakNarrowSpace'] . '$4', $textnode->data );
	}

	/**
	 * Wrap hard hypens with zero-width spaces (if enabled).
	 *
	 * @param array    $parsed_text_tokens The tokenized content of a textnode.
	 * @param Settings $settings           The settings to apply.
	 */
	function wrap_hard_hyphens( array $parsed_text_tokens, Settings $settings ) {
		if ( ! empty( $settings['hyphenHardWrap'] ) || ! empty( $settings['smartDashes'] ) ) {

			// Various special characters and regular expressions.
			$chr        = $settings->get_named_characters();
			$regex      = $settings->get_regular_expressions();
			$components = $settings->get_components();

			foreach ( $parsed_text_tokens as &$text_token ) {
				if ( isset( $settings['hyphenHardWrap'] ) && $settings['hyphenHardWrap'] ) {
					$text_token['value'] = str_replace( $components['hyphensArray'], '-' . $chr['zeroWidthSpace'], $text_token['value'] );
					$text_token['value'] = str_replace( '_', '_' . $chr['zeroWidthSpace'], $text_token['value'] );
					$text_token['value'] = str_replace( '/', '/' . $chr['zeroWidthSpace'], $text_token['value'] );

					$text_token['value'] = preg_replace( $regex['wrapHardHyphensRemoveEndingSpace'], '$1', $text_token['value'] );
				}

				if ( ! empty( $settings['smartDashes'] ) ) {
					// Handled here because we need to know we are inside a word and not a URL.
					$text_token['value'] = str_replace( '-', $chr['hyphen'], $text_token['value'] );
				}
			}
		}

		return $parsed_text_tokens;
	}

	/**
	 * Prevent widows (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function dewidow( \DOMText $textnode, Settings $settings ) {
		// Intervening inline tags may interfere with widow identification, but that is a sacrifice of using the parser.
		// Intervening tags will only interfere if they separate the widow from previous or preceding whitespace.
		if ( empty( $settings['dewidow'] ) || empty( $settings['dewidowMaxPull'] ) || empty( $settings['dewidowMaxLength'] ) ) {
			return;
		}

		if ( '' === $this->get_next_chr( $textnode ) ) {
			// We have the last type "text" child of a block level element.
			$that = $this;
			$chr  = $settings->get_named_characters();
			$textnode->data = preg_replace_callback( $settings->regex( 'dewidow' ), function( array $widow ) use ( $settings, $that, $chr ) {
				$func = $that->str_functions[ mb_detect_encoding( $widow[0], $that->encodings, true ) ];

				// If we are here, we know that widows are being protected in some fashion
				// with that, we will assert that widows should never be hyphenated or wrapped
				// as such, we will strip soft hyphens and zero-width-spaces.
				$widow['widow']    = str_replace( $chr['zeroWidthSpace'], '', $widow['widow'] ); // TODO: check if this can match here.
				$widow['widow']    = str_replace( $chr['softHyphen'],     '', $widow['widow'] ); // TODO: check if this can match here.
				$widow['trailing'] = preg_replace( "/\s+/{$func['u']}", $chr['noBreakSpace'], $widow['trailing'] );
				$widow['trailing'] = str_replace( $chr['zeroWidthSpace'], '', $widow['trailing'] );
				$widow['trailing'] = str_replace( $chr['softHyphen'],     '', $widow['trailing'] );

				// Eject if widows neighbor is proceeded by a no break space (the pulled text would be too long).
				if ( '' === $widow['space_before'] || strstr( $chr['noBreakSpace'], $widow['space_before'] ) ) {
					return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Eject if widows neighbor length exceeds the max allowed or widow length exceeds max allowed.
				if ( $func['strlen']( $widow['neighbor'] ) > $settings['dewidowMaxPull'] ||
					 $func['strlen']( $widow['widow'] ) > $settings['dewidowMaxLength'] ) {
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Never replace thin and hair spaces with &nbsp;.
				switch ( $widow['space_between'] ) {
					case $chr['thinSpace']:
					case $chr['hairSpace']:
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Let's protect some widows!
				return $widow['space_before'] . $widow['neighbor'] . $chr['noBreakSpace'] . $widow['widow'] . $widow['trailing'];
			}, $textnode->data );
		}
	}

	/**
	 * Wrap URL parts zero-width spaces (if enabled).
	 *
	 * @param array    $parsed_text_tokens The tokenized content of a textnode.
	 * @param Settings $settings           The settings to apply.
	 */
	function wrap_urls( array $parsed_text_tokens, Settings $settings ) {
		if ( empty( $settings['urlWrap'] ) || empty( $settings['urlMinAfterWrap'] ) ) {
			return $parsed_text_tokens;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		// Test for and parse urls.
		foreach ( $parsed_text_tokens as &$text_token ) {
			if ( preg_match( $regex['wrapUrlsPattern'], $text_token['value'], $url_match ) ) {

				// $url_match['schema'] holds "http://".
				// $url_match['domain'] holds "subdomains.domain.tld".
				// $url_match['path']   holds the path after the domain.
				$http = ( $url_match['schema'] ) ? $url_match[1] . $chr['zeroWidthSpace'] : '';

				$domain_parts = preg_split( $regex['wrapUrlsDomainParts'], $url_match['domain'], -1, PREG_SPLIT_DELIM_CAPTURE );

				// This is a hack, but it works.
				// First, we hyphenate each part, we need it formated like a group of words.
				$parsed_words_like = array();
				foreach ( $domain_parts as $key => $part ) {
					$parsed_words_like[ $key ]['value'] = $part;
				}

				// Do the hyphenation.
				$parsed_words_like = $this->do_hyphenate( $parsed_words_like, $settings, $chr['zeroWidthSpace'] );

				// Restore format.
				foreach ( $parsed_words_like as $key => $parsed_word ) {
					if ( $key > 0 && 1 === strlen( $parsed_word['value'] ) ) {
						$domain_parts[ $key ] = $chr['zeroWidthSpace'] . $parsed_word['value'];
					} else {
						$domain_parts[ $key ] = $parsed_word['value'];
					}
				}

				// Lastly let's recombine.
				$domain = implode( $domain_parts );

				// Break up the URL path to individual characters.
				$path_parts = str_split( $url_match['path'], 1 );
				$path_count = count( $path_parts );
				$path = '';
				foreach ( $path_parts as $index => $path_part ) {
					if ( 0 === $index || $path_count - $index < $settings['urlMinAfterWrap'] ) {
						$path .= $path_part;
					} else {
						$path .= $chr['zeroWidthSpace'] . $path_part;
					}
				}

				$text_token['value'] = $http . $domain . $path;
			}
		}

		return $parsed_text_tokens;
	}

	/**
	 * Wrap email parts zero-width spaces (if enabled).
	 *
	 * @param array    $parsed_text_tokens The tokenized content of a textnode.
	 * @param Settings $settings           The settings to apply.
	 */
	function wrap_emails( array $parsed_text_tokens, Settings $settings ) {
		if ( empty( $settings['emailWrap'] ) ) {
			return $parsed_text_tokens;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		// Test for and parse urls.
		foreach ( $parsed_text_tokens as &$text_token ) {
			if ( preg_match( $regex['wrapEmailsMatchEmails'], $text_token['value'], $email_match ) ) {
				$text_token['value'] = preg_replace( $regex['wrapEmailsReplaceEmails'], '$1' . $chr['zeroWidthSpace'], $text_token['value'] );
			}
		}

		return $parsed_text_tokens;
	}

	/**
	 * Wraps words of all caps (may include numbers) in <span class="caps"> if enabled.
	 *
	 * Call before style_numbers().Only call if you are certain that no html tags have been
	 * injected containing capital letters.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function style_caps( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['styleCaps'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $settings->regex( 'styleCaps' ), '<span class="' . $this->css_classes['caps'] . '">$1</span>', $textnode->data );
	}

	/**
	 * Replace the given node with HTML content. Uses the HTML5 parser.
	 *
	 * @param \DOMNode $node    The node to replace.
	 * @param string   $content The HTML fragment used to replace the node.
	 *
	 * @return \DOMNode|array An array of \DOMNode containing the new nodes or the old \DOMNode if the replacement failed.
	 */
	function replace_node_with_html( \DOMNode $node, $content ) {
		$result = $node;

		$parent = $node->parentNode; // @codingStandardsIgnoreLine.
		if ( empty( $parent ) ) {
			return $node; // abort early to save cycles.
		}

		set_error_handler( array( $this, 'handle_parsing_errors' ) );

		$html_fragment = $this->get_html5_parser()->loadHTMLFragment( $content );
		if ( ! empty( $html_fragment ) ) {
			$imported_fragment = $node->ownerDocument->importNode( $html_fragment, true ); // @codingStandardsIgnoreLine.

			if ( ! empty( $imported_fragment ) ) {
				// Save the children of the imported DOMDocumentFragment before replacement.
				$children = nodelist_to_array( $imported_fragment->childNodes ); // @codingStandardsIgnoreLine.

				if ( false !== $parent->replaceChild( $imported_fragment, $node ) ) {
				 	// Success! We return the saved array of DOMNodes as
				 	// $imported_fragment is just an empty DOMDocumentFragment now.
				 	$result = $children;
				}
			}
		}

		restore_error_handler();

		return $result;
	}

	/**
	 * Wraps numbers in <span class="numbers"> (even numbers that appear inside a word,
	 * i.e. A9 becomes A<span class="numbers">9</span>), if enabled.
	 *
	 * Call after style_caps so A9 becomes <span class="caps">A<span class="numbers">9</span></span>.
	 * Call after smart_fractions and smart_ordinal_suffix.
	 * Only call if you are certain that no html tags have been injected containing numbers.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function style_numbers( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['styleNumbers'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $settings->regex( 'styleNumbers' ), '<span class="' . $this->css_classes['numbers'] . '">$1</span>', $textnode->data );
	}

	/**
	 * Wraps hanging punctuation in <span class="pull-*"> and <span class="push-*">, if enabled.
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function style_hanging_punctuation( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['styleHangingPunctuation'] ) ) {
			return;
		}

		// We need the parent.
		$block = $this->get_block_parent( $textnode );
		$firstnode = ! empty( $block ) ? $this->get_first_textnode( $block ) : null;

		// Need to get context of adjacent characters outside adjacent inline tags or HTML comment
		// if we have adjacent characters add them to the text.
		$next_character = $this->get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data = $textnode->data . $next_character;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		$textnode->data = preg_replace( $regex['styleHangingPunctuationDouble'], '$1<span class="' . $this->css_classes['push-double'] . '"></span>' . $chr['zeroWidthSpace'] . '<span class="' . $this->css_classes['pull-double'] . '">$2</span>$3', $textnode->data );
		$textnode->data = preg_replace( $regex['styleHangingPunctuationSingle'], '$1<span class="' . $this->css_classes['push-single'] . '"></span>' . $chr['zeroWidthSpace'] . '<span class="' . $this->css_classes['pull-single'] . '">$2</span>$3', $textnode->data );

		if ( empty( $block ) || $firstnode === $textnode ) {
			$textnode->data = preg_replace( $regex['styleHangingPunctuationInitialDouble'], '<span class="' . $this->css_classes['pull-double'] . '">$1</span>$2', $textnode->data );
			$textnode->data = preg_replace( $regex['styleHangingPunctuationInitialSingle'], '<span class="' . $this->css_classes['pull-single'] . '">$1</span>$2', $textnode->data );
		} else {
			$textnode->data = preg_replace( $regex['styleHangingPunctuationInitialDouble'], '<span class="' . $this->css_classes['push-double'] . '"></span>' . $chr['zeroWidthSpace'] . '<span class="' . $this->css_classes['pull-double'] . '">$1</span>$2', $textnode->data );
			$textnode->data = preg_replace( $regex['styleHangingPunctuationInitialSingle'], '<span class="' . $this->css_classes['push-single'] . '"></span>' . $chr['zeroWidthSpace'] . '<span class="' . $this->css_classes['pull-single'] . '">$1</span>$2', $textnode->data );
		}

		// Remove any added characters.
		if ( '' !== $next_character ) {
			$func = $this->str_functions[ mb_detect_encoding( $textnode->data, $this->encodings, true ) ];
			$textnode->data = $func['substr']( $textnode->data, 0, $func['strlen']( $textnode->data ) - 1 );
		}
	}

	/**
	 * Wraps ampersands in <span class="amp"> (i.e. H&amp;J becomes H<span class="amp">&amp;</span>J),
	 * if enabled.
	 *
	 * Call after style_caps so H&amp;J becomes <span class="caps">H<span class="amp">&amp;</span>J</span>.
	 * Note that all standalone ampersands were previously converted to &amp;.
	 * Only call if you are certain that no html tags have been injected containing "&amp;".
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 */
	function style_ampersands( \DOMText $textnode, Settings $settings ) {
		if ( empty( $settings['styleAmpersands'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $settings->regex( 'styleAmpersands' ), '<span class="' . $this->css_classes['amp'] . '">$1</span>', $textnode->data );
	}

	/**
	 * Styles initial quotes and guillemets (if enabled).
	 *
	 * @param \DOMText $textnode The content node.
	 * @param Settings $settings The settings to apply.
	 * @param boolean  $is_title Default false.
	 */
	function style_initial_quotes( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['styleInitialQuotes'] ) || empty( $settings['initialQuoteTags'] ) ) {
			return;
		}

		if ( '' === $this->get_prev_chr( $textnode ) ) { // we have the first text in a block level element.

			$func            = $this->str_functions[ mb_detect_encoding( $textnode->data, $this->encodings, true ) ];
			$first_character = $func['substr']( $textnode->data, 0, 1 );
			$chr             = $settings->get_named_characters();

			switch ( $first_character ) {
				case "'":
				case $chr['singleQuoteOpen']:
				case $chr['singleLow9Quote']:
				case ',':
				case '"':
				case $chr['doubleQuoteOpen']:
				case $chr['guillemetOpen']:
				case $chr['guillemetClose']:
				case $chr['doubleLow9Quote']:

					$block_level_parent = $this->get_block_parent( $textnode );
					$block_level_parent = isset( $block_level_parent->tagName ) ? $block_level_parent->tagName : false; // @codingStandardsIgnoreLine.

					if ( $is_title ) {
						// Assume page title is h2.
						$block_level_parent = 'h2';
					}

					if ( $block_level_parent && isset( $settings['initialQuoteTags'][ $block_level_parent ] ) ) {
						switch ( $first_character ) {
							case "'":
							case $chr['singleQuoteOpen']:
							case $chr['singleLow9Quote']:
							case ',':
								$span_class = 'quo';
								break;

							default: // double quotes or guillemets.
								$span_class = 'dquo';
						}

						$textnode->data = '<span class="' . $this->css_classes[ $span_class ] . '">' . $first_character . '</span>' . $func['substr']( $textnode->data, 1, $func['strlen']( $textnode->data ) );
					}
			}
		}
	}

	/**
	 * Hyphenate given text fragment (if enabled).
	 *
	 * Actual work is done in do_hyphenate().
	 *
	 * @param array    $parsed_text_tokens Filtered to words.
	 * @param Settings $settings           The settings to apply.
	 * @param boolean  $is_title           Flag to indicate title fragments. Optional. Default false.
	 * @param \DOMText $textnode           The textnode corresponding to the $parsed_text_tokens. Optional. Default null.
	 */
	function hyphenate( $parsed_text_tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings['hyphenation'] ) ) {
			return $parsed_text_tokens; // abort.
		}

		$is_heading = false;
		if ( ! empty( $textnode ) && ! empty( $textnode->parentNode ) ) { // @codingStandardsIgnoreLine.
			$block_level_parent = $this->get_block_parent( $textnode );
			$block_level_parent = isset( $block_level_parent->tagName ) ? $block_level_parent->tagName : false; // @codingStandardsIgnoreLine.

			if ( $block_level_parent && isset( $this->heading_tags[ $block_level_parent ] ) ) {
				$is_heading = true;
			}
		}

		if ( empty( $settings['hyphenateTitle'] ) && ( $is_title || $is_heading ) ) {
			return $parsed_text_tokens; // abort.
		}

		// Call functionality as seperate function so it can be run without test for setting['hyphenation'] - such as with url wrapping.
		return $this->do_hyphenate( $parsed_text_tokens, $settings );
	}

	/**
	 * Hyphenate hyphenated compound words (if enabled).
	 *
	 * Calls hyphenate() on the component words.
	 *
	 * @param array    $parsed_text_tokens Filtered to compound words.
	 * @param Settings $settings           The settings to apply.
	 * @param boolean  $is_title           Flag to indicate title fragments. Optional. Default false.
	 * @param \DOMText $textnode           The textnode corresponding to the $parsed_text_tokens. Optional. Default null.
	 */
	function hyphenate_compounds( array $parsed_text_tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings['hyphenateCompounds'] ) ) {
			return $parsed_text_tokens; // abort.
		}

		// Hyphenate compound words.
		foreach ( $parsed_text_tokens as $key => $word_token ) {
			$component_words = array();
			foreach ( preg_split( '/(-)/', $word_token['value'], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE ) as $word_part ) {
				$component_words[] = array( 'value' => $word_part );
			}

			$parsed_text_tokens[ $key ]['value'] = array_reduce( $this->hyphenate( $component_words, $settings, $is_title, $textnode ), function( $carry, $item ) {
				return $carry . $item['value'];
			});
		}

		return $parsed_text_tokens;
	}

	/**
	 * Retrieve the hyphenator instance.
	 *
	 * @param Settings $settings The settings to apply.
	 *
	 * @return \PHP_Typography\
	 */
	public function get_hyphenator( Settings $settings ) {
		if ( ! isset( $this->hyphenator ) ) {

			// Create and initialize our hyphenator instance.
			$this->hyphenator = new Hyphenator(
				isset( $settings['hyphenLanguage'] )              ? $settings['hyphenLanguage'] : null,
				isset( $settings['hyphenationCustomExceptions'] ) ? $settings['hyphenationCustomExceptions'] : array()
			);
		} else {
			$this->hyphenator->set_language( $settings['hyphenLanguage'] );
			$this->hyphenator->set_custom_exceptions( isset( $settings['hyphenationCustomExceptions'] ) ? $settings['hyphenationCustomExceptions'] : array() );
		}

		return $this->hyphenator;
	}

	/**
	 * Really hyphenate given text fragment.
	 *
	 * @param array    $parsed_text_tokens Filtered to words.
	 * @param Settings $settings          The settings to apply.
	 * @param string   $hyphen             Hyphenation character. Optional. Default is the soft hyphen character (`&shy;`).
	 *
	 * @return array The hyphenated text token.
	 */
	function do_hyphenate( array $parsed_text_tokens, Settings $settings, $hyphen = null ) {
		if ( empty( $settings['hyphenMinLength'] ) || empty( $settings['hyphenMinBefore'] ) ) {
			return $parsed_text_tokens;
		}

		// Default to &shy; is $hyphen is not set.
		if ( ! isset( $hyphen ) ) {
			$hyphen = $settings->chr( 'softHyphen' );
		}

		return $this->get_hyphenator( $settings )->hyphenate( $parsed_text_tokens, $hyphen, ! empty( $settings['hyphenateTitleCase'] ), $settings['hyphenMinLength'], $settings['hyphenMinBefore'], $settings['hyphenMinAfter'] );
	}

	/**
	 * Returns the nearest block-level parent.
	 *
	 * @param \DOMNode $element The node to get the containing block-level tag.
	 *
	 * @return \DOMElement
	 */
	function get_block_parent( \DOMNode $element ) {
		$parent = $element->parentNode; // @codingStandardsIgnoreLine.

		while ( isset( $parent->tagName ) && ! isset( $this->block_tags[ $parent->tagName ] ) && ! empty( $parent->parentNode ) && $parent->parentNode instanceof \DOMElement ) { // @codingStandardsIgnoreLine.
			$parent = $parent->parentNode; // @codingStandardsIgnoreLine.
		}

		return $parent;
	}

	/**
	 * Retrieve a unique hash value for the current settings.
	 *
	 * @param number $max_length The maximum number of bytes returned. Optional. Default 16.
	 * @return string An binary hash value for the current settings limited to $max_length.
	 */
	public function get_settings_hash( $max_length = 16 ) {
		return $this->settings->get_hash( $max_length );
	}

	/**
	 * Retrieve the HTML5 parser instance.
	 *
	 * @return \Mastermind\HTML5
	 */
	public function get_html5_parser() {
		// Lazy-load HTML5 parser.
		if ( ! isset( $this->html5_parser ) ) {
			$this->html5_parser = new \Masterminds\HTML5( array( 'disable_html_ns' => true ) );
		}

		return $this->html5_parser;
	}

	/**
	 * Retrieve the text parser instance.
	 *
	 * @return \PHP_Typography\Parse_Text
	 */
	public function get_text_parser() {
		// Lazy-load text parser.
		if ( ! isset( $this->text_parser ) ) {
			$this->text_parser = new Parse_Text( $this->encodings );
		}

		return $this->text_parser;
	}

	/**
	 * Retrieve the list of valid hyphenation languages.
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	static public function get_hyphenation_languages() {
		return \PHP_Typography\get_language_plugin_list( __DIR__ . '/lang/', 'patgenLanguage' );
	}

	/**
	 * Retrieve the list of valid diacritic replacement languages.
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	static public function get_diacritic_languages() {
		return \PHP_Typography\get_language_plugin_list( __DIR__ . '/diacritics/', 'diacriticLanguage' );
	}
}
