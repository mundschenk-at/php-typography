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
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
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
require_once dirname( __DIR__ ) . '/vendor/Masterminds/HTML5.php'; // @codeCoverageIgnore

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
	 * A hashmap for various special characters.
	 */
	public $chr = array();

	/**
	 * A hashmap of settings for the various typographic options.
	 */
	public $settings = array();

	/**
	 * A custom parser for \DOMText to separate words, whitespace etc. for HTML injection.
	 */
	private $text_parser;

	/**
	 * A DOM-based HTML5 parser.
	 */
	private $html5_parser;

	/**
	 * An array containing all self-closing HTML5 tags.
	 */
	private $self_closing_tags = array();

	/**
	 * A array of tags we should never touch.
	 */
	private $inappropriate_tags = array();

	/**
	 * An array of ( $tag => true ) for quick checking with `isset`.
	 */
	private $heading_tags = array( 'h1' => true, 'h2' => true, 'h3' => true, 'h4' => true, 'h5' => true, 'h6' => true );

	/**
	 * An array of encodings in detection order.
	 */
	private $encodings = array( 'ASCII', 'UTF-8' );

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * $encoding => array( 'strlen' => $function_name, ... )
	 */
	private $str_functions = array( 'UTF-8' => array(),
									'ASCII' => array(),
									false   => array(),
	);

	/**
	 * An array of various regex components (not complete patterns).
	 *
	 * @var array $components
	 */
	private $components = array();

	/**
	 * An array of regex patterns.
	 *
	 * @var array $regex
	 */
	private $regex = array();

	/**
	 * An array in the form of [ '$style' => [ 'open' => $chr, 'close' => $chr ] ]
	 */
	private $quote_styles = array();

	/**
	 * An array in the form of [ '$style' => [ 'parenthetical' => $chr, 'interval' => $chr ] ]
	 */
	private $dash_styles = array();

	/**
	 * An array in the form of [ '$tag' => true ]
	 */
	private $block_Tags = array();

	/**
	 * Set up a new PHP_Typography object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 * @param string $init Flag to control initialization. Valid inputs are 'now' and 'lazy'. Optional. Default 'now'.
	 */
	function __construct( $set_defaults = true, $init = 'now' )	{
		// ASCII has to be first to have chance at detection
		mb_detect_order( $this->encodings );

		// not sure if this is necessary - but error_log seems to have problems with the strings.
		// used as the default encoding for mb_* functions
		$encoding_set = mb_internal_encoding('UTF-8');

		if ( 'now' === $init ) {
			$this->init( $set_defaults );
		}
	}

	/**
	 * Load the given state.
	 *
	 * @param  array $state
	 * @return boolean True if successful, false if $state is incomplete.
	 */
	function load_state( $state ) {
		if ( ! isset( $state['block_tags'] )         ||
			 ! isset( $state['chr'] )                ||
			 ! isset( $state['quote_styles'] )       ||
			 ! isset( $state['str_functions'] )      ||
			 ! isset( $state['components'] )         ||
			 ! isset( $state['regex'] )              ||
			 ! isset( $state['self_closing_tags'] )  ||
			 ! isset( $state['inappropriate_tags'] ) ||
			 ! isset( $state['settings'] ) ) {
		 	return false;
		}

		$this->block_tags         = $state['block_tags'];
		$this->chr                = $state['chr'];
		$this->quote_styles       = $state['quote_styles'];
		$this->dash_styles        = $state['dash_styles'];
		$this->str_functions      = $state['str_functions'];
		$this->components         = $state['components'];
		$this->regex              = $state['regex'];
		$this->self_closing_tags  = $state['self_closing_tags'];
		$this->inappropriate_tags = $state['inappropriate_tags'];
		$this->settings           = $state['settings'];

		return true;
	}

	/**
	 * Retrieves to current state of the PHP_Typography object for caching.
	 *
	 * @return array The state array.
	 */
	function save_state() {
		return array(
			'block_tags'         => $this->block_tags,
			'chr'                => $this->chr,
			'quote_styles'       => $this->quote_styles,
			'dash_styles'        => $this->dash_styles,
			'str_functions'      => $this->str_functions,
			'components'         => $this->components,
			'regex'              => $this->regex,
			'self_closing_tags'  => $this->self_closing_tags,
			'inappropriate_tags' => $this->inappropriate_tags,
			'settings'           => $this->settings,
		);
	}

	/**
	 * Initialize the PHP_Typography object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	function init( $set_defaults = true ) {
		$this->block_tags = array_flip( array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ), function( $tag ) { return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::BLOCK_TAG ); } )
										+ array( 'li', 'td', 'dt' ) ); // not included as "block tags" in current HTML5-PHP version

		$this->chr['noBreakSpace']            = uchr(160);
		$this->chr['noBreakNarrowSpace']      = uchr(8239);  // used in unit spacing
		$this->chr['copyright']               = uchr(169);
		$this->chr['guillemetOpen']           = uchr(171);
		$this->chr['softHyphen']              = uchr(173);
		$this->chr['registeredMark']          = uchr(174);
		$this->chr['guillemetClose']          = uchr(187);
		$this->chr['multiplication']          = uchr(215);
		$this->chr['division']                = uchr(247);
		$this->chr['figureSpace']             = uchr(8199);
		$this->chr['thinSpace']               = uchr(8201);
		$this->chr['hairSpace']               = uchr(8202);
		$this->chr['zeroWidthSpace']          = uchr(8203);
		$this->chr['hyphen']                  = '-';        // should be uchr(8208), but IE6 chokes;
		$this->chr['noBreakHyphen']           = uchr(8209);
		$this->chr['enDash']                  = uchr(8211);
		$this->chr['emDash']                  = uchr(8212);
		$this->chr['parentheticalDash']       = uchr(8212); // defined separate from emDash so it can be redefined in set_smart_dashes_style
		$this->chr['intervalDash']            = uchr(8211); // defined separate from enDash so it can be redefined in set_smart_dashes_style
		$this->chr['parentheticalDashSpace']  = uchr(8201);
		$this->chr['intervalDashSpace']       = uchr(8201);
		$this->chr['singleQuoteOpen']         = uchr(8216);
		$this->chr['singleQuoteClose']        = uchr(8217);
		$this->chr['apostrophe']              = uchr(8217); // defined seperate from singleQuoteClose so quotes can be redefined in set_smart_quotes_language() without disrupting apostrophies
		$this->chr['singleLow9Quote']         = uchr(8218);
		$this->chr['doubleQuoteOpen']         = uchr(8220);
		$this->chr['doubleQuoteClose']        = uchr(8221);
		$this->chr['doubleLow9Quote']         = uchr(8222);
		$this->chr['ellipses']                = uchr(8230);
		$this->chr['singlePrime']             = uchr(8242);
		$this->chr['doublePrime']             = uchr(8243);
		$this->chr['singleAngleQuoteOpen']    = uchr(8249);
		$this->chr['singleAngleQuoteClose']   = uchr(8250);
		$this->chr['fractionSlash']           = uchr(8260);
		$this->chr['soundCopyMark']           = uchr(8471);
		$this->chr['serviceMark']             = uchr(8480);
		$this->chr['tradeMark']               = uchr(8482);
		$this->chr['minus']                   = uchr(8722);
		$this->chr['leftCornerBracket']       = uchr(12300);
		$this->chr['rightCornerBracket']      = uchr(12301);
		$this->chr['leftWhiteCornerBracket']  = uchr(12302);
		$this->chr['rightWhiteCornerBracket'] = uchr(12303);

		$this->quote_styles = array(
			'doubleCurled'             => array( 'open'  => uchr(8220),
				                                 'close' => uchr(8221) ),
			'doubleCurledReversed'     => array( 'open'  => uchr(8221),
				                                 'close' => uchr(8221) ),
			'doubleLow9'               => array( 'open'  => $this->chr['doubleLow9Quote'],
				                                 'close' => uchr(8221) ),
			'doubleLow9Reversed'       => array( 'open'  => $this->chr['doubleLow9Quote'],
				                                 'close' => uchr(8220) ),
			'singleCurled'             => array( 'open'  => uchr(8216),
				                                 'close' => uchr(8217) ),
			'singleCurledReversed'     => array( 'open'  => uchr(8217),
				                                 'close' => uchr(8217) ),
			'singleLow9'               => array( 'open'  => $this->chr['singleLow9Quote'],
				                                 'close' => uchr(8217) ),
			'singleLow9Reversed'       => array( 'open'  => $this->chr['singleLow9Quote'],
				                                 'close' => uchr(8216) ),
			'doubleGuillemetsFrench'   => array( 'open'  => $this->chr['guillemetOpen'].$this->chr['noBreakSpace'],
				                                 'close' => $this->chr['noBreakSpace'].$this->chr['guillemetClose'] ),
			'doubleGuillemets'         => array( 'open'  => $this->chr['guillemetOpen'],
				                                 'close' => $this->chr['guillemetClose'] ),
			'doubleGuillemetsReversed' => array( 'open'  => $this->chr['guillemetClose'],
				                                 'close' => $this->chr['guillemetOpen'] ),
			'singleGuillemets'         => array( 'open'  => $this->chr['singleAngleQuoteOpen'],
				                                 'close' => $this->chr['singleAngleQuoteClose'] ),
			'singleGuillemetsReversed' => array( 'open'  => $this->chr['singleAngleQuoteClose'],
				                                 'close' => $this->chr['singleAngleQuoteOpen'] ),
			'cornerBrackets'           => array( 'open'  => $this->chr['leftCornerBracket'],
				                                 'close' => $this->chr['rightCornerBracket'] ),
			'whiteCornerBracket'       => array( 'open'  => $this->chr['leftWhiteCornerBracket'],
				                                 'close' => $this->chr['rightWhiteCornerBracket'] ),
		);

		$this->dash_styles = array(
			'traditionalUS'        => array(
				'parenthetical'      => $this->chr['emDash'],
				'interval'           => $this->chr['enDash'],
				'parentheticalSpace' => $this->chr['thinSpace'],
				'intervalSpace'      => $this->chr['thinSpace'],
			),
			'international'        => array(
				'parenthetical'      => $this->chr['enDash'],
				'interval'           => $this->chr['enDash'],
				'parentheticalSpace' => ' ',
				'intervalSpace'      => $this->chr['hairSpace'],
			)
		);

		// set up both UTF-8 and ASCII string functions
		// UTF-8 first
		$this->str_functions['UTF-8']['strlen']     = 'mb_strlen';
		$this->str_functions['UTF-8']['str_split']  = __NAMESPACE__ . '\mb_str_split';
		$this->str_functions['UTF-8']['strtolower'] = 'mb_strtolower';
		$this->str_functions['UTF-8']['substr']     = 'mb_substr';
		$this->str_functions['UTF-8']['u']          = 'u'; // unicode flag for regex
		// now ASCII
		$this->str_functions['ASCII']['strlen']     = 'strlen';
		$this->str_functions['ASCII']['str_split']  = 'str_split';
		$this->str_functions['ASCII']['strtolower'] = 'strtolower';
		$this->str_functions['ASCII']['substr']     = 'substr';
		$this->str_functions['ASCII']['u']			= ''; // no regex flag needed
		// all other encodings get the empty array

		// set up regex patterns
		$this->initialize_components();
		$this->initialize_patterns();

		// set up some arrays for quick HTML5 introspection
		$this->self_closing_tags = array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ),	function( $tag ) { return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::VOID_TAG ); } );
		$this->inappropriate_tags = array( 'iframe', 'textarea', 'button', 'select', 'optgroup', 'option', 'map', 'style', 'head', 'title', 'script', 'applet', 'object', 'param' );

		if ( $set_defaults ) {
			$this->set_defaults();
		}
	}

	/**
	 * (Re)set various options to their default values.
	 */
	function set_defaults() {
		// general attributes
		$this->set_tags_to_ignore();
		$this->set_classes_to_ignore();
		$this->set_ids_to_ignore();

		//smart characters
		$this->set_smart_quotes();
		$this->set_smart_quotes_primary();   // added in version 1.15
		$this->set_smart_quotes_secondary(); // added in version 1.15
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

		//smart spacing
		$this->set_single_character_word_spacing();
		$this->set_fraction_spacing();
		$this->set_unit_spacing();
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

		//character styling
		$this->set_style_ampersands();
		$this->set_style_caps();
		$this->set_style_initial_quotes();
		$this->set_style_numbers();
		$this->set_initial_quote_tags();

		//hyphenation
		$this->set_hyphenation();
		$this->set_hyphenation_language();
		$this->set_min_length_hyphenation();
		$this->set_min_before_hyphenation();
		$this->set_min_after_hyphenation();
		$this->set_hyphenate_headings();
		$this->set_hyphenate_all_caps();
		$this->set_hyphenate_title_case();   // added in version 1.5
		$this->set_hyphenation_exceptions();
	}

	/**
	 * Set up our regex components for later use.
	 *
	 * Call before initialize_patterns().
	 */
	private function initialize_components() {
		// various regex components (but not complete patterns)
		$this->components['nonEnglishWordCharacters'] = "
					[0-9A-Za-z]|\x{00c0}|\x{00c1}|\x{00c2}|\x{00c3}|\x{00c4}|\x{00c5}|\x{00c6}|\x{00c7}|\x{00c8}|\x{00c9}|
					\x{00ca}|\x{00cb}|\x{00cc}|\x{00cd}|\x{00ce}|\x{00cf}|\x{00d0}|\x{00d1}|\x{00d2}|\x{00d3}|\x{00d4}|
					\x{00d5}|\x{00d6}|\x{00d8}|\x{00d9}|\x{00da}|\x{00db}|\x{00dc}|\x{00dd}|\x{00de}|\x{00df}|\x{00e0}|
					\x{00e1}|\x{00e2}|\x{00e3}|\x{00e4}|\x{00e5}|\x{00e6}|\x{00e7}|\x{00e8}|\x{00e9}|\x{00ea}|\x{00eb}|
					\x{00ec}|\x{00ed}|\x{00ee}|\x{00ef}|\x{00f0}|\x{00f1}|\x{00f2}|\x{00f3}|\x{00f4}|\x{00f5}|\x{00f6}|
					\x{00f8}|\x{00f9}|\x{00fa}|\x{00fb}|\x{00fc}|\x{00fd}|\x{00fe}|\x{00ff}|\x{0100}|\x{0101}|\x{0102}|
					\x{0103}|\x{0104}|\x{0105}|\x{0106}|\x{0107}|\x{0108}|\x{0109}|\x{010a}|\x{010b}|\x{010c}|\x{010d}|
					\x{010e}|\x{010f}|\x{0110}|\x{0111}|\x{0112}|\x{0113}|\x{0114}|\x{0115}|\x{0116}|\x{0117}|\x{0118}|
					\x{0119}|\x{011a}|\x{011b}|\x{011c}|\x{011d}|\x{011e}|\x{011f}|\x{0120}|\x{0121}|\x{0122}|\x{0123}|
					\x{0124}|\x{0125}|\x{0126}|\x{0127}|\x{0128}|\x{0129}|\x{012a}|\x{012b}|\x{012c}|\x{012d}|\x{012e}|
					\x{012f}|\x{0130}|\x{0131}|\x{0132}|\x{0133}|\x{0134}|\x{0135}|\x{0136}|\x{0137}|\x{0138}|\x{0139}|
					\x{013a}|\x{013b}|\x{013c}|\x{013d}|\x{013e}|\x{013f}|\x{0140}|\x{0141}|\x{0142}|\x{0143}|\x{0144}|
					\x{0145}|\x{0146}|\x{0147}|\x{0148}|\x{0149}|\x{014a}|\x{014b}|\x{014c}|\x{014d}|\x{014e}|\x{014f}|
					\x{0150}|\x{0151}|\x{0152}|\x{0153}|\x{0154}|\x{0155}|\x{0156}|\x{0157}|\x{0158}|\x{0159}|\x{015a}|
					\x{015b}|\x{015c}|\x{015d}|\x{015e}|\x{015f}|\x{0160}|\x{0161}|\x{0162}|\x{0163}|\x{0164}|\x{0165}|
					\x{0166}|\x{0167}|\x{0168}|\x{0169}|\x{016a}|\x{016b}|\x{016c}|\x{016d}|\x{016e}|\x{016f}|\x{0170}|
					\x{0171}|\x{0172}|\x{0173}|\x{0174}|\x{0175}|\x{0176}|\x{0177}|\x{0178}|\x{0179}|\x{017a}|\x{017b}|
					\x{017c}|\x{017d}|\x{017e}|\x{017f}
					";

		/**
		 * Find the HTML character representation for the following characters:
		 *		tab | line feed | carriage return | space | non-breaking space | ethiopic wordspace
		 *		ogham space mark | en quad space | em quad space | en-space | three-per-em space
		 *		four-per-em space | six-per-em space | figure space | punctuation space | em-space
		 *		thin space | hair space | narrow no-break space
		 *		medium mathematical space | ideographic space
		 * Some characters are used inside words, we will not count these as a space for the purpose
		 * of finding word boundaries:
		 *		zero-width-space ("&#8203;", "&#x200b;")
		 *		zero-width-joiner ("&#8204;", "&#x200c;", "&zwj;")
		 *		zero-width-non-joiner ("&#8205;", "&#x200d;", "&zwnj;")
		 */
		$this->components['htmlSpaces'] = '
			\x{00a0}		# no-break space
			|
			\x{1361}		# ethiopic wordspace
			|
			\x{2000}		# en quad-space
			|
			\x{2001}		# em quad-space
			|
			\x{2002}		# en space
			|
			\x{2003}		# em space
			|
			\x{2004}		# three-per-em space
			|
			\x{2005}		# four-per-em space
			|
			\x{2006}		# six-per-em space
			|
			\x{2007}		# figure space
			|
			\x{2008}		# punctuation space
			|
			\x{2009}		# thin space
			|
			\x{200a}		# hair space
			|
			\x{200b}		# zero-width space
			|
			\x{200c}		# zero-width joiner
			|
			\x{200d}		# zero-width non-joiner
			|
			\x{202f}		# narrow no-break space
			|
			\x{205f}		# medium mathematical space
			|
			\x{3000}		# ideographic space
			'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8)
		$this->components['normalSpaces'] = ' \f\n\r\t\v'; // equivalent to \s in non-Unicode mode

		$this->components['unitSpacingStandardUnits'] = '
			### Temporal units
			(?:ms|s|secs?|mins?|hrs?)\.?|
			milliseconds?|seconds?|minutes?|hours?|days?|years?|decades?|century|centuries|millennium|millennia|

			### Imperial units
			(?:in|ft|yd|mi)\.?|
			(?:ac|ha|oz|pt|qt|gal|lb|st)\.?
			s\.f\.|sf|s\.i\.|si|square[ ]feet|square[ ]foot|
			inch|inches|foot|feet|yards?|miles?|acres?|hectares?|ounces?|pints?|quarts?|gallons?|pounds?|stones?|

			### Metric units (with prefixes)
			(?:p|µ|[mcdhkMGT])?
			(?:[mgstAKNJWCVFSTHBL]|mol|cd|rad|Hz|Pa|Wb|lm|lx|Bq|Gy|Sv|kat|Ω|Ohm|&Omega;|&\#0*937;|&\#[xX]0*3[Aa]9;)|
			(?:nano|micro|milli|centi|deci|deka|hecto|kilo|mega|giga|tera)?
			(?:liters?|meters?|grams?|newtons?|pascals?|watts?|joules?|amperes?)|

			### Computers units (KB, Kb, TB, Kbps)
			[kKMGT]?(?:[oBb]|[oBb]ps|flops)|

			### Money
			¢|M?(?:£|¥|€|$)|

			### Other units
			°[CF]? |
			%|pi|M?px|em|en|[NSEOW]|[NS][EOW]|mbar
		'; // required modifiers: x (multiline pattern)

		$this->components['hyphensArray'] = array_unique( array( '-', $this->chr['hyphen'] ) );
		$this->components['hyphens']      = implode( '|', $this->components['hyphensArray'] );

		/*
		 // \p{Lu} equals upper case letters and should match non english characters; since PHP 4.4.0 and 5.1.0
		 // for more info, see http://www.regextester.com/pregsyntax.html#regexp.reference.unicode
		 $this->components['styleCaps']  = '
		 (?<![\w\-_'.$this->chr['zeroWidthSpace'].$this->chr['softHyphen'].'])
		 # negative lookbehind assertion
		 (
		 (?:							# CASE 1: " 9A "
		 [0-9]+					# starts with at least one number
		 \p{Lu}					# must contain at least one capital letter
		 (?:\p{Lu}|[0-9]|\-|_|'.$this->chr['zeroWidthSpace'].'|'.$this->chr['softHyphen'].')*
		 # may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
		 )
		 |
		 (?:							# CASE 2: " A9 "
		 \p{Lu}					# starts with capital letter
		 (?:\p{Lu}|[0-9])		# must be followed a number or capital letter
		 (?:\p{Lu}|[0-9]|\-|_|'.$this->chr['zeroWidthSpace'].'|'.$this->chr['softHyphen'].')*
		 # may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens

		 )
		 )
		 (?![\w\-_'.$this->chr['zeroWidthSpace'].$this->chr['softHyphen'].'])
		 # negative lookahead assertion
		 '; // required modifiers: x (multiline pattern) u (utf8)
		 */
		// Servers with PCRE compiled without "--enable-unicode-properties" fail at \p{Lu} by returning an empty string (this leaving the screen void of text
		// thus are testing this alternative
		$this->components['styleCaps'] = '
				(?<![\w\-_'.$this->chr['zeroWidthSpace'].$this->chr['softHyphen'].']) # negative lookbehind assertion
				(
					(?:							# CASE 1: " 9A "
						[0-9]+					# starts with at least one number
						[A-ZÀ-ÖØ-Ý]				# must contain at least one capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|'.$this->chr['zeroWidthSpace'].'|'.$this->chr['softHyphen'].')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
					)
					|
					(?:							# CASE 2: " A9 "
						[A-ZÀ-ÖØ-Ý]				# starts with capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9])	# must be followed a number or capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|'.$this->chr['zeroWidthSpace'].'|'.$this->chr['softHyphen'].')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens

					)
				)
				(?![\w\-_'.$this->chr['zeroWidthSpace'].$this->chr['softHyphen'].']) # negative lookahead assertion
			'; // required modifiers: x (multiline pattern) u (utf8)


		// initialize valid top level domains from IANA list
		$this->components['validTopLevelDomains'] = $this->get_top_level_domains_from_file( dirname( __DIR__ ) . '/vendor/IANA/tlds-alpha-by-domain.txt' );
		// valid URL schemes
		$this->components['urlScheme'] = '(?:https?|ftps?|file|nfs|feed|itms|itpc)';
		// combined URL pattern
		$this->components['urlPattern'] = "(?:
			\A
			(?<schema>{$this->components['urlScheme']}:\/\/)?	# Subpattern 1: contains _http://_ if it exists
			(?<domain>											# Subpattern 2: contains subdomains.domain.tld
				(?:
					[a-z0-9]									# first chr of (sub)domain can not be a hyphen
					[a-z0-9\-]{0,61}							# middle chrs of (sub)domain may be a hyphen;
																# limit qty of middle chrs so total domain does not exceed 63 chrs
					[a-z0-9]									# last chr of (sub)domain can not be a hyphen
					\.											# dot separator
				)+
				(?:
					{$this->components['validTopLevelDomains']}	# validates top level domain
				)
				(?:												# optional port numbers
					:
					(?:
						[1-5]?[0-9]{1,4} | 6[0-4][0-9]{3} | 65[0-4][0-9]{2} | 655[0-2][0-9] | 6553[0-5]
					)
				)?
			)
			(?<path>											# Subpattern 3: contains path following domain
				(?:
					\/											# marks nested directory
					[a-z0-9\"\$\-_\.\+!\*\'\(\),;\?:@=&\#]+		# valid characters within directory structure
				)*
				[\/]?											# trailing slash if any
			)
			\Z
		)"; // required modifiers: x (multiline pattern) i (case insensitive)

		$this->components['wrapEmailsEmailPattern'] = "(?:
			\A
			[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			(?:
				\.
				[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			)*
			@
			(?:
				[a-z0-9]
				[a-z0-9\-]{0,61}
				[a-z0-9]
				\.
			)+
			(?:
				{$this->components['validTopLevelDomains']}
			)
			\Z
		)"; // required modifiers: x (multiline pattern) i (case insensitive)

		$this->components['smartQuotesApostropheExceptions'] = array(
			"'tain".$this->chr['apostrophe'].'t' => $this->chr['apostrophe'] . 'tain' . $this->chr['apostrophe'] . 't',
			"'twere"                             => $this->chr['apostrophe'] . 'twere',
			"'twas"                              => $this->chr['apostrophe'] . 'twas',
			"'tis"                               => $this->chr['apostrophe'] . 'tis',
			"'til"                               => $this->chr['apostrophe'] . 'til',
			"'bout"                              => $this->chr['apostrophe'] . 'bout',
			"'nuff"                              => $this->chr['apostrophe'] . 'nuff',
			"'round"                             => $this->chr['apostrophe'] . 'round',
			"'cause"                             => $this->chr['apostrophe'] . 'cause',
			"'splainin"                          => $this->chr['apostrophe'] . 'splainin',
		);
		$this->components['smartQuotesApostropheExceptionMatches']      = array_keys( $this->components['smartQuotesApostropheExceptions'] );
		$this->components['smartQuotesApostropheExceptionReplacements'] = array_values( $this->components['smartQuotesApostropheExceptions'] );

		$this->components['smartQuotesBrackets'] = array(
			// single quotes
			"['"  => "[" . $this->chr['singleQuoteOpen'],
			"{'"  => "{" . $this->chr['singleQuoteOpen'],
			"('"  => "(" . $this->chr['singleQuoteOpen'],
			"']"  => $this->chr['singleQuoteClose'] . "}",
			"'}"  => $this->chr['singleQuoteClose'] . "}",
			"')"  => $this->chr['singleQuoteClose'] . ")",

			// double quotes
			"[\"" => "[" . $this->chr['doubleQuoteOpen'],
			"{\"" => "{" . $this->chr['doubleQuoteOpen'],
			"(\"" => "(" . $this->chr['doubleQuoteOpen'],
			"\"]" => $this->chr['doubleQuoteClose'] . "]",
			"\"}" => $this->chr['doubleQuoteClose'] . "}",
			"\")" => $this->chr['doubleQuoteClose'] . ")",

			// quotes & quotes
			"\"'" => $this->chr['doubleQuoteOpen']  . $this->chr['singleQuoteOpen'],
			"'\"" => $this->chr['singleQuoteClose'] . $this->chr['doubleQuoteClose'],
		);
		$this->components['smartQuotesBracketMatches']      = array_keys( $this->components['smartQuotesBrackets'] );
		$this->components['smartQuotesBracketReplacements'] = array_values( $this->components['smartQuotesBrackets'] );
	}

	/**
	 * Load a list of top-level domains from a file.
	 *
	 * @param string $path The full path and filename.
	 * @return string A list of top-level domains concatenated with '|'.
	 */
	function get_top_level_domains_from_file( $path ) {
		$domains = array();

		if ( file_exists( $path ) ) {
			$file = new \SplFileObject( $path );

			while ( ! $file->eof() ) {
				$line = $file->fgets();

				if ( preg_match('#^[a-zA-Z0-9][a-zA-Z0-9-]*$#', $line, $matches ) ) {
					$domains[] = strtolower( $matches[0] );
				}
			}

		}

		if ( count( $domains ) > 0 ) {
			return implode( '|', $domains );
		} else {
			return 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
		}
	}

	/**
	 * Set up our regex patterns for later use.
	 *
	 * Call after intialize_components().
	 */
	private function initialize_patterns() {
		// actual regex patterns
		$this->regex['customDiacriticsDoubleQuoteKey']   = "/(?:\")([^\"]+)(?:\"\s*=>)/";
		$this->regex['customDiacriticsSingleQuoteKey']   = "/(?:')([^']+)(?:'\s*=>)/";
		$this->regex['customDiacriticsDoubleQuoteValue'] = "/(?:=>\s*\")([^\"]+)(?:\")/";
		$this->regex['customDiacriticsSingleQuoteValue'] = "/(?:=>\s*')([^']+)(?:')/";

		$this->regex['controlCharacters'] = '/\p{C}/Su';

		$this->regex['smartQuotesSingleQuotedNumbers']       = "/(?<=\W|\A)'(\d+)'(?=\W|\Z)/u";
		$this->regex['smartQuotesDoubleQuotedNumbers']       = "/(?<=\W|\A)\"(\d+)\"(?=\W|\Z)/u";
		$this->regex['smartQuotesDoublePrime']               = "/(\b\d{1,3})''(?=\W|\Z)/u";
		$this->regex['smartQuotesDoublePrimeCompound']       = "/(\b\d{1,3})''(?=-\w)/u";
		$this->regex['smartQuotesDoublePrime1GlyphCompound'] = "/(\b\d{1,3})\"(?=-\w)/u";
		$this->regex['smartQuotesSinglePrimeCompound']       = "/(\b\d{1,3})'(?=-\w)/u";
		$this->regex['smartQuotesSingleDoublePrime']         = "/(\b\d{1,3})'(\s*)(\b\d+)''(?=\W|\Z)/u";
		$this->regex['smartQuotesSingleDoublePrime1Glyph']   = "/(\b\d{1,3})'(\s*)(\b\d+)\"(?=\W|\Z)/u";
		$this->regex['smartQuotesCommaQuote']                = "/(?<=\s|\A),(?=\S)/";
		$this->regex['smartQuotesApostropheWords']           = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])'(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesApostropheDecades']         = "/'(\d\d\b)/";
		$this->regex['smartQuotesSingleQuoteOpen']           = "/'(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesSingleQuoteClose']          = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])'/u";
		$this->regex['smartQuotesSingleQuoteOpenSpecial']    = "/(?<=\s|\A)'(?=\S)/"; // like _'¿hola?'_
		$this->regex['smartQuotesSingleQuoteCloseSpecial']   = "/(?<=\S)'(?=\s|\Z)/";
		$this->regex['smartQuotesDoubleQuoteOpen']           = "/\"(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesDoubleQuoteClose']          = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])\"/u";
		$this->regex['smartQuotesDoubleQuoteOpenSpecial']    = "/(?<=\s|\A)\"(?=\S)/";
		$this->regex['smartQuotesDoubleQuoteCloseSpecial']   = "/(?<=\S)\"(?=\s|\Z)/";

		$this->regex['smartDashesParentheticalDoubleDash']   = "/(\s|{$this->components['htmlSpaces']})--(\s|{$this->components['htmlSpaces']})/xui"; // ' -- ';
		$this->regex['smartDashesParentheticalSingleDash']   = "/(\s|{$this->components['htmlSpaces']})-(\s|{$this->components['htmlSpaces']})/xui";  // ' - ';
		$this->regex['smartDashesEnDashAll']                 = "/(\A|\s)\-([\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartDashesEnDashWords']               = "/([\w|{$this->components['nonEnglishWordCharacters']}])\-(\Z|{$this->chr['thinSpace']}|{$this->chr['hairSpace']}|{$this->chr['noBreakNarrowSpace']})/u";
		$this->regex['smartDashesEnDashNumbers']             = "/(\b\d+)\-(\d+\b)/";
		$this->regex['smartDashesEnDashPhoneNumbers']        = "/(\b\d{3})".$this->chr['enDash']."(\d{4}\b)/";
		$this->regex['smartDashesYYYY-MM-DD']                = "/
				(
					(?<=\s|\A|".$this->chr['noBreakSpace'].")
					[12][0-9]{3}
				)
				[\-".$this->chr['enDash']."]
				(
					(?:[0][1-9]|[1][0-2])
				)
				[\-".$this->chr['enDash']."]
				(
					(?:[0][1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";

		$this->regex['smartDashesMM-DD-YYYY']                = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0]?[1-9]|[1][0-2])
						)
						[\-".$this->chr['enDash']."]
						(
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
						[\-".$this->chr['enDash']."]
						(
							(?:[0]?[1-9]|[1][0-2])
						)
					)
				)
				[\-".$this->chr['enDash']."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";
		$this->regex['smartDashesYYYY-MM']                   = "/
				(
					(?<=\s|\A|".$this->chr['noBreakSpace'].")
					[12][0-9]{3}
				)
				[\-".$this->chr['enDash']."]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";

		// smart math
		// ==========

		//first, let's find math equations
		$this->regex['smartMathEquation'] = "/
				(?<=\A|\s)										# lookbehind assertion: proceeded by beginning of string or space
				[\.,\'\"\¿\¡".$this->chr['ellipses'].$this->chr['singleQuoteOpen'].$this->chr['doubleQuoteOpen'].$this->chr['guillemetOpen'].$this->chr['guillemetClose'].$this->chr['singleLow9Quote'].$this->chr['doubleLow9Quote']."]*
																# allowed proceeding punctuation
				[\-\(".$this->chr['minus']."]*					# optionally proceeded by dash, minus sign or open parenthesis
				[0-9]+											# must begin with a number
				(\.[0-9]+)?										# optionally allow decimal values after first integer
				(												# followed by a math symbol and a number
					[\/\*x\-+=\^".$this->chr['minus'].$this->chr['multiplication'].$this->chr['division']."]
																# allowed math symbols
					[\-\(".$this->chr['minus']."]*				# opptionally preceeded by dash, minus sign or open parenthesis
					[0-9]+										# must begin with a number
					(\.[0-9]+)?									# optionally allow decimal values after first integer
					[\-\(\)".$this->chr['minus']."]*			# opptionally preceeded by dash, minus sign or parenthesis
				)+
				[\.,;:\'\"\?\!".$this->chr['ellipses'].$this->chr['singleQuoteClose'].$this->chr['doubleQuoteClose'].$this->chr['guillemetOpen'].$this->chr['guillemetClose']."]*
																# allowed trailing punctuation
				(?=\Z|\s)										# lookahead assertion: followed by end of string or space
			/ux";
		// revert 4-4 to plain minus-hyphen so as to not mess with ranges of numbers (i.e. pp. 46-50)
		$this->regex['smartMathRevertRange'] = "/
				(
					(?<=\s|\A|".$this->chr['noBreakSpace'].")
					\d+
				)
				[\-".$this->chr['minus']."]
				(
					\d+
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";
		// revert fractions to basic slash
		// we'll leave styling fractions to smart_fractions
		$this->regex['smartMathRevertFraction'] = "/
				(
					(?<=\s|\A|\'|\"|".$this->chr['noBreakSpace'].")
					\d+
				)
				".$this->chr['division']."
				(
					\d+
					(?:st|nd|rd|th)?
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";
		// revert date back to original formats
		// YYYY-MM-DD
		$this->regex['smartMathRevertDateYYYY-MM-DD'] = "/
				(
					(?<=\s|\A|".$this->chr['noBreakSpace'].")
					[12][0-9]{3}
				)
				[\-".$this->chr['minus']."]
				(
					(?:[0]?[1-9]|[1][0-2])
				)
				[\-".$this->chr['minus']."]
				(
					(?:[0]?[1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";
		// MM-DD-YYYY or DD-MM-YYYY
		$this->regex['smartMathRevertDateMM-DD-YYYY'] = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0]?[1-9]|[1][0-2])
						)
						[\-".$this->chr['minus']."]
						(
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
						[\-".$this->chr['minus']."]
						(
							(?:[0]?[1-9]|[1][0-2])
						)
					)
				)
				[\-".$this->chr['minus']."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";
		// YYYY-MM or YYYY-DDD next
		$this->regex['smartMathRevertDateYYYY-MM'] = "/
				(
					(?<=\s|\A|".$this->chr['noBreakSpace'].")
					[12][0-9]{3}
				)
				[\-".$this->chr['minus']."]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";

		// MM/DD/YYYY or DD/MM/YYYY
		$this->regex['smartMathRevertDateMM/DD/YYYY'] = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0][1-9]|[1][0-2])
						)
						[\/".$this->chr['division']."]
						(
							(?:[0][1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr['noBreakSpace'].")
							(?:[0][1-9]|[12][0-9]|[3][0-1])
						)
						[\/".$this->chr['division']."]
						(
							(?:[0][1-9]|[1][0-2])
						)
					)
				)
				[\/".$this->chr['division']."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr['noBreakSpace'].")
				)
			/xu";

		// handle exponents (ie. 4^2)
		$this->regex['smartExponents'] = "/
			\b
			(\d+)
			\^
			(\w+)
			\b
		/xu";

		$this->regex['smartFractionsSpacing'] = '/\b(\d+)\s(\d+\s?\/\s?\d+)\b/';
		$this->regex['smartFractionsReplacement'] = "/
			(?<=\A|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']})		# lookbehind assertion: makes sure we are not messing up a url
			(\d+)
			(?:\s?\/\s?{$this->chr['zeroWidthSpace']}?)	# strip out any zero-width spaces inserted by wrap_hard_hyphens
			(\d+)
			(
				(?:\<sup\>(?:st|nd|rd|th)<\/sup\>)?	# handle ordinals after fractions
				(?:\Z|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']}|\.|\!|\?|\)|\;|\:|\'|\")	# makes sure we are not messing up a url
			)
			/xu";
		$this->regex['smartFractionsEscapeMM/YYYY'] = "/
			(?<=\A|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']})		# lookbehind assertion: makes sure we are not messing up a url
				(\d\d?)
			(\s?\/\s?{$this->chr['zeroWidthSpace']}?)	# capture any zero-width spaces inserted by wrap_hard_hyphens
				(
					(?:19\d\d)|(?:20\d\d) # handle 4-decimal years in the 20th and 21st centuries
				)
				(
					(?:\Z|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']}|\.|\!|\?|\)|\;|\:|\'|\")	# makes sure we are not messing up a url
				)
			/xu";

		$year_regex = array();
		for ( $year = 1900; $year < 2100; ++$year ) {
			$year_regex[] = "(?: ( $year ) (\s?\/\s?{$this->chr['zeroWidthSpace']}?) ( " . ( $year + 1 ) . " ) )";
		}
		$this->regex['smartFractionsEscapeYYYY/YYYY'] = "/
			(?<=\A|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']})		# lookbehind assertion: makes sure we are not messing up a url
			(?| " . implode( '|', $year_regex ) . " )
			(
				(?:\Z|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']}|\.|\!|\?|\)|\;|\:|\'|\")	# makes sure we are not messing up a url
			)
			/xu";

		$this->regex['smartOrdinalSuffix'] = "/\b(\d+)(st|nd|rd|th)\b/";


		// end smart math
		// ==============

		$this->regex['singleCharacterWordSpacing'] = "/
				(?:
					(\s)
					(\w)
					[{$this->components['normalSpaces']}]
					(?=\w)
				)
			/xu";

		$this->regex['dashSpacingEmDash'] = "/
				(?:
					\s
					({$this->chr['emDash']})
					\s
				)
				|
				(?:
					(?<=\S)							# lookbehind assertion
					({$this->chr['emDash']})
					(?=\S)							# lookahead assertion
				)
			/xu";
		$this->regex['dashSpacingParentheticalDash'] = "/
				(?:
					\s
					({$this->chr['enDash']})
					\s
				)
			/xu";
		$this->regex['dashSpacingIntervalDash'] = "/
				(?:
					(?<=\S)							# lookbehind assertion
					({$this->chr['enDash']})
					(?=\S)							# lookahead assertion
				)
			/xu";

		$this->regex['spaceCollapseNormal']       = "/[{$this->components['normalSpaces']}]+/xu";
		$this->regex['spaceCollapseNonBreakable'] = "/(?:[{$this->components['normalSpaces']}]|{$this->components['htmlSpaces']})*{$this->chr['noBreakSpace']}(?:[{$this->components['normalSpaces']}]|{$this->components['htmlSpaces']})*/xu";
		$this->regex['spaceCollapseOther']        = "/(?:[{$this->components['normalSpaces']}])*({$this->components['htmlSpaces']})(?:[{$this->components['normalSpaces']}]|{$this->components['htmlSpaces']})*/xu";
		$this->regex['spaceCollapseBlockStart']   = "/\A(?:[{$this->components['normalSpaces']}]|{$this->components['htmlSpaces']})+/xu";

		$this->regex['unitSpacingEscapeSpecialChars'] = "#([\[\\\^\$\.\|\?\*\+\(\)\{\}])#";
		$this->update_unit_pattern( isset( $this->settings['units'] ) ? $this->settings['units'] : array() );

		// wrap_hard_hyphens
		$this->regex['wrapHardHyphensRemoveEndingSpace'] = "/({$this->components['hyphens']}){$this->chr['zeroWidthSpace']}\$/";

		// wrap_emails
        $this->regex['wrapEmailsMatchEmails']   = "/{$this->components['wrapEmailsEmailPattern']}/xi";
        $this->regex['wrapEmailsReplaceEmails'] = '/([^a-zA-Z])/';

        // wrap_urls
        $this->regex['wrapUrlsPattern']     = "`{$this->components['urlPattern']}`xi";
        $this->regex['wrapUrlsDomainParts'] = '#(\-|\.)#';

        // style_caps
        $this->regex['styleCaps'] = "/{$this->components['styleCaps']}/xu";

        // style_numbers
        $this->regex['styleNumbers'] = "/([0-9]+)/u";

        // style_ampersands
        $this->regex['styleAmpersands'] = "/(\&amp\;)/u";

        // dewidow
        $this->regex['dewidow'] = "/
				(?:
					\A
					|
					(?:
						(?<space_before>			# subpattern 1: space before (note: ZWSP is not a space)
							[\s{$this->chr['zeroWidthSpace']}{$this->chr['softHyphen']}]+
						)
						(?<neighbor>				# subpattern 2: neighbors widow (short as possible)
							[^\s{$this->chr['zeroWidthSpace']}{$this->chr['softHyphen']}]+?
						)
					)
				)
				(?<space_between>					# subpattern 3: space between
					[\s]+                           # \s includes all special spaces (but not ZWSP) with the u flag
				)
				(?<widow>							# subpattern 4: widow
					[\w\pM\-]+?                       # \w includes all alphanumeric Unicode characters but not composed characters
				)
				(?<trailing>					    # subpattern 5: any trailing punctuation or spaces
					[^\w\pM]*
				)
				\Z
			/xu";

        // utility patterns for splitting string parameter lists into arrays
        $this->regex['parameterSplitting'] = '/[\s,]+/';

        // add the "study" flag to all our regular expressions
        foreach ( $this->regex as &$regex ) {
        	$regex .= 'S';
        }
	}


	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_tags_to_ignore( $tags = array( 'code', 'head', 'kbd', 'object', 'option', 'pre',
		 										'samp', 'script', 'noscript', 'noembed', 'select',
												'style', 'textarea', 'title', 'var', 'math',
										      ) ) {
		if ( ! is_array( $tags ) ) {
			$tags = preg_split( $this->regex['parameterSplitting'], $tags, -1, PREG_SPLIT_NO_EMPTY );
		}

		// ensure that we pass only lower-case tag names to XPath
		$tags = array_filter( array_map( 'strtolower', $tags ), 'ctype_alnum' );

		// self closing tags shouldn't be in $tags
		$this->settings['ignoreTags'] = array_unique( array_merge( array_diff( $tags, $this->self_closing_tags ), $this->inappropriate_tags ) );;
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of class names.
	 */
	 function set_classes_to_ignore( $classes = array( 'vcard', 'noTypo' ) ) {
		if ( ! is_array( $classes ) ) {
			$classes = preg_split( $this->regex['parameterSplitting'], $classes, -1, PREG_SPLIT_NO_EMPTY );
		}
		$this->settings['ignoreClasses'] = $classes;
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_ids_to_ignore( $ids = array() ) {
		if ( ! is_array( $ids ) ) {
			$ids = preg_split( $this->regex['parameterSplitting'], $ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		$this->settings['ignoreIDs'] = $ids;
	}

	/**
	 * Enable/disable typographic quotes.
	 *
	 * @param boolean $on Defaults to true.
	 */
	function set_smart_quotes( $on = true ) {
		$this->settings['smartQuotes'] = $on;
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
		if ( isset( $this->quote_styles[ $style ] ) ) {
			if ( ! empty( $this->quote_styles[ $style ]['open'] ) ) {
				$this->chr['doubleQuoteOpen'] = $this->quote_styles[ $style ]['open'];
			}
			if ( ! empty( $this->quote_styles[ $style ]['close'] ) ) {
				$this->chr['doubleQuoteClose'] = $this->quote_styles[ $style ]['close'];
			}
		} else {
			trigger_error( "Invalid quote style $style.", E_USER_WARNING );
		}
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
		if ( isset( $this->quote_styles[ $style ] ) ) {
			if ( ! empty( $this->quote_styles[ $style ]['open'] ) ) {
				$this->chr['singleQuoteOpen'] = $this->quote_styles[ $style ]['open'];
			}
			if ( ! empty( $this->quote_styles[ $style ]['close'] ) ) {
				$this->chr['singleQuoteClose'] = $this->quote_styles[ $style ]['close'];
			}
		} else {
			trigger_error( "Invalid quote style $style.", E_USER_WARNING );
		}
	}

	/**
	 * Enable/disable replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_dashes( $on = true ) {
		$this->settings['smartDashes'] = $on;
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
		if ( isset( $this->dash_styles[ $style ] ) ) {
			if ( ! empty( $this->dash_styles[ $style ]['parenthetical'] ) ) {
				$this->chr['parentheticalDash'] = $this->dash_styles[ $style ]['parenthetical'];
			}
			if ( ! empty( $this->dash_styles[ $style ]['interval'] ) ) {
				$this->chr['intervalDash'] = $this->dash_styles[ $style ]['interval'];
			}
			if ( ! empty( $this->dash_styles[ $style ]['parentheticalSpace'] ) ) {
				$this->chr['parentheticalDashSpace'] = $this->dash_styles[ $style ]['parentheticalSpace'];
			}
			if ( ! empty( $this->dash_styles[ $style ]['intervalSpace'] ) ) {
				$this->chr['intervalDashSpace'] = $this->dash_styles[ $style ]['intervalSpace'];
			}

			// Update dash spacing regex
			$this->regex['dashSpacingParentheticalDash'] = "/
				(?:
					\s
					({$this->chr['parentheticalDash']})
					\s
				)
				/xu";
			$this->regex['dashSpacingIntervalDash'] = "/
				(?:
					(?<=\S)							# lookbehind assertion
					({$this->chr['intervalDash']})
					(?=\S)							# lookahead assertion
				)
				/xu";

		} else {
			trigger_error( "Invalid dash style $style.", E_USER_WARNING );
		}
	}

	/**
	 * Enable/disable replacement of "..." with "…".
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_ellipses( $on = true ) {
		$this->settings['smartEllipses'] = $on;
	}

	/**
	 * Enable/disable replacement "creme brulee" with "crème brûlée".
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_diacritics( $on = true ) {
		$this->settings['smartDiacritics'] = $on;
	}

	/**
	 * Sets the diacritics replacement language. // FIXME
	 *
	 * @param string $lang Defaults to 'en-US'.
	 */
	function set_diacritic_language( $lang = 'en-US' ) {
		if ( isset($this->settings['diacriticLanguage']) && $this->settings['diacriticLanguage'] === $lang ) {
			return;
		}

		$this->settings['diacriticLanguage'] = $lang;

		if ( file_exists( dirname( __FILE__ ).'/diacritics/'.$this->settings['diacriticLanguage'].'.php' ) ) {
			include( 'diacritics/'.$this->settings['diacriticLanguage'].'.php' );
			$this->settings['diacriticWords'] = $diacriticWords;
		} else {
			unset( $this->settings['diacriticWords'] );
		}

		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Set up custom diacritics replacements.
	 *
	 * @param string|array $custom_replacements An array formatted array(needle=>replacement, needle=>replacement...),
	 * or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 */
	function set_diacritic_custom_replacements( $custom_replacements = array() ) {
		if ( ! is_array( $custom_replacements ) ) {
			$custom_replacements = preg_split( '/,/', $custom_replacements, -1, PREG_SPLIT_NO_EMPTY );
		}

		$replacements = array();
		foreach ( $custom_replacements as $custom_key => $custom_replacement ) {
			//account for single and double quotes
			preg_match( $this->regex['customDiacriticsDoubleQuoteKey'],   $custom_replacement, $double_quote_key_match );
			preg_match( $this->regex['customDiacriticsSingleQuoteKey'],   $custom_replacement, $single_quote_key_match );
			preg_match( $this->regex['customDiacriticsDoubleQuoteValue'], $custom_replacement, $double_quote_value_match );
			preg_match( $this->regex['customDiacriticsSingleQuoteValue'], $custom_replacement, $single_quote_value_match );

			if ( ! empty( $double_quote_key_match[1] ) ) {
				$key = $double_quote_key_match[1];
			} elseif ( ! empty( $single_quote_key_match[1] ) ) {
				$key = $single_quote_key_match[1];
			} else {
				$key = $custom_key;
			}

			if ( ! empty( $double_quote_value_match[1] ) ) {
				$value = $double_quote_value_match[1];
			} elseif ( ! empty( $single_quote_value_match[1] ) ) {
				$value = $single_quote_value_match[1];
			} else {
				$value = $custom_replacement;
			}

			if ( isset( $key ) && isset( $value ) ) {
				$replacements[ strip_tags( trim( $key ) ) ] = strip_tags( trim( $value ) );
			}
		}

		$this->settings['diacriticCustomReplacements'] = $replacements;
		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Update the pattern and replacement arrays in $settings['diacriticReplacement'].
	 * Should be called whenever a new diacritics replacement language is selected or
	 * when the custom replacements are updated.
	 */
	private function update_diacritics_replacement_arrays() {
		$patterns = array();
		$replacements = array();

		if ( ! empty( $this->settings['diacriticCustomReplacements'] ) ) {
			foreach ( $this->settings['diacriticCustomReplacements'] as $needle => $replacement ) {
				$patterns[] = "/\b$needle\b/u";
				$replacements[$needle] = $replacement;
			}
		}
		if ( ! empty( $this->settings['diacriticWords'] ) ) {
	 		foreach ( $this->settings['diacriticWords'] as $needle => $replacement ) {
				$patterns[] = "/\b$needle\b/u";
				$replacements[$needle] = $replacement;
	 		}
		}

		$this->settings['diacriticReplacement'] = array( 'patterns' => $patterns, 'replacements' => $replacements );
	}

	/**
	 * Enable/disable replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_marks( $on = true ) {
		$this->settings['smartMarks'] = $on;
	}

	/**
	 * Enable/disable proper mathematical symbols.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_math( $on = true ) {
		$this->settings['smartMath'] = $on;
	}

	/**
	 * Enable/disable replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_exponents( $on = true ) {
		$this->settings['smartExponents'] = $on;
	}

	/**
	 * Enable/disable replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_fractions( $on = true ) {
		$this->settings['smartFractions'] = $on;
	}

	/**
	 * Enable/disable replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_smart_ordinal_suffix( $on = true )	{
		$this->settings['smartOrdinalSuffix'] = $on;
	}

	/**
	 * Enable/disable forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_single_character_word_spacing( $on = true ) {
		$this->settings['singleCharacterWordSpacing'] = $on;
	}

	/**
	 * Enable/disable FIXME.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_fraction_spacing( $on = true ) {
		$this->settings['fractionSpacing'] = $on;
	}

	/**
	 * Enable/disable keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_unit_spacing($on = true) {
		$this->settings['unitSpacing'] = $on;
	}

	/**
	 * Set the list of units to keep together with their values.
	 *
	 * @param string|array $units A comma separated list or an array of units.
	 */
	function set_units( $units = array() ) {
		if ( ! is_array( $units ) ) {
			$units = preg_split( $this->regex['parameterSplitting'], $units, -1, PREG_SPLIT_NO_EMPTY );
		}

		$this->settings['units'] = $units;
		$this->update_unit_pattern( $units );
	}

	/**
	 * Update components and pattern for matching both standard and custom units.
	 *
	 * @param array $units An array of unit names.
	 */
	private function update_unit_pattern( array $units ) {
		// update components & regex pattern
		foreach ( $units as $index => $unit ) {
			// escape special chars
			$units[ $index ] = preg_replace( $this->regex['unitSpacingEscapeSpecialChars'], "\\\\$1", $unit );
		}
		$custom_units = implode( '|', $units );
		$custom_units .= ( $custom_units ) ? '|' : '';
		$this->components['unitSpacingUnits'] = $custom_units . $this->components['unitSpacingStandardUnits'];
		$this->regex['unitSpacingUnitPattern'] = "/(\d\.?)\s({$this->components['unitSpacingUnits']})\b/x";
	}

	/**
	 * Enable/disable wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_dash_spacing( $on = true )	{
		$this->settings['dashSpacing'] = $on;
	}

	/**
	 * Enable/disable removal of extra whitespace characters.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_space_collapse( $on = true ) {
		$this->settings['spaceCollapse'] = $on;
	}

	/**
	 * Enable/disable widow handling.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_dewidow( $on = true ) {
		$this->settings['dewidow'] = $on;
	}

	/**
	 * Set the maximum length of widows that will be protected.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_length( $length = 5 )	{
		$length = ( $length > 1 ) ? $length : 5;

		$this->settings['dewidowMaxLength'] = $length;
	}

	/**
	 * Set the maximum length of pulled text to keep widows company.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_pull( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->settings['dewidowMaxPull'] = $length;
	}

	/**
	 * Enable/disable wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_wrap_hard_hyphens( $on = true ) {
		$this->settings['hyphenHardWrap'] = $on;
	}

	/**
	 * Enable/disable wrapping of urls.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_url_wrap( $on = true ) {
		$this->settings['urlWrap'] = $on;
	}

	/**
	 * Enable/disable wrapping of email addresses.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_email_wrap( $on = true ) {
		$this->settings['emailWrap'] = $on;
	}

	/**
	 * Set the minimum character requirement after an URL wrapping point.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_url_wrap( $length = 5 ) {
		$length = ( $length > 0 ) ? $length : 5;

		$this->settings['urlMinAfterWrap'] = $length;
	}

	/**
	 * Enable/disable wrapping of ampersands in <span class="amp">.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_style_ampersands( $on = true ) {
		$this->settings['styleAmpersands'] = $on;
	}

	/**
	 * Enable/disable wrapping caps in <span class="caps">.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_style_caps( $on = true ) {
		$this->settings['styleCaps'] = $on;
	}

	/**
	 * Enable/disable wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_style_initial_quotes($on = true)
	{
		$this->settings['styleInitialQuotes'] = $on;
	}

	/**
	 * Enable/disable wrapping of numbers in <span class="numbers">.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_style_numbers( $on = true ) {
		$this->settings['styleNumbers'] = $on;
	}

	/**
	 * Set the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|array $units A comma separated list or an array of tag names.
	 */
	function set_initial_quote_tags( $tags = array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt') ) {
		// make array if handed a list of tags as a string
		if ( ! is_array( $tags ) ) {
			$tags = preg_split( '/[^a-z0-9]+/', $tags, -1, PREG_SPLIT_NO_EMPTY );
		}

		// store the tag array inverted (with the tagName as its index for faster lookup)
		$this->settings['initialQuoteTags'] = array_change_key_case( array_flip( $tags ), CASE_LOWER );
	}

	/**
	 * Enable/disable hyphenation.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_hyphenation( $on = true ) {
		$this->settings['hyphenation'] = $on;
	}

	/**
	 * Set the hyphenation pattern language. //FIXME
	 *
	 * @param string $lang Defaults to 'en-US'.
	 */
	function set_hyphenation_language( $lang = 'en-US' ) {
		if ( isset( $this->settings['hyphenLanguage'] ) && $this->settings['hyphenLanguage'] === $lang ) {
			return; // bail out, no need to do anything
		}

		$this->settings['hyphenLanguage'] = $lang;

		if ( file_exists( dirname( __FILE__ ).'/lang/'.$this->settings['hyphenLanguage'].'.php' ) ) {
			include( 'lang/'.$this->settings['hyphenLanguage'].'.php' );

			$this->settings['hyphenationPattern'] = $patgen;
			$this->settings['hyphenationPatternMaxSegment'] = $patgenMaxSeg;
			$this->settings['hyphenationPatternExceptions'] = $patgenExceptions;
		} else {
			unset( $this->settings['hyphenationPattern'] );
			unset( $this->settings['hyphenationPatternMaxSegment'] );
			unset( $this->settings['hyphenationPatternExceptions'] );
		}

		// make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions
		if ( isset( $this->settings['hyphenationExceptions'] ) ) {
			unset( $this->settings['hyphenationExceptions'] );
		}
	}

	/**
	 * Set the minimum length of a word that may be hyphenated.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_min_length_hyphenation( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->settings['hyphenMinLength'] = $length;
	}

	/**
	 * Set the minimum character requirement before a hyphenation point.
	 *
	 * @param number $length Defaults to 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_before_hyphenation( $length = 3 ) {
		$length = ( $length > 0 ) ? $length : 3;

		$this->settings['hyphenMinBefore'] = $length;
	}

	/**
	 * Set the minimum character requirement after a hyphenation point.
	 *
	 * @param number $length Defaults to 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_hyphenation( $length = 2 ) {
		$length = ( $length > 0 ) ? $length : 2;

		$this->settings['hyphenMinAfter'] = $length;
	}

	/**
	 * Enable/disable hyphenation of titles and headings.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_hyphenate_headings( $on = true ) {
		$this->settings['hyphenateTitle'] = $on;
	}

	/**
	 * Enable/disable hyphenation of words set completely in capital letters.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_hyphenate_all_caps( $on = true ) {
		$this->settings['hyphenateAllCaps'] = $on;
	}

	/**
	 * Enable/disable hyphenation of words starting with a capital letter.
	 *
	 * @param boolean $on Defaults to true;
	 */
	function set_hyphenate_title_case( $on = true ) {
		$this->settings['hyphenateTitleCase'] = $on;
	}

	/**
	 * Sets custom word hyphenations.
	 *
 	 * @param string|array $exceptions An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
 	 *        In the latter case, only alphanumeric characters and hyphens are recognized. The default is empty.
	 */
	function set_hyphenation_exceptions( $exceptions = array() ) {
		if ( ! is_array( $exceptions ) ) {
			$exceptions = preg_split( $this->regex['parameterSplitting'], $exceptions, -1, PREG_SPLIT_NO_EMPTY );
		}

		$exception_keys = array();
		$func = array();
		foreach ( $exceptions as $exception ) {
			$func = $this->str_functions[ mb_detect_encoding( $exception, $this->encodings, true ) ];
			if ( empty( $func ) || empty( $func['strlen'] ) ) {
				continue; // unknown encoding, abort
			}

			$exception = $func['strtolower']( $exception );
			$exception_keys[ $exception ] = preg_replace( "#-#{$func['u']}", '', $exception );
		}

		$this->settings['hyphenationCustomExceptions'] = array_flip( $exception_keys );

		// make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions
		if ( isset( $this->settings['hyphenationExceptions'] ) ) {
			unset( $this->settings['hyphenationExceptions'] );
		}
	}

	/**
	 * Modifies $html according to the defined settings.
	 *
	 * @param string $html
	 * @param string $is_title If the HTML fragment is a title. Defaults to false.
	 * @return string The processed $html.
	 */
	function process( $html, $is_title = false ) {
		if ( isset( $this->settings['ignoreTags'] ) && $is_title && ( in_array('h1', $this->settings['ignoreTags'] ) || in_array('h2', $this->settings['ignoreTags'] ) ) ) {
			return $html;
		}

		// Lazy-load our parsers
		$html5_parser = $this->get_html5_parser();
		$text_parser  = $this->get_text_parser();

		// parse the html
		$dom = $this->parse_html( $html5_parser, $html );
		$xpath = new \DOMXPath( $dom );

		// query some nodes
		$body_node = $xpath->query( '/html/body' )->item( 0 );
		$all_textnodes = $xpath->query( '//text()' );
		$tags_to_ignore = $this->query_tags_to_ignore( $xpath, $body_node );

		// start processing
		foreach ( $all_textnodes as $textnode ) {
			if ( arrays_intersect( get_ancestors( $textnode ), $tags_to_ignore ) ) {
				continue;
			}

			// we won't be doing anything with spaces, so we can jump ship if that is all we have
			if ( $textnode->isWhitespaceInElementContent() ) {
				continue;
			}

			// decode all characters except < > &
			$textnode->data = htmlspecialchars( $textnode->data, ENT_NOQUOTES, 'UTF-8' ); //returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively)

			// modify anything that requires adjacent text awareness here
			$this->smart_math( $textnode );
			$this->smart_diacritics( $textnode );
			$this->smart_quotes( $textnode );
			$this->smart_dashes( $textnode );
			$this->smart_ellipses( $textnode );
			$this->smart_marks( $textnode );

			// keep spacing after smart character replacement
			$this->single_character_word_spacing( $textnode );
			$this->dash_spacing( $textnode );
			$this->unit_spacing( $textnode );

			// break it down for a bit more granularity
			$text_parser->load( $textnode->data );
			$parsed_mixed_words = $text_parser->get_words( 'no-all-letters', 'allow-all-caps' ); // prohibit letter only words, allow caps
			$parsed_words = $text_parser->get_words( 'require-all-letters',  // require letter only words, caps allowance in setting; mutually exclusive with $parsed_mixed_words
														   ( ! empty ( $this->settings['hyphenateAllCaps'] ) ) ? 'allow-all-caps' : 'no-all-caps' );
			$parsed_other = $text_parser->get_other();

			// process individual text parts here
			$parsed_mixed_words = $this->wrap_hard_hyphens( $parsed_mixed_words );
			$parsed_words = $this->hyphenate( $parsed_words, $is_title, $textnode );
			$parsed_other = $this->wrap_urls( $parsed_other );
			$parsed_other = $this->wrap_emails( $parsed_other );

			// apply updates to unlockedText
			$text_parser->update( $parsed_mixed_words + $parsed_words + $parsed_other );
			$textnode->data = $text_parser->unload();

			// some final space manipulation
			$this->dewidow( $textnode );
			$this->space_collapse( $textnode );

			// everything that requires HTML injection occurs here (functions above assume tag-free content)
			// pay careful attention to functions below for tolerance of injected tags
			$this->smart_ordinal_suffix( $textnode );	// call before "style_numbers" and "smart_fractions"
			$this->smart_exponents( $textnode ); // call before "style_numbers"
			$this->smart_fractions( $textnode ); // call before "style_numbers" and after "smart_ordinal_suffix"
			if ( ! has_class( $textnode, 'caps' ) ) {
				$this->style_caps( $textnode ); // call before "style_numbers"
			}
			if ( ! has_class( $textnode, 'numbers' ) ) {
				$this->style_numbers( $textnode ); // call after "smart_ordinal_suffix", "smart_exponents", "smart_fractions", and "style_caps"
			}
			if ( ! has_class( $textnode, 'amp') ) {
				$this->style_ampersands( $textnode );
			}
			if ( ! has_class( $textnode, array( 'quo', 'dquo' ) ) ) {
				$this->style_initial_quotes( $textnode, $is_title );
			}

			// Until now, we've only been working on a textnode.
			// HTMLify result
			$this->replace_node_with_html( $textnode, $textnode->data );
		}

		return $html5_parser->saveHTML( $body_node->childNodes );;
	}


	/**
	 * Modifies $html according to the defined settings, in a way that is appropriate for RSS feeds
	 * (i.e. excluding processes that may not display well with limited character set intelligence).
	 *
	 * @param string $html
	 * @param string $is_title If the HTML fragment is a title. Defaults to false.
	 * @return string The processed $html.
	 */
	function process_feed( $html, $is_title = false ) {
		if ( isset( $this->settings['ignoreTags'] ) && $is_title && ( in_array( 'h1', $this->settings['ignoreTags'] ) || in_array( 'h2', $this->settings['ignoreTags'] ) ) ) {
			return $html;
		}

		// Lazy-load our parser (the text parser is not needed for feeds)
		$html5_parser = $this->get_html5_parser();

		// parse the html
		$dom = $this->parse_html( $html5_parser, $html );
		$xpath = new \DOMXPath( $dom );

		// query some nodes in the DOM
		$body_node = $xpath->query( '/html/body' )->item( 0 );
		$all_textnodes = $xpath->query( '//text()' );
		$tags_to_ignore = $this->query_tags_to_ignore( $xpath, $body_node );

		// start processing
		foreach ( $all_textnodes as $textnode ) {
			if ( arrays_intersect( get_ancestors( $textnode ), $tags_to_ignore ) ) {
				continue;
			}

			// we won't be doing anything with spaces, so we can jump ship if that is all we have
			if ( $textnode->isWhitespaceInElementContent() ) {
				continue;
			}

			// decode all characters except < > &
			$textnode->data = htmlspecialchars( $textnode->data, ENT_NOQUOTES, 'UTF-8' ); // returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively)

			// modify anything that requires adjacent text awareness here
			$this->smart_quotes( $textnode );
			$this->smart_dashes( $textnode );
			$this->smart_ellipses( $textnode );
			$this->smart_marks( $textnode );

			// Until now, we've only been working on a textnode.
			// HTMLify result
			$this->replace_node_with_html( $textnode, $textnode->data );
		}

		return $html5_parser->saveHTML( $body_node->childNodes );;
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
		// silence some parsing errors for invalid HTML
		set_error_handler( array( $this, 'handle_parsing_errors' ) );

		// do the actual parsing
		$dom = $parser->loadHTML( '<body>' . $html . '</body>' );
		$dom->encoding = 'UTF-8';

		// restor original error handling
		restore_error_handler();

		return $dom;
	}

	/**
	 * Silently handle certain HTML parsing errors.
	 *
	 * @param int    $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int    $errline
	 * @param array  $errcontext
	 *
	 * @return boolean Returns true if the error was handled, false otherwise.
	 */
	public function handle_parsing_errors( $errno, $errstr, $errfile, $errline, array $errcontext ) {
		if ( ! ( error_reporting() & $errno ) ) {
			return true; // not interesting
		}

		if ( $errno & E_USER_WARNING && 0 === substr_compare( $errfile, 'DOMTreeBuilder.php', -18 ) ) {
			// ignore warnings from parser
			return true;
		}

		// let PHP handle the rest
		return false;
	}


	/**
	 * Retrieve an array of nodes that should be skipped during processing.
	 *
	 * @param \DOMXPath $xpath A valid XPath instance for the DOM to be queried.
	 * @param \DOMNode  $initial_node The starting node of the XPath query.
	 * @return array An array of \DOMNode (can be empty).
	 */
	function query_tags_to_ignore( \DOMXPath $xpath, \DOMNode $initial_node ) {
		$elements = array();
		$query_parts = array();
		if ( ! empty( $this->settings['ignoreTags'] ) ) {
			$query_parts[] = '//' . implode( ' | //', $this->settings['ignoreTags'] );
		}
		if ( ! empty( $this->settings['ignoreClasses'] ) ) {
			$query_parts[] = "//*[contains(concat(' ', @class, ' '), ' " . implode( " ') or contains(concat(' ', @class, ' '), ' ", $this->settings['ignoreClasses'] ) . " ')]";
		}
		if ( ! empty( $this->settings['ignoreIDs'] ) ) {
			$query_parts[] = '//*[@id=\'' . implode( '\' or @id=\'', $this->settings['ignoreIDs'] ) . '\']';
		}

		if ( ! empty( $query_parts ) ) {
			$ignore_query = implode(' | ', $query_parts );

			if ( false !== ( $nodelist = $xpath->query( $ignore_query, $initial_node ) ) ) {
				$elements = nodelist_to_array( $nodelist );
			}
		}

		return $elements;
	}

	/**
	 * Retrieve the last character of the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element
	 *
	 * @return string A single character (or the empty string).
	 */
	function get_prev_chr( \DOMNode $element ) {
		$previous_textnode = $this->get_previous_textnode( $element );

		if ( isset( $previous_textnode ) && isset( $previous_textnode->data ) ) {
			// first determine encoding
			$func = $this->str_functions[ mb_detect_encoding( $previous_textnode->data, $this->encodings, true ) ];

			if ( ! empty( $func ) && ! empty( $func['substr'] ) ) {
				return preg_replace( $this->regex['controlCharacters'], '', $func['substr']( $previous_textnode->data, - 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}


	/**
	 * Retrieve the first character of the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element
	 *
	 * @return string A single character (or the empty string).
	 */
	function get_next_chr( \DOMNode $element ) {
		$next_textnode = $this->get_next_textnode($element);

		if ( isset( $next_textnode ) && isset( $next_textnode->data ) ) {
			// first determine encoding
			$func = $this->str_functions[ mb_detect_encoding( $next_textnode->data, $this->encodings, true ) ];

			if ( ! empty( $func ) && ! empty( $func['substr'] ) ) {
				return preg_replace( $this->regex['controlCharacters'], '', $func['substr']( $next_textnode->data, 0, 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}


	/**
	 * Retrieve the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element
	 *
	 * @return \DOMText Null if $element is a block-level element or no text sibling exists.
	 */
	function get_previous_textnode( \DOMNode $element = null) {
		if ( ! isset( $element ) ) {
			return null;
		}

		$previous_textnode = null;
		$node = $element;

		if ( $node instanceof \DOMElement && isset( $this->block_tags[ $node->tagName ] ) ) {
			return null;
		}

		while ( ( $node = $node->previousSibling ) && empty( $previous_textnode ) ) {
			$previous_textnode = $this->get_last_textnode( $node );
		}

		if ( ! $previous_textnode ) {
			$previous_textnode = $this->get_previous_textnode( $element->parentNode );
		}

		return $previous_textnode;
	}


	/**
	 * Retrieve the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element
	 *
	 * @return \DOMText Null if $element is a block-level element or no text sibling exists.
	 */
	function get_next_textnode( \DOMNode $element = null ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		$next_textnode = null;
		$node = $element;

		if ( $node instanceof \DOMElement && isset( $this->block_tags[ $node->tagName ] ) ) {
			return null;
		}

		while ( ( $node = $node->nextSibling ) && empty( $next_textnode ) ) {
			$next_textnode = $this->get_first_textnode( $node );
		}

		if ( ! $next_textnode ) {
			$next_textnode = $this->get_next_textnode( $element->parentNode );
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
			// return null if $element is neither \DOMText nor \DOMElement
			return null;
		} elseif ( $recursive && isset( $this->block_tags[ $element->tagName ] ) ) {
			return null;
		}

		$first_textnode = null;

		if ( $element->hasChildNodes() ) {
			$children = $element->childNodes;
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
			// return null if $element is neither \DOMText nor \DOMElement
			return null;
		} elseif ( $recursive && isset( $this->block_tags[ $element->tagName ] ) ) {
			return null;
		}

		$last_textnode = null;

		if ( $element->hasChildNodes() ) {
			$children = $element->childNodes;
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
	 * @param \DOMText $textnode
	 */
	function smart_quotes( \DOMText $textnode ) {
		if ( empty( $this->settings['smartQuotes'] ) ) {
			return;
		}

		// need to get context of adjacent characters outside adjacent inline tags or HTML comment
		// if we have adjacent characters add them to the text
		$previous_character = $this->get_prev_chr( $textnode );
		if ( '' !== $previous_character ) {
			$textnode->data = $previous_character.$textnode->data;
		}
		$next_character = $this->get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data =  $textnode->data.$next_character;
		}

		////Logic

		// before primes, handle quoted numbers
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleQuotedNumbers'], $this->chr['singleQuoteOpen'].'$1'.$this->chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoubleQuotedNumbers'], $this->chr['doubleQuoteOpen'].'$1'.$this->chr['doubleQuoteClose'], $textnode->data );

		// guillemets
		$textnode->data = str_replace( "<<",       $this->chr['guillemetOpen'],  $textnode->data );
		$textnode->data = str_replace( "&lt;&lt;", $this->chr['guillemetOpen'],  $textnode->data );
		$textnode->data = str_replace( ">>",       $this->chr['guillemetClose'], $textnode->data );
		$textnode->data = str_replace( "&gt;&gt;", $this->chr['guillemetClose'], $textnode->data );

		// primes
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleDoublePrime'],         '$1' . $this->chr['singlePrime'] . '$2$3' . $this->chr['doublePrime'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleDoublePrime1Glyph'],   '$1' . $this->chr['singlePrime'] . '$2$3' . $this->chr['doublePrime'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoublePrime'],               '$1' . $this->chr['doublePrime'],                                      $textnode->data ); // should not interfere with regular quote matching
		$textnode->data = preg_replace( $this->regex['smartQuotesSinglePrimeCompound'],       '$1' . $this->chr['singlePrime'],                                      $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoublePrimeCompound'],       '$1' . $this->chr['doublePrime'],                                      $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoublePrime1GlyphCompound'], '$1' . $this->chr['doublePrime'],                                      $textnode->data );

		// backticks
		$textnode->data = str_replace( "``", $this->chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = str_replace( "`",  $this->chr['singleQuoteOpen'],  $textnode->data );
		$textnode->data = str_replace( "''", $this->chr['doubleQuoteClose'], $textnode->data );

		// comma quotes
		$textnode->data = str_replace( ",,", $this->chr['doubleLow9Quote'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesCommaQuote'], $this->chr['singleLow9Quote'], $textnode->data ); // like _,¿hola?'_

		// apostrophes
		$textnode->data = preg_replace( $this->regex['smartQuotesApostropheWords'],   $this->chr['apostrophe'],      $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesApostropheDecades'], $this->chr['apostrophe'].'$1', $textnode->data ); // decades: '98
		$textnode->data = str_replace( $this->components['smartQuotesApostropheExceptionMatches'], $this->components['smartQuotesApostropheExceptionReplacements'], $textnode->data );

		// quotes
		$textnode->data = str_replace( $this->components['smartQuotesBracketMatches'], $this->components['smartQuotesBracketReplacements'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleQuoteOpen'],         $this->chr['singleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleQuoteClose'],        $this->chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleQuoteOpenSpecial'],  $this->chr['singleQuoteOpen'],  $textnode->data ); // like _'¿hola?'_
		$textnode->data = preg_replace( $this->regex['smartQuotesSingleQuoteCloseSpecial'], $this->chr['singleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoubleQuoteOpen'],         $this->chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoubleQuoteClose'],        $this->chr['doubleQuoteClose'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoubleQuoteOpenSpecial'],  $this->chr['doubleQuoteOpen'],  $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartQuotesDoubleQuoteCloseSpecial'], $this->chr['doubleQuoteClose'], $textnode->data );

		// quote catch-alls - assume left over quotes are closing - as this is often the most complicated position, thus most likely to be missed
		$textnode->data = str_replace( "'", $this->chr['singleQuoteClose'], $textnode->data );
		$textnode->data = str_replace( '"', $this->chr['doubleQuoteClose'], $textnode->data );

		// if we have adjacent characters remove them from the text
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
	 * @param \DOMText $textnode
	 */
	function smart_dashes( \DOMText $textnode ) {
		if ( empty( $this->settings['smartDashes'] ) ) {
			return;
		}

		$textnode->data = str_replace( '---', $this->chr['emDash'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartDashesParentheticalDoubleDash'], "\$1{$this->chr['parentheticalDash']}\$2", $textnode->data );
		$textnode->data = str_replace( '--', $this->chr['enDash'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartDashesParentheticalSingleDash'], "\$1{$this->chr['parentheticalDash']}\$2", $textnode->data );

		$textnode->data = preg_replace( $this->regex['smartDashesEnDashAll'],          '$1'.$this->chr['enDash'].'$2',        $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartDashesEnDashWords'] ,       '$1'.$this->chr['enDash'].'$2',        $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartDashesEnDashNumbers'],      '$1'.$this->chr['intervalDash'].'$2',  $textnode->data );
		$textnode->data = preg_replace( $this->regex['smartDashesEnDashPhoneNumbers'], '$1'.$this->chr['noBreakHyphen'].'$2', $textnode->data ); // phone numbers
		$textnode->data =  str_replace( "xn{$this->chr['enDash']}",                    'xn--',                                $textnode->data ); // revert messed-up punycode

		// revert dates back to original formats

		// YYYY-MM-DD
		$textnode->data = preg_replace( $this->regex['smartDashesYYYY-MM-DD'], "$1-$2-$3",     $textnode->data );
		// MM-DD-YYYY or DD-MM-YYYY
		$textnode->data = preg_replace( $this->regex['smartDashesMM-DD-YYYY'], "$1$3-$2$4-$5", $textnode->data );
		// YYYY-MM or YYYY-DDDD next
		$textnode->data = preg_replace( $this->regex['smartDashesYYYY-MM'],    "$1-$2",        $textnode->data );
	}

	/**
	 * Apply smart ellipses (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	 function smart_ellipses( \DOMText $textnode ) {
		if ( empty( $this->settings['smartEllipses'] ) ) {
			return;
		}

		$textnode->data = str_replace( array( '....', '. . . .' ), '.' . $this->chr['ellipses'], $textnode->data );
		$textnode->data = str_replace( array( '...',  '. . .' ),   $this->chr['ellipses'],       $textnode->data );
	}

	/**
	 * Apply smart diacritics (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function smart_diacritics( \DOMText $textnode )	{
		if ( empty( $this->settings['smartDiacritics'] ) ) {
			return; // abort
		}

		if ( ! empty( $this->settings['diacriticReplacement'] ) &&
			 ! empty( $this->settings['diacriticReplacement']['patterns'] ) &&
			 ! empty( $this->settings['diacriticReplacement']['replacements'] ) ) {

			// Uses "word" => "replacement" pairs from an array to make fast preg_* replacements.
			$replacements = $this->settings['diacriticReplacement']['replacements'];
			$textnode->data = preg_replace_callback( $this->settings['diacriticReplacement']['patterns'], function( $match ) use ( $replacements ) {
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
	 * @param \DOMText $textnode
	 */
	function smart_marks( \DOMText $textnode ) {
		if ( empty( $this->settings['smartMarks'] ) ) {
			return;
		}

		$textnode->data = str_replace( array( '(c)', '(C)' ),   $this->chr['copyright'],      $textnode->data );
		$textnode->data = str_replace( array( '(r)', '(R)' ),   $this->chr['registeredMark'], $textnode->data );
		$textnode->data = str_replace( array( '(p)', '(P)' ),   $this->chr['soundCopyMark'],  $textnode->data );
		$textnode->data = str_replace( array( '(sm)', '(SM)' ), $this->chr['serviceMark'],    $textnode->data );
		$textnode->data = str_replace( array( '(tm)', '(TM)' ), $this->chr['tradeMark'],      $textnode->data );
	}

	/**
	 * Apply smart math (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function smart_math( \DOMText $textnode ) {
		if ( empty( $this->settings['smartMath'] ) ) {
			return;
		}

		//first, let's find math equations
		$textnode->data = preg_replace_callback( $this->regex['smartMathEquation'], array($this, '_smart_math_callback'), $textnode->data );

		// revert 4-4 to plain minus-hyphen so as to not mess with ranges of numbers (i.e. pp. 46-50)
		$textnode->data = preg_replace( $this->regex['smartMathRevertRange'], '$1-$2', $textnode->data );

		// revert fractions to basic slash
		// we'll leave styling fractions to smart_fractions
		$textnode->data = preg_replace( $this->regex['smartMathRevertFraction'], '$1/$2', $textnode->data );

		// revert date back to original formats
		// YYYY-MM-DD
		$textnode->data = preg_replace( $this->regex['smartMathRevertDateYYYY-MM-DD'], '$1-$2-$3', $textnode->data );
		// MM-DD-YYYY or DD-MM-YYYY
		$textnode->data = preg_replace( $this->regex['smartMathRevertDateMM-DD-YYYY'], '$1$3-$2$4-$5', $textnode->data );
		// YYYY-MM or YYYY-DDD next
		$textnode->data = preg_replace( $this->regex['smartMathRevertDateYYYY-MM'], '$1-$2', $textnode->data );
		// MM/DD/YYYY or DD/MM/YYYY
		$textnode->data = preg_replace( $this->regex['smartMathRevertDateMM/DD/YYYY'], '$1$3/$2$4/$5', $textnode->data );
	}

	/**
	 * Callback function for smart math.
	 *
	 * @param array $matches
	 */
	private function _smart_math_callback( array $matches ) {
		$matches[0] = str_replace( '-', $this->chr['minus'], $matches[0] );
		$matches[0] = str_replace( '/', $this->chr['division'], $matches[0] );
		$matches[0] = str_replace( 'x', $this->chr['multiplication'], $matches[0] );
		$matches[0] = str_replace( '*', $this->chr['multiplication'], $matches[0] );

		return $matches[0];
	}

	/**
	 * Apply smart exponents (if enabled).
	 * Purposefully seperated from smart_math because of HTML code injection.
	 *
	 * @param \DOMText $textnode
	 */
	function smart_exponents( \DOMText $textnode ) {
		if ( empty( $this->settings['smartExponents'] ) ) {
			return;
		}

		// handle exponents (ie. 4^2)
		$textnode->data = preg_replace( $this->regex['smartExponents'], '$1<sup>$2</sup>', $textnode->data );
	}

	/**
	 * Apply smart fractions (if enabled).
	 *
	 * Call before style_numbers, but after smart_ordinal_suffix.
	 * Purposefully seperated from smart_math because of HTML code injection.
	 *
	 * @param \DOMText $textnode
	 */
	function smart_fractions( \DOMText $textnode ) {
		if ( empty( $this->settings['smartFractions'] ) && empty( $this->settings['fractionSpacing'] ) ) {
			return;
		}

		if ( ! empty( $this->settings['fractionSpacing'] ) && ! empty( $this->settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $this->regex['smartFractionsSpacing'], '$1'.$this->chr['noBreakNarrowSpace'].'$2', $textnode->data );
		} elseif ( ! empty( $this->settings['fractionSpacing'] ) && empty( $this->settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $this->regex['smartFractionsSpacing'], '$1'.$this->chr['noBreakSpace'].'$2', $textnode->data );
		}

		if ( !empty($this->settings['smartFractions']) ) {
			// Escape sequences we don't want fractionified
 			$textnode->data = preg_replace( $this->regex['smartFractionsEscapeYYYY/YYYY'], '$1_E_S_C_A_P_E_D_$2$3$4', $textnode->data );
 			$textnode->data = preg_replace( $this->regex['smartFractionsEscapeMM/YYYY'], '$1_E_S_C_A_P_E_D_$2$3$4', $textnode->data );

 			// Replace fractions
 			$textnode->data = preg_replace( $this->regex['smartFractionsReplacement'], '<sup>$1</sup>'.$this->chr['fractionSlash'].'<sub>$2</sub>$3', $textnode->data );

 			// Unescape escaped sequences
 			$textnode->data = str_replace( '_E_S_C_A_P_E_D_', '', $textnode->data );
		}
	}

	/**
	 * Apply smart ordinal suffix (if enabled).
	 *
	 * Call before style_numbers.
	 *
	 * @param \DOMText $textnode
	 */
	function smart_ordinal_suffix( \DOMText $textnode) {
		if ( empty( $this->settings['smartOrdinalSuffix'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['smartOrdinalSuffix'], '$1'.'<sup>$2</sup>', $textnode->data );
	}

	/**
	 * Prevent single character words from being alone (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function single_character_word_spacing( \DOMText $textnode ) {
		if ( empty( $this->settings['singleCharacterWordSpacing'] ) ) {
			return;
		}

		// add $nextChr and $prevChr for context
		$previous_character = $this->get_prev_chr( $textnode );
		if ( '' !== $previous_character) {
			$textnode->data = $previous_character . $textnode->data;
		}

		$next_character = $this->get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data = $textnode->data . $next_character;
		}

		$textnode->data = preg_replace( $this->regex['singleCharacterWordSpacing'], '$1$2' . $this->chr['noBreakSpace'], $textnode->data );

		// if we have adjacent characters remove them from the text
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
	 * @param \DOMText $textnode
	 */
	function dash_spacing( \DOMText $textnode )	{
		if ( empty( $this->settings['dashSpacing'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['dashSpacingEmDash'],            $this->chr['intervalDashSpace'] . '$1$2' . $this->chr['intervalDashSpace'],           $textnode->data );
		$textnode->data = preg_replace( $this->regex['dashSpacingParentheticalDash'], $this->chr['parentheticalDashSpace'] . '$1$2' . $this->chr['parentheticalDashSpace'], $textnode->data );
		$textnode->data = preg_replace( $this->regex['dashSpacingIntervalDash'],      $this->chr['intervalDashSpace'] . '$1$2' . $this->chr['intervalDashSpace'],           $textnode->data );
	}

	/**
	 * Collapse spaces (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function space_collapse( \DOMText $textnode ) {
		if ( empty( $this->settings['spaceCollapse'] ) ) {
			return;
		}

		// normal spacing
		$textnode->data = preg_replace( $this->regex['spaceCollapseNormal'], ' ', $textnode->data );

		// nbsp get's priority.  if nbsp exists in a string of spaces, it collapses to nbsp
		$textnode->data = preg_replace( $this->regex['spaceCollapseNonBreakable'], $this->chr['noBreakSpace'], $textnode->data );

		// for any other spaceing, replace with the first occurance of an unusual space character
		$textnode->data = preg_replace( $this->regex['spaceCollapseOther'], '$1', $textnode->data );

		// remove all spacing at beginning of block level elements
		if( '' === $this->get_prev_chr( $textnode ) ) { // we have the first text in a block level element
			$textnode->data = preg_replace( $this->regex['spaceCollapseBlockStart'], '', $textnode->data );
		}
	}

	/**
	 * Prevent values being split from their units (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function unit_spacing( \DOMText $textnode ) {
		if ( empty( $this->settings['unitSpacing'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['unitSpacingUnitPattern'], '$1'.$this->chr['noBreakNarrowSpace'].'$2', $textnode->data );
	}

	/**
	 * Wrap hard hypens with zero-width spaces (if enabled).
	 *
	 * @param array $parsed_text_tokens
	 */
	function wrap_hard_hyphens( array $parsed_text_tokens ) {
		if ( ! empty( $this->settings['hyphenHardWrap'] ) || ! empty( $this->settings['smartDashes'] ) ) {

			foreach ( $parsed_text_tokens as &$text_token ) {

				if ( isset( $this->settings['hyphenHardWrap'] ) && $this->settings['hyphenHardWrap'] ) {
					$text_token['value'] = str_replace( $this->components['hyphensArray'], '-' . $this->chr['zeroWidthSpace'], $text_token['value'] );
					$text_token['value'] = str_replace( '_', '_' . $this->chr['zeroWidthSpace'], $text_token['value'] );
					$text_token['value'] = str_replace( '/', '/' . $this->chr['zeroWidthSpace'], $text_token['value'] );

					$text_token['value'] = preg_replace( $this->regex['wrapHardHyphensRemoveEndingSpace'], '$1', $text_token['value'] );
				}

				if ( ! empty( $this->settings['smartDashes'] ) ) {
					// handled here because we need to know we are inside a word and not a url
					$text_token['value'] = str_replace( '-', $this->chr['hyphen'], $text_token['value'] );
				}
			}
		}

		return $parsed_text_tokens;
	}

	/**
	 * Prevent widows (if enabled).
	 *
	 * @param \DOMText $textnode
	 */
	function dewidow( \DOMText $textnode ) {
		// intervening inline tags may interfere with widow identification, but that is a sacrifice of using the parser
		// intervening tags will only interfere if they separate the widow from previous or preceding whitespace
		if ( empty( $this->settings['dewidow'] ) || empty( $this->settings['dewidowMaxPull'] ) || empty( $this->settings['dewidowMaxLength'] ) ) {
			return;
		}

		if ( '' === $this->get_next_chr( $textnode ) ) {
			// we have the last type "text" child of a block level element
			$textnode->data = preg_replace_callback( $this->regex['dewidow'], array( $this, '_dewidow_callback' ), $textnode->data );
		}
	}

	/**
	 * Callback function for de-widowing.
	 *
	 * @param array $widow Regex matching array.
	 * @return string
	 */
	private function _dewidow_callback( array $widow ) {
		$func = $this->str_functions[ mb_detect_encoding( $widow[0], $this->encodings, true ) ];

		// if we are here, we know that widows are being protected in some fashion
		//   with that, we will assert that widows should never be hyphenated or wrapped
		//   as such, we will strip soft hyphens and zero-width-spaces
		$widow['widow']    = str_replace( $this->chr['zeroWidthSpace'], '', $widow['widow'] ); // TODO: check if this can match here
		$widow['widow']    = str_replace( $this->chr['softHyphen'],     '', $widow['widow'] ); // TODO: check if this can match here
		$widow['trailing'] = preg_replace( "/\s+/{$func['u']}", $this->chr['noBreakSpace'], $widow['trailing'] );
		$widow['trailing'] = str_replace( $this->chr['zeroWidthSpace'], '', $widow['trailing'] );
		$widow['trailing'] = str_replace( $this->chr['softHyphen'],     '', $widow['trailing'] );

		// eject if widows neighbor is proceeded by a no break space (the pulled text would be too long)
		if ( '' === $widow['space_before'] || strstr( $this->chr['noBreakSpace'], $widow['space_before'] ) ) {
			return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
		}


		// eject if widows neighbor length exceeds the max allowed or widow length exceeds max allowed
		if ( $func['strlen']( $widow['neighbor'] ) > $this->settings['dewidowMaxPull'] ||
			 $func['strlen']( $widow['widow'] )    > $this->settings['dewidowMaxLength'] ) {
				return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
		}

		// never replace thin and hair spaces with &nbsp;
		switch ( $widow['space_between'] ) {
			case $this->chr['thinSpace']:
			case $this->chr['hairSpace']:
				return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
		}

		// lets protect some widows!
		return $widow['space_before'] . $widow['neighbor'] . $this->chr['noBreakSpace'] . $widow['widow'] . $widow['trailing'];
	}

	/**
	 * Wrap URL parts zero-width spaces (if enabled).
	 *
	 * @param array $parsed_text_tokens
	 */
	function wrap_urls( array $parsed_text_tokens ) {
		if ( empty( $this->settings['urlWrap'] ) || empty( $this->settings['urlMinAfterWrap'] ) ) {
			return $parsed_text_tokens;
		}

		// test for and parse urls
		foreach ( $parsed_text_tokens as &$text_token ) {
			if ( preg_match( $this->regex['wrapUrlsPattern'], $text_token['value'], $url_match ) ) {

				// $url_match['schema'] holds "http://"
				// $url_match['domain'] holds "subdomains.domain.tld"
				// $url_match['path']   holds the path after the domain

				$http = ( $url_match['schema'] ) ? $url_match[1].$this->chr['zeroWidthSpace'] : '';

				$domain_parts = preg_split( $this->regex['wrapUrlsDomainParts'], $url_match['domain'], -1, PREG_SPLIT_DELIM_CAPTURE );

				// this is a hack, but it works
				// first, we hyphenate each part
				// we need it formated like a group of words
				$parsed_words_like = array();
				foreach ( $domain_parts as $key => $part ) {
					$parsed_words_like[ $key ]['value'] = $part;
				}

				// do the hyphenation
				$parsed_words_like = $this->do_hyphenate( $parsed_words_like );

				// restore format
				foreach ( $parsed_words_like as $key => $parsed_word ) {
					$domain_parts[ $key ] = $parsed_word['value'];
				}
				foreach ( $domain_parts as $key => &$part ) {
					// then we swap out each soft-hyphen" with a zero-space
					$part = str_replace( $this->chr['softHyphen'], $this->chr['zeroWidthSpace'], $part );

					// we also insert zero-spaces before periods and hyphens

					if ( $key > 0 && 1 === strlen( $part ) ) {
						$part = $this->chr['zeroWidthSpace'] . $part;
					}
				}

				// lastly let's recombine
				$domain = implode( $domain_parts );

				// break up the URL path to individual characters
				$path_parts = str_split( $url_match['path'], 1 );
				$path_count = count( $path_parts );
				$path = '';
				foreach ( $path_parts as $index => $path_part ) {
					if ( 0 === $index || $path_count - $index < $this->settings['urlMinAfterWrap'] ) {
						$path .= $path_part;
					} else {
						$path .= $this->chr['zeroWidthSpace'] . $path_part;
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
	 * @param array $parsed_text_tokens
	 */
	function wrap_emails( array $parsed_text_tokens )	{
		if ( empty( $this->settings['emailWrap'] ) ) {
			return $parsed_text_tokens;
		}

		// test for and parse urls
		foreach ( $parsed_text_tokens as &$text_token ) {
			if ( preg_match( $this->regex['wrapEmailsMatchEmails'], $text_token['value'], $email_match ) ) {
				$text_token['value'] = preg_replace( $this->regex['wrapEmailsReplaceEmails'], '$1' . $this->chr['zeroWidthSpace'], $text_token['value'] );
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
	 * @param \DOMText $textnode
	 */
	function style_caps( \DOMText $textnode ) {
		if ( empty($this->settings['styleCaps'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['styleCaps'], '<span class="caps">$1</span>', $textnode->data );
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

		$parent = $node->parentNode;
		if ( empty( $parent ) ) {
			return $node; // abort early to save cycles
		}

		set_error_handler( array( $this, 'handle_parsing_errors' ) );

		$html_fragment = $this->get_html5_parser()->loadHTMLFragment( $content );
		if ( ! empty( $html_fragment ) ) {
			$imported_fragment = $node->ownerDocument->importNode( $html_fragment, true );

			if ( ! empty( $imported_fragment ) ) {
				// save the children of the imported DOMDocumentFragment before replacement
				$children = nodelist_to_array( $imported_fragment->childNodes );

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
	 * @param \DOMText $textnode
	 */
	function style_numbers( \DOMText $textnode ) {
		if ( empty( $this->settings['styleNumbers'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['styleNumbers'], '<span class="numbers">$1</span>', $textnode->data );
	}

	/**
	 * Wraps ampersands in <span class="amp"> (i.e. H&amp;J becomes H<span class="amp">&amp;</span>J),
	 * if enabled.
	 *
	 * Call after style_caps so H&amp;J becomes <span class="caps">H<span class="amp">&amp;</span>J</span>.
	 * Note that all standalone ampersands were previously converted to &amp;.
	 * Only call if you are certain that no html tags have been injected containing "&amp;".
	 *
	 * @param \DOMText $textnode
	 */
	function style_ampersands( \DOMText $textnode ) {
		if ( empty( $this->settings['styleAmpersands'] ) ) {
			return;
		}

		$textnode->data = preg_replace( $this->regex['styleAmpersands'], '<span class="amp">$1</span>', $textnode->data );
	}

	/**
	 * Styles initial quotes and guillemets (if enabled).
	 *
	 * @param \DOMText $textnode
	 * @param boolean $is_title Default false.
	 */
	function style_initial_quotes( \DOMText $textnode, $is_title = false ) {
		if ( empty( $this->settings['styleInitialQuotes'] ) || empty( $this->settings['initialQuoteTags'] ) ) {
			return;
		}

		if ( '' === $this->get_prev_chr( $textnode )) { // we have the first text in a block level element

			$func = $this->str_functions[ mb_detect_encoding( $textnode->data, $this->encodings, true ) ];
			$first_character = $func['substr']( $textnode->data, 0, 1 );

			switch ( $first_character ) {
				case "'":
				case $this->chr['singleQuoteOpen']:
				case $this->chr['singleLow9Quote']:
				case ',':
				case '"':
				case $this->chr['doubleQuoteOpen']:
				case $this->chr['guillemetOpen']:
				case $this->chr['guillemetClose']:
				case $this->chr['doubleLow9Quote']:

					$block_level_parent = $this->get_block_parent( $textnode );
					$block_level_parent = isset( $block_level_parent->tagName ) ? $block_level_parent->tagName : false;

					if ( $is_title ) {
						// assume page title is h2
						$block_level_parent = 'h2';
					}

					if ( $block_level_parent && isset( $this->settings['initialQuoteTags'][ $block_level_parent ] ) ) {
						switch( $first_character ) {
							case "'":
							case $this->chr['singleQuoteOpen']:
							case $this->chr['singleLow9Quote']:
							case ",":
								$span_class = 'quo';
								break;

							default: // double quotes or guillemets
								$span_class = 'dquo';
						}

						$textnode->data =  '<span class="' . $span_class . '">' . $first_character . '</span>' . $func['substr']( $textnode->data, 1, $func['strlen']( $textnode->data ) );
					}
			}
		}
	}

	/**
	 * Inject the PatGen segments pattern into the PatGen words pattern.
	 *
	 * @param array  $word_pattern
	 * @param array  $segment_pattern
	 * @param number $segment_position
	 * @param number $segment_length
	 */
	function hyphenation_pattern_injection( array $word_pattern, array $segment_pattern, $segment_position, $segment_length ) {

		for ( $number_position = $segment_position;
			  $number_position <= $segment_position + $segment_length;
			  $number_position++ ) {

			$word_pattern[ $number_position ] =
				( intval( $word_pattern[ $number_position ] ) >= intval( $segment_pattern[ $number_position - $segment_position ] ) ) ?
					$word_pattern[ $number_position ] : $segment_pattern[ $number_position - $segment_position ];
		}

		return $word_pattern;
	}

	/**
	 * Hyphenate given text fragment (if enabled).
	 *
	 * Actual work is done in do_hyphenate().
	 *
	 * @param array $parsed_text_tokens Filtered to words.
	 * @param boolean $isTitle Flag to indicate title fragments. Optional. Default false.
	 * @param \DOMText $textnode The textnode corresponding to the $parsed_text_tokens. Optional. Default null.
	 */
	function hyphenate( $parsed_text_tokens, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $this->settings['hyphenation'] ) ) {
			return $parsed_text_tokens; // abort
		}

		$is_heading = false;
		if ( ! empty( $textnode ) && ! empty( $textnode->parentNode ) ) {
			$block_level_parent = $this->get_block_parent( $textnode );
			$block_level_parent = isset( $block_level_parent->tagName ) ? $block_level_parent->tagName : false;

			if ( $block_level_parent && isset( $this->heading_tags[ $block_level_parent ] ) ) {
				$is_heading = true;
			}
		}

		if ( empty( $this->settings['hyphenateTitle'] ) && ( $is_title || $is_heading ) ) {
			return $parsed_text_tokens; // abort
		}

		// call functionality as seperate function so it can be run without test for setting['hyphenation'] - such as with url wrapping
		return $this->do_hyphenate( $parsed_text_tokens );
	}

	/**
	 * Really hyphenate given text fragment.
	 *
	 * @param  array $parsed_text_tokens Filtered to words.
	 * @return array The hyphenated text token.
	 */
	function do_hyphenate( array $parsed_text_tokens ) {

		if ( empty( $this->settings['hyphenMinLength'] )              ||
			 empty( $this->settings['hyphenMinBefore'] )              ||
		   ! isset( $this->settings['hyphenationPatternMaxSegment'] ) ||
		   ! isset( $this->settings['hyphenationPatternExceptions'] ) ||
		   ! isset( $this->settings['hyphenationPattern'] ) ) {

		   	return $parsed_text_tokens;
		}

		// make sure we have full exceptions list
		if ( ! isset( $this->settings['hyphenationExceptions'] ) ) {
			$exceptions = array();

			if ( $this->settings['hyphenationPatternExceptions'] || ! empty( $this->settings['hyphenationCustomExceptions'] ) ) {
				if ( isset( $this->settings['hyphenationCustomExceptions'] ) ) {
					// merges custom and language specific word hyphenations
					$exceptions = array_merge( $this->settings['hyphenationCustomExceptions'], $this->settings['hyphenationPatternExceptions'] );
				} else {
					$exceptions = $this->settings['hyphenationPatternExceptions'];
				}
			}

			$this->settings['hyphenationExceptions'] = $exceptions;
		}

		$func = array(); // quickly reference string functions according to encoding
		foreach ( $parsed_text_tokens as &$text_token ) {
			$func = $this->str_functions[ mb_detect_encoding( $text_token['value'], $this->encodings, true ) ];
			if ( empty( $func ) || empty( $func['strlen'] ) ) {
				continue; // unknown encoding, abort
			}

			$word_length = $func['strlen']( $text_token['value'] );
			$the_key     = $func['strtolower']( $text_token['value'] );

			if ( $word_length < $this->settings['hyphenMinLength'] ) {
				continue;
			}

			// If this is a capitalized word, and settings do not allow hyphenation of such, abort!
			// Note: This is different than uppercase words, where we are looking for title case
			if ( empty( $this->settings['hyphenateTitleCase'] ) && $func['substr']( $the_key , 0 , 1 ) !== $func['substr']( $text_token['value'], 0, 1 ) ) {
				continue;
			}

			// give exceptions preference
			if ( isset($this->settings['hyphenationExceptions'][ $the_key ] ) ) {
				// Set the word_pattern - this method keeps any contextually important capitalization
				$lowercase_hyphened_word        = $this->settings['hyphenationExceptions'][ $the_key ];
				$lowercase_hyphened_word_parts  = $func['str_split']( $lowercase_hyphened_word, 1 );
				$lowercase_hyphened_word_length = $func['strlen']( $lowercase_hyphened_word );

				$word_pattern = array();
				for ( $i = 0; $i < $lowercase_hyphened_word_length; $i++ ) {
					if( '-' === $lowercase_hyphened_word_parts[ $i ] ) {
						$word_pattern[] = '9';
						$i++;
					} else {
						$word_pattern[] = '0';
					}
				}
				$word_pattern[] = '0'; //for consistent length with the other word patterns
			}

			if ( ! isset( $word_pattern ) ) {
				// first we set up the matching pattern to be a series of zeros one character longer than $parsedTextToken
				$word_pattern = array();
				for ( $i = 0; $i < $word_length +1; $i++ ) {
					$word_pattern[] = '0';
				}

				// we grab all possible segments from $parsedTextToken of length 1 through $this->settings['hyphenationPatternMaxSegment']
				for ( $segment_length = 1; ( $segment_length <= $word_length ) && ( $segment_length <= $this->settings['hyphenationPatternMaxSegment'] ); $segment_length++ ) {
					for ( $segment_position = 0; $segment_position + $segment_length <= $word_length; $segment_position++ ) {
						$segment = $func['strtolower']( $func['substr']( $text_token['value'], $segment_position, $segment_length ) );

						if ( 0 === $segment_position && isset($this->settings['hyphenationPattern']['begin'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->settings['hyphenationPattern']['begin'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}

						if ( $segment_position + $segment_length === $word_length && isset($this->settings['hyphenationPattern']['end'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->settings['hyphenationPattern']['end'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}

						if ( isset($this->settings['hyphenationPattern']['all'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->settings['hyphenationPattern']['all'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}
					}
				}
			}

			// add soft-hyphen based on $wordPattern
			$word_parts = $func['str_split']( $text_token['value'], 1 );

			$hyphenated_word = '';
			for ( $i = 0; $i < $word_length; $i++ ) {
				if ( is_odd( intval( $word_pattern[ $i ] ) ) && ( $i >= $this->settings['hyphenMinBefore']) && ( $i < $word_length - $this->settings['hyphenMinAfter'] ) ) {
					$hyphenated_word .= $this->chr['softHyphen'] . $word_parts[ $i ];
				} else {
					$hyphenated_word .= $word_parts[ $i ];
				}
			}

			$text_token['value'] = $hyphenated_word;
			unset( $word_pattern );
		}

		return $parsed_text_tokens;
	}

	/**
	 * Returns the nearest block-level parent.
	 *
	 * @param \DOMNode $element The node to get the containing block-level tag.
	 *
	 * @return \DOMNode
	 */
	function get_block_parent( \DOMNode $element ) {
		$parent = $element->parentNode;

		while ( isset( $parent->tagName ) && ! isset( $this->block_tags[ $parent->tagName ] ) && ! empty( $parent->parentNode ) ) {
			$parent = $parent->parentNode;
		}

		return $parent;
	}

	/**
	 * Retrieve a unique hash value for the current settings.
	 *
	 * @param number $max_length The maximum number of bytes returned.
	 * @return string An binary hash value for the current settings limited to $max_length.
	 */
	public function get_settings_hash( $max_length = 8 ) {
		return substr( md5( json_encode( $this->settings ), true ), 0, $max_length );
	}

	/**
	 * Retrieve the HTML5 parser instance.
	 *
	 * @return \Mastermind\HTML5
	 */
	public function get_html5_parser() {
		// Lazy-load HTML5 parser
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
		// Lazy-load text parser
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
