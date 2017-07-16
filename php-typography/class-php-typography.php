<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or
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

use \PHP_Typography\Fixes\Node_Fix;
use \PHP_Typography\Fixes\Node_Fixes;
use \PHP_Typography\Fixes\Token_Fix;
use \PHP_Typography\Fixes\Token_Fixes;

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

	const CHARACTERS         = 10;
	const SPACING_PRE_WORDS  = 20;
	const PROCESS_WORDS      = 30;
	const SPACING_POST_WORDS = 40;
	const HTML_INSERTION     = 50;

	const GROUPS = [ self::CHARACTERS, self::SPACING_PRE_WORDS, self::PROCESS_WORDS, self::SPACING_POST_WORDS, self::HTML_INSERTION ];

	/**
	 * A hashmap of settings for the various typographic options.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * A DOM-based HTML5 parser.
	 *
	 * @var \Masterminds\HTML5
	 */
	private $html5_parser;

	/**
	 * An array of Node_Fix implementations.
	 *
	 * @var array
	 */
	private $node_fixes = [
		self::CHARACTERS         => [],
		self::SPACING_PRE_WORDS  => [],
		self::PROCESS_WORDS      => [],
		self::SPACING_POST_WORDS => [],
		self::HTML_INSERTION     => [],
	];

	/**
	 * The token fix registry.
	 *
	 * @var Node_Fixes\Process_Words_Fix
	 */
	private $process_words_fix;

	/**
	 * An array of Token_Fix implementations.
	 *
	 * @var array
	 */
	private $token_fixes = [];

	/**
	 * An array of CSS classes that are added for ampersands, numbers etc that can be overridden in a subclass.
	 *
	 * @var array
	 */
	protected $css_classes = [
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
	];

	/**
	 * Sets up a new PHP_Typography object.
	 *
	 * @param boolean $set_defaults Optional. If true, set default values for various properties. Default true.
	 * @param string  $init         Optional. Flag to control initialization. Valid inputs are 'now' and 'lazy'. Default 'now'.
	 */
	public function __construct( $set_defaults = true, $init = 'now' ) {
		if ( 'now' === $init ) {
			$this->init( $set_defaults );
		}
	}

	/**
	 * Initializes the PHP_Typography object.
	 *
	 * @param boolean $set_defaults Optional. If true, set default values for various properties. Default true.
	 */
	public function init( $set_defaults = true ) {
		$this->settings          = new Settings( $set_defaults );
		$this->process_words_fix = new Node_Fixes\Process_Words_Fix();

		// Nodify anything that requires adjacent text awareness here.
		$this->register_node_fix( new Node_Fixes\Smart_Maths_Fix(),          self::CHARACTERS );
		$this->register_node_fix( new Node_Fixes\Smart_Diacritics_Fix(),     self::CHARACTERS );
		$this->register_node_fix( new Node_Fixes\Smart_Quotes_Fix( true ),   self::CHARACTERS );
		$this->register_node_fix( new Node_Fixes\Smart_Dashes_Fix( true ),   self::CHARACTERS );
		$this->register_node_fix( new Node_Fixes\Smart_Ellipses_Fix( true ), self::CHARACTERS );
		$this->register_node_fix( new Node_Fixes\Smart_Marks_Fix( true ),    self::CHARACTERS );

		// Keep spacing after smart character replacement.
		$this->register_node_fix( new Node_Fixes\Single_Character_Word_Spacing_Fix(), self::SPACING_PRE_WORDS );
		$this->register_node_fix( new Node_Fixes\Dash_Spacing_Fix(),                  self::SPACING_PRE_WORDS );
		$this->register_node_fix( new Node_Fixes\Unit_Spacing_Fix(),                  self::SPACING_PRE_WORDS );
		$this->register_node_fix( new Node_Fixes\Numbered_Abbreviation_Spacing_Fix(), self::SPACING_PRE_WORDS );
		$this->register_node_fix( new Node_Fixes\French_Punctuation_Spacing_Fix(),    self::SPACING_PRE_WORDS );

		// Parse and process individual words.
		$this->register_node_fix( $this->process_words_fix, self::PROCESS_WORDS );

		// Some final space manipulation.
		$this->register_node_fix( new Node_Fixes\Dewidow_Fix(),        self::SPACING_POST_WORDS );
		$this->register_node_fix( new Node_Fixes\Space_Collapse_Fix(), self::SPACING_POST_WORDS );

		// Everything that requires HTML injection occurs here (functions above assume tag-free content)
		// pay careful attention to functions below for tolerance of injected tags.
		$this->register_node_fix( new Node_Fixes\Smart_Ordinal_Suffix_Fix( $this->css_classes['ordinal'] ),                                 self::HTML_INSERTION ); // call before "style_numbers" and "smart_fractions".
		$this->register_node_fix( new Node_Fixes\Smart_Exponents_Fix(),                                                                     self::HTML_INSERTION ); // call before "style_numbers".
		$this->register_node_fix( new Node_Fixes\Smart_Fractions_Fix( $this->css_classes['numerator'], $this->css_classes['denominator'] ), self::HTML_INSERTION ); // call before "style_numbers" and after "smart_ordinal_suffix".
		$this->register_node_fix( new Node_Fixes\Style_Caps_Fix( $this->css_classes['caps'] ),                                              self::HTML_INSERTION ); // Call before "style_numbers".
		$this->register_node_fix( new Node_Fixes\Style_Numbers_Fix( $this->css_classes['numbers'] ),                                        self::HTML_INSERTION ); // Call after "smart_ordinal_suffix", "smart_exponents", "smart_fractions", and "style_caps".
		$this->register_node_fix( new Node_Fixes\Style_Ampersands_Fix( $this->css_classes['amp'] ),                                         self::HTML_INSERTION );
		$this->register_node_fix( new Node_Fixes\Style_Initial_Quotes_Fix( $this->css_classes['quo'], $this->css_classes['dquo'] ),         self::HTML_INSERTION );
		$this->register_node_fix( new Node_Fixes\Style_Hanging_Punctuation_Fix( $this->css_classes['push-single'], $this->css_classes['push-double'], $this->css_classes['pull-single'], $this->css_classes['pull-double'] ), self::HTML_INSERTION );

		// Register token fixes.
		$this->register_token_fix( new Token_Fixes\Wrap_Hard_Hyphens_Fix() );
		$this->register_token_fix( new Token_Fixes\Hyphenate_Compounds_Fix() );
		$this->register_token_fix( new Token_Fixes\Hyphenate_Fix() );
		$this->register_token_fix( new Token_Fixes\Wrap_URLs_Fix() );
		$this->register_token_fix( new Token_Fixes\Wrap_Emails_Fix() );
	}

	/**
	 * (Re)sets various options to their default values.
	 */
	public function set_defaults() {
		$this->settings->set_defaults();
	}

	/**
	 * Retrieves a copy (suitable for modification) of the internal settings.
	 *
	 * @return Settings|null A clone of the internal settings. Returns null if the settings object has not been initialized yet.
	 */
	public function get_settings() {
		if ( ! empty( $this->settings ) ) {
			return clone $this->settings;
		} else {
			return null;
		}
	}

	/**
	 * Enables lenient parser error handling (like it was before release 3.5.2).
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_ignore_parser_errors( $on = false ) {
		$this->settings->set_ignore_parser_errors( $on );
	}

	/**
	 * Sets an optional handler for parser errors. Invalid callbacks will be silently ignored.
	 *
	 * @param callable|null $handler Optional. A callable that takes an array of error strings as its parameter. Default null.
	 */
	public function set_parser_errors_handler( $handler = null ) {
		$this->settings->set_parser_errors_handler( $handler );
	}

	/**
	 * Enables usage of true "no-break narrow space" (&#8239;) instead of the normal no-break space (&nbsp;).
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_true_no_break_narrow_space( $on = false ) {
		$this->settings->set_true_no_break_narrow_space( $on );
	}

	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags Optional. A comma separated list or an array of tag names.
	 *                           Default [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ].
	 */
	public function set_tags_to_ignore( $tags = [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ] ) {
		$this->settings->set_tags_to_ignore( $tags );
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|array $classes Optional. A comma separated list or an array of class names. Default [ 'vcard', 'noTypo' ].
	 */
	 function set_classes_to_ignore( $classes = [ 'vcard', 'noTypo' ] ) {
		$this->settings->set_classes_to_ignore( $classes );
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|array $ids Optional. A comma separated list or an array of tag names. Default empty array.
	 */
	public function set_ids_to_ignore( $ids = [] ) {
		$this->settings->set_ids_to_ignore( $ids );
	}

	/**
	 * Enables/disables typographic quotes.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_quotes( $on = true ) {
		$this->settings->set_smart_quotes( $on );
	}

	/**
	 * Sets the style for primary ('double') quotemarks.
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
	 * @param string $style Optional. Default 'doubleCurled.
	 */
	public function set_smart_quotes_primary( $style = 'doubleCurled' ) {
		$this->settings->set_smart_quotes_primary( $style );
	}

	/**
	 * Sets the style for secondary ('single') quotemarks.
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
	 * @param string $style Optional. Default 'singleCurled'.
	 */
	public function set_smart_quotes_secondary( $style = 'singleCurled' ) {
		$this->settings->set_smart_quotes_secondary( $style );
	}

	/**
	 * Enables/disables replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_dashes( $on = true ) {
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
	public function set_smart_dashes_style( $style = 'traditionalUS' ) {
		$this->settings->set_smart_dashes_style( $style );
	}

	/**
	 * Enables/disables replacement of "..." with "…".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ellipses( $on = true ) {
		$this->settings->set_smart_ellipses( $on );
	}

	/**
	 * Enables/disables replacement "creme brulee" with "crème brûlée".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_diacritics( $on = true ) {
		$this->settings->set_smart_diacritics( $on );
	}

	/**
	 * Sets the language used for diacritics replacements.
	 *
	 * @param string $lang Optional. Has to correspond to a filename in 'diacritics/'. Default 'en-US'.
	 */
	public function set_diacritic_language( $lang = 'en-US' ) {
		$this->settings->set_diacritic_language( $lang );
	}

	/**
	 * Sets up custom diacritics replacements.
	 *
	 * @param string|array $replacements Optional An array formatted [needle=>replacement, needle=>replacement...],
	 *                                   or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 *                                   Default empty array.
	 */
	public function set_diacritic_custom_replacements( $replacements = [] ) {
		$this->settings->set_diacritic_custom_replacements( $replacements );
	}

	/**
	 * Enables/disables replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_marks( $on = true ) {
		$this->settings->set_smart_marks( $on );
	}

	/**
	 * Enables/disables proper mathematical symbols.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_math( $on = true ) {
		$this->settings->set_smart_math( $on );
	}

	/**
	 * Enables/disables replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_exponents( $on = true ) {
		$this->settings->set_smart_exponents( $on );
	}

	/**
	 * Enables/disables replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_fractions( $on = true ) {
		$this->settings->set_smart_fractions( $on );
	}

	/**
	 * Enables/disables replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ordinal_suffix( $on = true ) {
		$this->settings->set_smart_ordinal_suffix( $on );
	}

	/**
	 * Enables/disables forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_single_character_word_spacing( $on = true ) {
		$this->settings->set_single_character_word_spacing( $on );
	}

	/**
	 * Enables/disables fraction spacing.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_fraction_spacing( $on = true ) {
		$this->settings->set_fraction_spacing( $on );
	}

	/**
	 * Enables/disables keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_unit_spacing( $on = true ) {
		$this->settings->set_unit_spacing( $on );
	}

	/**
	 * Enables/disables numbered abbreviations like "ISO 9000" together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_numbered_abbreviation_spacing( $on = true ) {
		$this->settings->set_numbered_abbreviation_spacing( $on );
	}

	/**
	 * Enables/disables extra whitespace before certain punction marks, as is the French custom.
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_french_punctuation_spacing( $on = false ) {
		$this->settings->set_french_punctuation_spacing( $on );
	}

	/**
	 * Sets the list of units to keep together with their values.
	 *
	 * @param string|array $units Optional. A comma separated list or an array of units. Default empty array.
	 */
	public function set_units( $units = [] ) {
		$this->settings->set_units( $units );
	}

	/**
	 * Enables/disables wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dash_spacing( $on = true ) {
		$this->settings->set_dash_spacing( $on );
	}

	/**
	 * Enables/disables removal of extra whitespace characters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_space_collapse( $on = true ) {
		$this->settings->set_space_collapse( $on );
	}

	/**
	 * Enables/disables widow handling.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dewidow( $on = true ) {
		$this->settings->set_dewidow( $on );
	}

	/**
	 * Sets the maximum length of widows that will be protected.
	 *
	 * @param int $length Optional. Default 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_max_dewidow_length( $length = 5 ) {
		$this->settings->set_max_dewidow_length( $length );
	}

	/**
	 * Sets the maximum length of pulled text to keep widows company.
	 *
	 * @param int $length Optional. Default 5. Trying to set the value to less than 2 resets the pull to the default.
	 */
	public function set_max_dewidow_pull( $length = 5 ) {
		$this->settings->set_max_dewidow_pull( $length );
	}

	/**
	 * Enables/disables wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_wrap_hard_hyphens( $on = true ) {
		$this->settings->set_wrap_hard_hyphens( $on );
	}

	/**
	 * Enables/disables wrapping of urls.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_url_wrap( $on = true ) {
		$this->settings->set_url_wrap( $on );
	}

	/**
	 * Enables/disables wrapping of email addresses.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_email_wrap( $on = true ) {
		$this->settings->set_email_wrap( $on );
	}

	/**
	 * Sets the minimum character requirement after an URL wrapping point.
	 *
	 * @param int $length Optional. Default 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_url_wrap( $length = 5 ) {
		$this->settings->set_min_after_url_wrap( $length );
	}

	/**
	 * Enables/disables wrapping of ampersands in <span class="amp">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_ampersands( $on = true ) {
		$this->settings->set_style_ampersands( $on );
	}

	/**
	 * Enables/disables wrapping caps in <span class="caps">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_caps( $on = true ) {
		$this->settings->set_style_caps( $on );
	}

	/**
	 * Enables/disables wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_initial_quotes( $on = true ) {
		$this->settings->set_style_initial_quotes( $on );
	}

	/**
	 * Enables/disables wrapping of numbers in <span class="numbers">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_numbers( $on = true ) {
		$this->settings->set_style_numbers( $on );
	}

	/**
	 * Enables/disables wrapping of punctiation and wide characters in <span class="pull-*">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_hanging_punctuation( $on = true ) {
		$this->settings->set_style_hanging_punctuation( $on );
	}

	/**
	 * Sets the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|array $tags Optional. A comma separated list or an array of tag names.
	 *                           Default [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ].
	 */
	public function set_initial_quote_tags( $tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ] ) {
		$this->settings->set_initial_quote_tags( $tags );
	}

	/**
	 * Enables/disables hyphenation.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenation( $on = true ) {
		$this->settings->set_hyphenation( $on );
	}

	/**
	 * Sets the hyphenation pattern language.
	 *
	 * @param string $lang Optional. Has to correspond to a filename in 'lang'. Default 'en-US'.
	 */
	public function set_hyphenation_language( $lang = 'en-US' ) {
		$this->settings->set_hyphenation_language( $lang );
	}

	/**
	 * Sets the minimum length of a word that may be hyphenated.
	 *
	 * @param int $length Optional. Default 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_min_length_hyphenation( $length = 5 ) {
		$this->settings->set_min_length_hyphenation( $length );
	}

	/**
	 * Sets the minimum character requirement before a hyphenation point.
	 *
	 * @param int $length Optional. Default 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_before_hyphenation( $length = 3 ) {
		$this->settings->set_min_before_hyphenation( $length );
	}

	/**
	 * Sets the minimum character requirement after a hyphenation point.
	 *
	 * @param int $length Optional. Default 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_hyphenation( $length = 2 ) {
		$length = ( $length > 0 ) ? $length : 2;

		$this->settings->set_min_after_hyphenation( $length );
	}

	/**
	 * Enables/disables hyphenation of titles and headings.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_headings( $on = true ) {
		$this->settings->set_hyphenate_headings( $on );
	}

	/**
	 * Enables/disables hyphenation of words set completely in capital letters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_all_caps( $on = true ) {
		$this->settings->set_hyphenate_all_caps( $on );
	}

	/**
	 * Enables/disables hyphenation of words starting with a capital letter.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_title_case( $on = true ) {
		$this->settings->set_hyphenate_title_case( $on );
	}

	/**
	 * Enables/disables hyphenation of compound words (e.g. "editor-in-chief").
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_compounds( $on = true ) {
		$this->settings->set_hyphenate_compounds( $on );
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param string|array $exceptions Optional. An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
	 *                                 In the latter case, only alphanumeric characters and hyphens are recognized. Default empty array.
	 */
	public function set_hyphenation_exceptions( $exceptions = [] ) {
		$this->settings->set_hyphenation_exceptions( $exceptions );
	}

	/**
	 * Modifies $html according to the defined settings.
	 *
	 * @param string        $html      A HTML fragment.
	 * @param bool          $is_title  Optional. If the HTML fragment is a title. Default false.
	 * @param Settings|null $settings  Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	public function process( $html, $is_title = false, Settings $settings = null ) {
		return $this->process_textnodes( $html, [ $this, 'apply_fixes_to_html_node' ], $is_title, $settings );
	}

	/**
	 * Modifies $html according to the defined settings, in a way that is appropriate for RSS feeds
	 * (i.e. excluding processes that may not display well with limited character set intelligence).
	 *
	 * @param string        $html     A HTML fragment.
	 * @param bool          $is_title Optional. If the HTML fragment is a title. Default false.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	public function process_feed( $html, $is_title = false, Settings $settings = null ) {
		return $this->process_textnodes( $html, [ $this, 'apply_fixes_to_feed_node' ], $is_title, $settings );
	}

	/**
	 * Applies specific fixes to all textnodes of the HTML fragment.
	 *
	 * @param string        $html     A HTML fragment.
	 * @param callable      $fixer    A callback that applies typography fixes to a single textnode.
	 * @param bool          $is_title Optional. If the HTML fragment is a title. Default false.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $html.
	 */
	public function process_textnodes( $html, $fixer, $is_title = false, Settings $settings = null ) {
		// Don't do aynthing if there is no valid callback.
		if ( ! is_callable( $fixer ) ) {
			trigger_error( 'PHP_Typography::process_textnodes called without a valid callback.', E_USER_WARNING ); // @codingStandardsIgnoreLine
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
		$dom = $this->parse_html( $html5_parser, $html, $settings );

		// Abort if there were parsing errors.
		if ( empty( $dom ) ) {
			return $html;
		}

		// Query some nodes in the DOM.
		$xpath = new \DOMXPath( $dom );
		$body_node = $xpath->query( '/html/body' )->item( 0 );
		$all_textnodes = $xpath->query( '//text()', $body_node );
		$tags_to_ignore = $this->query_tags_to_ignore( $xpath, $body_node, $settings );

		// Start processing.
		foreach ( $all_textnodes as $textnode ) {
			if ( self::arrays_intersect( DOM::get_ancestors( $textnode ), $tags_to_ignore ) ) {
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

		return $html5_parser->saveHTML( $body_node->childNodes );
	}

	/**
	 * Determines whether two object arrays intersect. The second array is expected
	 * to use the spl_object_hash for its keys.
	 *
	 * @param array $array1 The keys are ignored.
	 * @param array $array2 This array has to be in the form ( $spl_object_hash => $object ).
	 *
	 * @return boolean
	 */
	protected static function arrays_intersect( array $array1, array $array2 ) {
		foreach ( $array1 as $value ) {
			if ( isset( $array2[ spl_object_hash( $value ) ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Applies standard typography fixes to a textnode.
	 *
	 * @param \DOMText $textnode The node to process.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_fixes_to_html_node( \DOMText $textnode, Settings $settings, $is_title = false ) {
		foreach ( self::GROUPS as $group ) {
			foreach ( $this->node_fixes[ $group ] as $fix ) {
				$fix->apply( $textnode, $settings, $is_title );
			}
		}
	}

	/**
	 * Applies typography fixes specific to RSS feeds to a textnode.
	 *
	 * @param \DOMText $textnode The node to process.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_fixes_to_feed_node( \DOMText $textnode, Settings $settings, $is_title = false ) {
		foreach ( self::GROUPS as $group ) {
			foreach ( $this->node_fixes[ $group ] as $fix ) {
				if ( $fix->feed_compatible() ) {
					$fix->apply( $textnode, $settings, $is_title );
				}
			}
		}
	}

	/**
	 * Parse HTML5 fragment while ignoring certain warnings for invalid HTML code (e.g. duplicate IDs).
	 *
	 * @param \Masterminds\HTML5 $parser   An intialized parser object.
	 * @param string             $html     The HTML fragment to parse (not a complete document).
	 * @param Settings           $settings The settings to apply.
	 *
	 * @return \DOMDocument The encoding has already been set to UTF-8. Returns null if there were parsing errors.
	 */
	public function parse_html( \Masterminds\HTML5 $parser, $html, Settings $settings ) {
		// Silence some parsing errors for invalid HTML.
		set_error_handler( [ $this, 'handle_parsing_errors' ] ); // @codingStandardsIgnoreLine
		$xml_error_handling = libxml_use_internal_errors( true );

		// Do the actual parsing.
		$dom = $parser->loadHTML( '<!DOCTYPE html><html><body>' . $html . '</body></html>' );
		$dom->encoding = 'UTF-8';

		// Restore original error handling.
		libxml_clear_errors();
		libxml_use_internal_errors( $xml_error_handling );
		restore_error_handler();

		// Handle any parser errors.
		$errors = $parser->getErrors();
		if ( ! empty( $settings['parserErrorsHandler'] ) && ! empty( $errors ) ) {
			$errors = call_user_func( $settings['parserErrorsHandler'], $errors );
		}

		// Return null if there are still unhandled parsing errors.
		if ( ! empty( $errors ) && ! $settings['parserErrorsIgnore'] ) {
			$dom = null;
		}

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

		// Ignore warnings from parser & let PHP handle the rest.
		return $errno & E_USER_WARNING && 0 === substr_compare( $errfile, 'DOMTreeBuilder.php', -18 );
	}

	/**
	 * Retrieves an array of nodes that should be skipped during processing.
	 *
	 * @param \DOMXPath $xpath        A valid XPath instance for the DOM to be queried.
	 * @param \DOMNode  $initial_node The starting node of the XPath query.
	 * @param Settings  $settings     The settings to apply.
	 *
	 * @return array An array of \DOMNode (can be empty).
	 */
	public function query_tags_to_ignore( \DOMXPath $xpath, \DOMNode $initial_node, Settings $settings ) {
		$elements = [];
		$query_parts = [];
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

			$nodelist = $xpath->query( $ignore_query, $initial_node );
			if ( false !== $nodelist ) {
				$elements = DOM::nodelist_to_array( $nodelist );
			}
		}

		return $elements;
	}

	/**
	 * Replaces the given node with HTML content. Uses the HTML5 parser.
	 *
	 * @param \DOMNode $node    The node to replace.
	 * @param string   $content The HTML fragment used to replace the node.
	 *
	 * @return \DOMNode|array An array of \DOMNode containing the new nodes or the old \DOMNode if the replacement failed.
	 */
	public function replace_node_with_html( \DOMNode $node, $content ) {
		$result = $node;

		$parent = $node->parentNode;
		if ( empty( $parent ) ) {
			return $node; // abort early to save cycles.
		}

		set_error_handler( [ $this, 'handle_parsing_errors' ] ); // @codingStandardsIgnoreLine.

		$html_fragment = $this->get_html5_parser()->loadHTMLFragment( $content );
		if ( ! empty( $html_fragment ) ) {
			$imported_fragment = $node->ownerDocument->importNode( $html_fragment, true );

			if ( ! empty( $imported_fragment ) ) {
				// Save the children of the imported DOMDocumentFragment before replacement.
				$children = DOM::nodelist_to_array( $imported_fragment->childNodes );

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
	 * Retrieves a unique hash value for the current settings.
	 *
	 * @param int $max_length Optional. The maximum number of bytes returned. Default 16.
	 *
	 * @return string An binary hash value for the current settings limited to $max_length.
	 */
	public function get_settings_hash( $max_length = 16 ) {
		return $this->settings->get_hash( $max_length );
	}

	/**
	 * Retrieves the HTML5 parser instance.
	 *
	 * @return \Masterminds\HTML5
	 */
	public function get_html5_parser() {
		// Lazy-load HTML5 parser.
		if ( ! isset( $this->html5_parser ) ) {
			$this->html5_parser = new \Masterminds\HTML5( [
				'disable_html_ns' => true,
			] );
		}

		return $this->html5_parser;
	}

	/**
	 * Registers a node fix.
	 *
	 * @param Node_Fix $fix   Required.
	 * @param int      $group Required. Only the constants CHARACTERS, SPACING_PRE_WORDS, SPACING_POST_WORDS, HTML_INSERTION are valid.
	 *
	 * @throws \InvalidArgumentException Group is invalid.
	 */
	public function register_node_fix( Node_Fix $fix, $group ) {
		switch ( $group ) {
			case self::CHARACTERS:
			case self::SPACING_PRE_WORDS:
			case self::PROCESS_WORDS: // Used internally.
			case self::SPACING_POST_WORDS:
			case self::HTML_INSERTION:
				break;

			default:
				throw new \InvalidArgumentException( "Invalid fixer group $group." );
		}

		$this->node_fixes[ $group ][] = $fix;
	}

	/**
	 * Registers a token fix.
	 *
	 * @param Token_Fix $fix Required.
	 */
	public function register_token_fix( Token_Fix $fix ) {
		$this->process_words_fix->register_token_fix( $fix );
	}

	/**
	 * Retrieves the list of valid language plugins in the given directory.
	 *
	 * @param string $path The path in which to look for language plugin files.
	 *
	 * @return array An array in the form ( $language_code => $language_name ).
	 */
	private static function get_language_plugin_list( $path ) {
		$language_name_pattern = '/"language"\s*:\s*((".+")|(\'.+\'))\s*,/';
		$languages = [];
		$handler = opendir( $path );

		// Read all files in directory.
		while ( $file = readdir( $handler ) ) {
			// We only want the JSON files.
			if ( '.json' === substr( $file, -5 ) ) {
				$file_content = file_get_contents( $path . $file );
				if ( preg_match( $language_name_pattern, $file_content, $matches ) ) {
					$language_name = substr( $matches[1], 1, -1 );
					$language_code = substr( $file, 0, -5 );
					$languages[ $language_code ] = $language_name;
				}
			}
		}
		closedir( $handler );

		// Sort translated language names according to current locale.
		asort( $languages );

		return $languages;
	}

	/**
	 * Retrieves the list of valid hyphenation languages.
	 *
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public static function get_hyphenation_languages() {
		return self::get_language_plugin_list( __DIR__ . '/lang/' );
	}

	/**
	 * Retrieves the list of valid diacritic replacement languages.
	 *
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public static function get_diacritic_languages() {
		return self::get_language_plugin_list( __DIR__ . '/diacritics/' );
	}
}
