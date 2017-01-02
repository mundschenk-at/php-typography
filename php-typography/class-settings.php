<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2009-2011 KINGdesk, LLC.
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
 * Store settings for the PHP_Typography class.
 *
 *  @author Peter Putzer <github@mundschenk.at>
 */
class Settings implements \ArrayAccess {

	/**
	 * A hashmap for various special characters.
	 *
	 * @var array
	 */
	protected $chr = array();

	/**
	 * A hashmap of settings for the various typographic options.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * An array containing all self-closing HTML5 tags.
	 *
	 * @var array
	 */
	protected $self_closing_tags = array();

	/**
	 * A array of tags we should never touch.
	 *
	 * @var array
	 */
	protected $inappropriate_tags = array();

	/**
	 * An array of encodings in detection order.
	 *
	 * @var array
	 */
	protected $encodings = array( 'ASCII', 'UTF-8' );

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @var array $encoding => array( 'strlen' => $function_name, ... ).
	 */
	protected $str_functions = array(
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
	 * An array of various regex components (not complete patterns).
	 *
	 * @var array $components
	 */
	protected $components = array();

	/**
	 * An array of regex patterns.
	 *
	 * @var array $regex
	 */
	protected $regex = array();

	/**
	 * An array in the form of [ '$style' => [ 'open' => $chr, 'close' => $chr ] ]
	 *
	 * @var array
	 */
	protected $quote_styles = array();

	/**
	 * An array in the form of [ '$style' => [ 'parenthetical' => $chr, 'interval' => $chr ] ]
	 *
	 * @var array
	 */
	protected $dash_styles = array();

	/**
	 * An array in the form of [ '$tag' => true ]
	 *
	 * @var array
	 */
	protected $block_tags = array();

	/**
	 * Set up a new Settings object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	function __construct( $set_defaults = true ) {

		// ASCII has to be first to have chance at detection.
		mb_detect_order( $this->encodings );

		// Not sure if this is necessary - but error_log seems to have problems with the strings.
		// Used as the default encoding for mb_* functions.
		$encoding_set = mb_internal_encoding( 'UTF-8' );

		$this->init( $set_defaults );
	}

	/**
	 * Provide access to named settings (object syntax).
	 *
	 * @param string $key The settings key.
	 *
	 * @return mixed
	 */
	public function &__get( $key ) {
		return $this->data[ $key ];
	}

	/**
	 * Change a named setting (object syntax).
	 *
	 * @param string $key   The settings key.
	 * @param mixed  $value The settings value.
	 */
	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Check if a named setting exists (object syntax).
	 *
	 * @param string $key The settings key.
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Unset a named setting.
	 *
	 * @param string $key The settings key.
	 */
	public function __unset( $key ) {
		unset( $this->data[ $key ] );
	}

	/**
	 * Change a named setting (array syntax).
	 *
	 * @param string $offset The settings key.
	 * @param mixed  $value  The settings value.
	 */
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->data[] = $value;
		} else {
			$this->data[ $offset ] = $value;
		}
	}

	/**
	 * Check if a named setting exists (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	/**
	 * Unset a named setting (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}

	/**
	 * Provide access to named settings (array syntax).
	 *
	 * @param string $offset The settings key.
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
	}

	/**
	 * Retrieve the array of named characters.
	 *
	 * @return array
	 */
	public function get_named_characters() {
		return $this->chr;
	}

	/**
	 * Retrieve the named character.
	 *
	 * @param string $name The character name.
	 *
	 * @return string|bool Returns the character or false if it does not exist.
	 */
	public function chr( $name ) {
		if ( isset( $this->chr[ $name ] ) ) {
			return $this->chr[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Retrieve the named components calculated from the current settings.
	 *
	 * @return array
	 */
	public function get_components() {
		return $this->components;
	}

	/**
	 * Retrieve the named component string.
	 *
	 * @param string $name The component name.
	 *
	 * @return string|bool Returns the component or false if it does not exist.
	 */
	public function component( $name ) {
		if ( isset( $this->components[ $name ] ) ) {
			return $this->components[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Retrieve the regular expressions calculated from the current settings.
	 *
	 * @return array
	 */
	public function get_regular_expressions() {
		return $this->regex;
	}

	/**
	 * Retrieve the named regular expression.
	 *
	 * @param string $name The regex name.
	 *
	 * @return string|bool Returns the regular expression or false if it does not exist.
	 */
	public function regex( $name ) {
		if ( isset( $this->regex[ $name ] ) ) {
			return $this->regex[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Initialize the PHP_Typography object.
	 *
	 * @param boolean $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	private function init( $set_defaults = true ) {
		$this->block_tags = array_flip( array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ), function( $tag ) { return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::BLOCK_TAG ); } )
										+ array( 'li', 'td', 'dt' ) ); // not included as "block tags" in current HTML5-PHP version.

		$this->chr['noBreakSpace']            = uchr( 160 );
		$this->chr['noBreakNarrowSpace']      = uchr( 160 );  // used in unit spacing - can be changed to 8239 via set_true_no_break_narrow_space.
		$this->chr['copyright']               = uchr( 169 );
		$this->chr['guillemetOpen']           = uchr( 171 );
		$this->chr['softHyphen']              = uchr( 173 );
		$this->chr['registeredMark']          = uchr( 174 );
		$this->chr['guillemetClose']          = uchr( 187 );
		$this->chr['multiplication']          = uchr( 215 );
		$this->chr['division']                = uchr( 247 );
		$this->chr['figureSpace']             = uchr( 8199 );
		$this->chr['thinSpace']               = uchr( 8201 );
		$this->chr['hairSpace']               = uchr( 8202 );
		$this->chr['zeroWidthSpace']          = uchr( 8203 );
		$this->chr['hyphen']                  = '-';          // should be uchr(8208), but IE6 chokes.
		$this->chr['noBreakHyphen']           = uchr( 8209 );
		$this->chr['enDash']                  = uchr( 8211 );
		$this->chr['emDash']                  = uchr( 8212 );
		$this->chr['parentheticalDash']       = uchr( 8212 ); // defined separate from emDash so it can be redefined in set_smart_dashes_style.
		$this->chr['intervalDash']            = uchr( 8211 ); // defined separate from enDash so it can be redefined in set_smart_dashes_style.
		$this->chr['parentheticalDashSpace']  = uchr( 8201 );
		$this->chr['intervalDashSpace']       = uchr( 8201 );
		$this->chr['singleQuoteOpen']         = uchr( 8216 );
		$this->chr['singleQuoteClose']        = uchr( 8217 );
		$this->chr['apostrophe']              = uchr( 8217 ); // defined seperate from singleQuoteClose so quotes can be redefined in set_smart_quotes_language() without disrupting apostrophies.
		$this->chr['singleLow9Quote']         = uchr( 8218 );
		$this->chr['doubleQuoteOpen']         = uchr( 8220 );
		$this->chr['doubleQuoteClose']        = uchr( 8221 );
		$this->chr['doubleLow9Quote']         = uchr( 8222 );
		$this->chr['ellipses']                = uchr( 8230 );
		$this->chr['singlePrime']             = uchr( 8242 );
		$this->chr['doublePrime']             = uchr( 8243 );
		$this->chr['singleAngleQuoteOpen']    = uchr( 8249 );
		$this->chr['singleAngleQuoteClose']   = uchr( 8250 );
		$this->chr['fractionSlash']           = uchr( 8260 );
		$this->chr['soundCopyMark']           = uchr( 8471 );
		$this->chr['serviceMark']             = uchr( 8480 );
		$this->chr['tradeMark']               = uchr( 8482 );
		$this->chr['minus']                   = uchr( 8722 );
		$this->chr['leftCornerBracket']       = uchr( 12300 );
		$this->chr['rightCornerBracket']      = uchr( 12301 );
		$this->chr['leftWhiteCornerBracket']  = uchr( 12302 );
		$this->chr['rightWhiteCornerBracket'] = uchr( 12303 );

		$this->quote_styles = array(
			'doubleCurled'             => array(
				'open'  => uchr( 8220 ),
				'close' => uchr( 8221 ),
			),
			'doubleCurledReversed'     => array(
				'open'  => uchr( 8221 ),
				'close' => uchr( 8221 ),
			),
			'doubleLow9'               => array(
				'open'  => $this->chr['doubleLow9Quote'],
				'close' => uchr( 8221 ),
			),
			'doubleLow9Reversed'       => array(
				'open'  => $this->chr['doubleLow9Quote'],
				'close' => uchr( 8220 ),
			),
			'singleCurled'             => array(
				'open'  => uchr( 8216 ),
				'close' => uchr( 8217 ),
			),
			'singleCurledReversed'     => array(
				'open'  => uchr( 8217 ),
				'close' => uchr( 8217 ),
			),
			'singleLow9'               => array(
				'open'  => $this->chr['singleLow9Quote'],
				'close' => uchr( 8217 ),
			),
			'singleLow9Reversed'       => array(
				'open'  => $this->chr['singleLow9Quote'],
				'close' => uchr( 8216 ),
			),
			'doubleGuillemetsFrench'   => array(
				'open'  => $this->chr['guillemetOpen'] . $this->chr['noBreakNarrowSpace'],
				'close' => $this->chr['noBreakNarrowSpace'] . $this->chr['guillemetClose'],
			),
			'doubleGuillemets'         => array(
				'open'  => $this->chr['guillemetOpen'],
				'close' => $this->chr['guillemetClose'],
			),
			'doubleGuillemetsReversed' => array(
				'open'  => $this->chr['guillemetClose'],
				'close' => $this->chr['guillemetOpen'],
			),
			'singleGuillemets'         => array(
				'open'  => $this->chr['singleAngleQuoteOpen'],
				'close' => $this->chr['singleAngleQuoteClose'],
			),
			'singleGuillemetsReversed' => array(
				'open'  => $this->chr['singleAngleQuoteClose'],
				'close' => $this->chr['singleAngleQuoteOpen'],
			),
			'cornerBrackets'           => array(
				'open'  => $this->chr['leftCornerBracket'],
				'close' => $this->chr['rightCornerBracket'],
			),
			'whiteCornerBracket'       => array(
				'open'  => $this->chr['leftWhiteCornerBracket'],
				'close' => $this->chr['rightWhiteCornerBracket'],
			),
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
			),
		);

		// All other encodings get the empty array.
		// Set up regex patterns.
		$this->initialize_components();
		$this->initialize_patterns();

		// Set up some arrays for quick HTML5 introspection.
		$this->self_closing_tags = array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ),	function( $tag ) { return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::VOID_TAG );
} );
		$this->inappropriate_tags = array( 'iframe', 'textarea', 'button', 'select', 'optgroup', 'option', 'map', 'style', 'head', 'title', 'script', 'applet', 'object', 'param' );

		if ( $set_defaults ) {
			$this->set_defaults();
		}
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
		$this->set_smart_quotes_primary();
		$this->set_smart_quotes_secondary();
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
		$this->set_hyphenate_title_case();
		$this->set_hyphenate_compounds();
		$this->set_hyphenation_exceptions();

		// Parser error handling.
		$this->set_ignore_parser_errors();
	}

	/**
	 * Set up our regex components for later use.
	 *
	 * Call before initialize_patterns().
	 */
	private function initialize_components() {
		// Various regex components (but not complete patterns).
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
			'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).
		$this->components['normalSpaces'] = ' \f\n\r\t\v'; // equivalent to \s in non-Unicode mode.

		// Hanging punctuation.
		$this->components['doubleHangingPunctuation'] = "
			\"
			{$this->chr['doubleQuoteOpen']}
			{$this->chr['doubleQuoteClose']}
			{$this->chr['doubleLow9Quote']}
			{$this->chr['doublePrime']}
			{$this->quote_styles['doubleCurled']['open']}
			{$this->quote_styles['doubleCurled']['close']}

			"; // requires modifiers: x (multiline pattern) u (utf8).
		$this->components['singleHangingPunctuation'] = "
			'
			{$this->chr['singleQuoteOpen']}
			{$this->chr['singleQuoteClose']}
			{$this->chr['singleLow9Quote']}
			{$this->chr['singlePrime']}
			{$this->quote_styles['singleCurled']['open']}
			{$this->quote_styles['singleCurled']['close']}
			{$this->chr['apostrophe']}

			"; // requires modifiers: x (multiline pattern) u (utf8).

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
		'; // required modifiers: x (multiline pattern).

		$this->components['hyphensArray'] = array_unique( array( '-', $this->chr['hyphen'] ) );
		$this->components['hyphens']      = implode( '|', $this->components['hyphensArray'] );

		$this->components['numbersPrime'] = '\b(?:\d+\/)?\d{1,3}';

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
		// thus are testing this alternative.
		$this->components['styleCaps'] = '
				(?<![\w\-_' . $this->chr['zeroWidthSpace'] . $this->chr['softHyphen'] . ']) # negative lookbehind assertion
				(
					(?:							# CASE 1: " 9A "
						[0-9]+					# starts with at least one number
					    (?:\-|_|' . $this->chr['zeroWidthSpace'] . '|' . $this->chr['softHyphen'] . ')*
					    	                    # may contain hyphens, underscores, zero width spaces, or soft hyphens,
						[A-ZÀ-ÖØ-Ý]				# but must contain at least one capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . $this->chr['zeroWidthSpace'] . '|' . $this->chr['softHyphen'] . ')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
					)
					|
					(?:							# CASE 2: " A9 "
						[A-ZÀ-ÖØ-Ý]				# starts with capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9])	# must be followed a number or capital letter
						(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . $this->chr['zeroWidthSpace'] . '|' . $this->chr['softHyphen'] . ')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
					)
				)
				(?![\w\-_' . $this->chr['zeroWidthSpace'] . $this->chr['softHyphen'] . ']) # negative lookahead assertion
			'; // required modifiers: x (multiline pattern) u (utf8).

		// Initialize valid top level domains from IANA list.
		$this->components['validTopLevelDomains'] = $this->get_top_level_domains_from_file( dirname( __DIR__ ) . '/vendor/IANA/tlds-alpha-by-domain.txt' );
		// Valid URL schemes.
		$this->components['urlScheme'] = '(?:https?|ftps?|file|nfs|feed|itms|itpc)';
		// Combined URL pattern.
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
		)"; // required modifiers: x (multiline pattern) i (case insensitive).

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
		)"; // required modifiers: x (multiline pattern) i (case insensitive).

		$this->components['smartQuotesApostropheExceptions'] = array(
			"'tain" . $this->chr['apostrophe'] . 't' => $this->chr['apostrophe'] . 'tain' . $this->chr['apostrophe'] . 't',
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

		// These patterns need to be updated whenever the quote style changes.
		$this->update_smart_quotes_brackets();

		// Marker for strings that should not be replaced.
		$this->components['escapeMarker'] = '_E_S_C_A_P_E_D_';
	}

	/**
	 * Update smartQuotesBrackets component after quote style change.
	 */
	private function update_smart_quotes_brackets() {
		$this->components['smartQuotesBrackets'] = array(
			// Single quotes.
			"['"  => '[' . $this->chr['singleQuoteOpen'],
			"{'"  => '{' . $this->chr['singleQuoteOpen'],
			"('"  => '(' . $this->chr['singleQuoteOpen'],
			"']"  => $this->chr['singleQuoteClose'] . ']',
			"'}"  => $this->chr['singleQuoteClose'] . '}',
			"')"  => $this->chr['singleQuoteClose'] . ')',

			// Double quotes.
			'["' => '[' . $this->chr['doubleQuoteOpen'],
			'{"' => '{' . $this->chr['doubleQuoteOpen'],
			'("' => '(' . $this->chr['doubleQuoteOpen'],
			'"]' => $this->chr['doubleQuoteClose'] . ']',
			'"}' => $this->chr['doubleQuoteClose'] . '}',
			'")' => $this->chr['doubleQuoteClose'] . ')',

			// Quotes & quotes.
			"\"'" => $this->chr['doubleQuoteOpen'] . $this->chr['singleQuoteOpen'],
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

				if ( preg_match( '#^[a-zA-Z0-9][a-zA-Z0-9-]*$#', $line, $matches ) ) {
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
		// Actual regex patterns.
		$this->regex['customDiacriticsDoubleQuoteKey']   = '/(?:")([^"]+)(?:"\s*=>)/';
		$this->regex['customDiacriticsSingleQuoteKey']   = "/(?:')([^']+)(?:'\s*=>)/";
		$this->regex['customDiacriticsDoubleQuoteValue'] = '/(?:=>\s*")([^"]+)(?:")/';
		$this->regex['customDiacriticsSingleQuoteValue'] = "/(?:=>\s*')([^']+)(?:')/";

		$this->regex['controlCharacters'] = '/\p{C}/Su'; // obsolete.

		$this->regex['smartQuotesSingleQuotedNumbers']       = "/(?<=\W|\A)'([^\"]*\d+)'(?=\W|\Z)/u";
		$this->regex['smartQuotesDoubleQuotedNumbers']       = '/(?<=\W|\A)"([^"]*\d+)"(?=\W|\Z)/u';
		$this->regex['smartQuotesDoublePrime']               = "/({$this->components['numbersPrime']})''(?=\W|\Z)/u";
		$this->regex['smartQuotesDoublePrimeCompound']       = "/({$this->components['numbersPrime']})''(?=-\w)/u";
		$this->regex['smartQuotesDoublePrime1Glyph']         = "/({$this->components['numbersPrime']})\"(?=\W|\Z)/u";
		$this->regex['smartQuotesDoublePrime1GlyphCompound'] = "/({$this->components['numbersPrime']})\"(?=-\w)/u";
		$this->regex['smartQuotesSinglePrime']               = "/({$this->components['numbersPrime']})'(?=\W|\Z)/u";
		$this->regex['smartQuotesSinglePrimeCompound']       = "/({$this->components['numbersPrime']})'(?=-\w)/u";
		$this->regex['smartQuotesSingleDoublePrime']         = "/({$this->components['numbersPrime']})'(\s*)(\b(?:\d+\/)?\d+)''(?=\W|\Z)/u";
		$this->regex['smartQuotesSingleDoublePrime1Glyph']   = "/({$this->components['numbersPrime']})'(\s*)(\b(?:\d+\/)?\d+)\"(?=\W|\Z)/u";
		$this->regex['smartQuotesCommaQuote']                = '/(?<=\s|\A),(?=\S)/';
		$this->regex['smartQuotesApostropheWords']           = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])'(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesApostropheDecades']         = "/'(\d\d\b)/";
		$this->regex['smartQuotesSingleQuoteOpen']           = "/'(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesSingleQuoteClose']          = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])'/u";
		$this->regex['smartQuotesSingleQuoteOpenSpecial']    = "/(?<=\s|\A)'(?=\S)/"; // like _'¿hola?'_.
		$this->regex['smartQuotesSingleQuoteCloseSpecial']   = "/(?<=\S)'(?=\s|\Z)/";
		$this->regex['smartQuotesDoubleQuoteOpen']           = "/\"(?=[\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartQuotesDoubleQuoteClose']          = "/(?<=[\w|{$this->components['nonEnglishWordCharacters']}])\"/u";
		$this->regex['smartQuotesDoubleQuoteOpenSpecial']    = '/(?<=\s|\A)"(?=\S)/';
		$this->regex['smartQuotesDoubleQuoteCloseSpecial']   = '/(?<=\S)"(?=\s|\Z)/';

		$this->regex['smartDashesParentheticalDoubleDash']   = "/(\s|{$this->components['htmlSpaces']})--(\s|{$this->components['htmlSpaces']})/xui"; // ' -- '.
		$this->regex['smartDashesParentheticalSingleDash']   = "/(\s|{$this->components['htmlSpaces']})-(\s|{$this->components['htmlSpaces']})/xui";  // ' - '.
		$this->regex['smartDashesEnDashAll']                 = "/(\A|\s)\-([\w|{$this->components['nonEnglishWordCharacters']}])/u";
		$this->regex['smartDashesEnDashWords']               = "/([\w|{$this->components['nonEnglishWordCharacters']}])\-(\Z|{$this->chr['thinSpace']}|{$this->chr['hairSpace']}|{$this->chr['noBreakNarrowSpace']})/u";
		$this->regex['smartDashesEnDashNumbers']             = "/(\b\d+)\-(\d+\b)/";
		$this->regex['smartDashesEnDashPhoneNumbers']        = "/(\b\d{3})" . $this->chr['enDash'] . "(\d{4}\b)/";
		$this->regex['smartDashesYYYY-MM-DD']                = '/
                (
                    (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                    [12][0-9]{3}
                )
                [\-' . $this->chr['enDash'] . ']
                (
                    (?:[0][1-9]|[1][0-2])
                )
                [\-' . $this->chr['enDash'] . "]
				(
					(?:[0][1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';

		$this->regex['smartDashesMM-DD-YYYY']                = '/
                (?:
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0]?[1-9]|[1][0-2])
                        )
                        [\-' . $this->chr['enDash'] . ']
                        (
                            (?:[0]?[1-9]|[12][0-9]|[3][0-1])
                        )
                    )
                    |
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0]?[1-9]|[12][0-9]|[3][0-1])
                        )
                        [\-' . $this->chr['enDash'] . ']
                        (
                            (?:[0]?[1-9]|[1][0-2])
                        )
                    )
                )
                [\-' . $this->chr['enDash'] . "]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';
		$this->regex['smartDashesYYYY-MM']                   = '/
                (
                    (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                    [12][0-9]{3}
                )
                [\-' . $this->chr['enDash'] . "]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';

		// Smart math.
		// First, let's find math equations.
		$this->regex['smartMathEquation'] = "/
				(?<=\A|\s)										# lookbehind assertion: proceeded by beginning of string or space
				[\.,\'\"\¿\¡" . $this->chr['ellipses'] . $this->chr['singleQuoteOpen'] . $this->chr['doubleQuoteOpen'] . $this->chr['guillemetOpen'] . $this->chr['guillemetClose'] . $this->chr['singleLow9Quote'] . $this->chr['doubleLow9Quote'] . ']*
                                                                # allowed proceeding punctuation
                [\-\(' . $this->chr['minus'] . ']*                  # optionally proceeded by dash, minus sign or open parenthesis
                [0-9]+                                          # must begin with a number
                (\.[0-9]+)?                                     # optionally allow decimal values after first integer
                (                                               # followed by a math symbol and a number
                    [\/\*x\-+=\^' . $this->chr['minus'] . $this->chr['multiplication'] . $this->chr['division'] . ']
                                                                # allowed math symbols
                    [\-\(' . $this->chr['minus'] . ']*              # opptionally preceeded by dash, minus sign or open parenthesis
                    [0-9]+                                      # must begin with a number
                    (\.[0-9]+)?                                 # optionally allow decimal values after first integer
                    [\-\(\)' . $this->chr['minus'] . "]*			# opptionally preceeded by dash, minus sign or parenthesis
				)+
				[\.,;:\'\"\?\!" . $this->chr['ellipses'] . $this->chr['singleQuoteClose'] . $this->chr['doubleQuoteClose'] . $this->chr['guillemetOpen'] . $this->chr['guillemetClose'] . ']*
                                                                # allowed trailing punctuation
                (?=\Z|\s)                                       # lookahead assertion: followed by end of string or space
            /ux';
		// Revert 4-4 to plain minus-hyphen so as to not mess with ranges of numbers (i.e. pp. 46-50).
		$this->regex['smartMathRevertRange'] = '/
                (
                    (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                    \d+
                )
                [\-' . $this->chr['minus'] . "]
				(
					\d+
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';
		// Revert fractions to basic slash.
		// We'll leave styling fractions to smart_fractions.
		$this->regex['smartMathRevertFraction'] = "/
				(
					(?<=\s|\A|\'|\"|" . $this->chr['noBreakSpace'] . ')
                    \d+
                )
                ' . $this->chr['division'] . "
				(
					\d+
					(?:st|nd|rd|th)?
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';
		// Revert date back to original formats:
		// YYYY-MM-DD.
		$this->regex['smartMathRevertDateYYYY-MM-DD'] = '/
                (
                    (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                    [12][0-9]{3}
                )
                [\-' . $this->chr['minus'] . ']
                (
                    (?:[0]?[1-9]|[1][0-2])
                )
                [\-' . $this->chr['minus'] . "]
				(
					(?:[0]?[1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';
		// MM-DD-YYYY or DD-MM-YYYY.
		$this->regex['smartMathRevertDateMM-DD-YYYY'] = '/
                (?:
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0]?[1-9]|[1][0-2])
                        )
                        [\-' . $this->chr['minus'] . ']
                        (
                            (?:[0]?[1-9]|[12][0-9]|[3][0-1])
                        )
                    )
                    |
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0]?[1-9]|[12][0-9]|[3][0-1])
                        )
                        [\-' . $this->chr['minus'] . ']
                        (
                            (?:[0]?[1-9]|[1][0-2])
                        )
                    )
                )
                [\-' . $this->chr['minus'] . "]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';
		// YYYY-MM or YYYY-DDD next.
		$this->regex['smartMathRevertDateYYYY-MM'] = '/
                (
                    (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                    [12][0-9]{3}
                )
                [\-' . $this->chr['minus'] . "]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';

		// MM/DD/YYYY or DD/MM/YYYY.
		$this->regex['smartMathRevertDateMM/DD/YYYY'] = '/
                (?:
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0][1-9]|[1][0-2])
                        )
                        [\/' . $this->chr['division'] . ']
                        (
                            (?:[0][1-9]|[12][0-9]|[3][0-1])
                        )
                    )
                    |
                    (?:
                        (
                            (?<=\s|\A|' . $this->chr['noBreakSpace'] . ')
                            (?:[0][1-9]|[12][0-9]|[3][0-1])
                        )
                        [\/' . $this->chr['division'] . ']
                        (
                            (?:[0][1-9]|[1][0-2])
                        )
                    )
                )
                [\/' . $this->chr['division'] . "]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . $this->chr['noBreakSpace'] . ')
                )
            /xu';

		// Handle exponents (ie. 4^2).
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
				(?:{$this->chr['singlePrime']}|{$this->chr['doublePrime']})? # handle fractions followed by prime symbols
				(?:\<sup\>(?:st|nd|rd|th)<\/sup\>)?	                         # handle ordinals after fractions
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
			$year_regex[] = "(?: ( $year ) (\s?\/\s?{$this->chr['zeroWidthSpace']}?) ( " . ( $year + 1 ) . ' ) )';
		}
		$this->regex['smartFractionsEscapeYYYY/YYYY'] = "/
			(?<=\A|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']})		# lookbehind assertion: makes sure we are not messing up a url
			(?| " . implode( '|', $year_regex ) . " )
			(
				(?:\Z|\s|{$this->chr['noBreakSpace']}|{$this->chr['noBreakNarrowSpace']}|\.|\!|\?|\)|\;|\:|\'|\")	# makes sure we are not messing up a url
			)
			/xu";

		$this->regex['smartOrdinalSuffix'] = "/\b(\d+)(st|nd|rd|th)\b/"; // End smart math.

		// Smart marks.
		$this->regex['smartMarksEscape501(c)'] = '/\b(501\()(c)(\)\((?:[1-9]|[1-2][0-9])\))/u';

		// Whitespace handling.
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

		// Unit spacing.
		$this->regex['unitSpacingEscapeSpecialChars'] = '#([\[\\\^\$\.\|\?\*\+\(\)\{\}])#';
		$this->update_unit_pattern( isset( $this->data['units'] ) ? $this->data['units'] : array() );

		// French punctuation spacing.
		$this->regex['frenchPunctuationSpacingNarrow']       = '/(\w+)(\s?)([?!»])(\s|\Z)/u';
		$this->regex['frenchPunctuationSpacingFull']         = '/(\w+)(\s?)(:)(\s|\Z)/u';
		$this->regex['frenchPunctuationSpacingSemicolon']    = '/(\w+)(\s?)((?<!&amp|&gt|&lt);)(\s|\Z)/u';
		$this->regex['frenchPunctuationSpacingOpeningQuote'] = '/(\s|\A)(«)(\s?)(\w+)/u';

		// Wrap hard hyphens.
		$this->regex['wrapHardHyphensRemoveEndingSpace'] = "/({$this->components['hyphens']}){$this->chr['zeroWidthSpace']}\$/";

		// Wrap emails.
		$this->regex['wrapEmailsMatchEmails']   = "/{$this->components['wrapEmailsEmailPattern']}/xi";
		$this->regex['wrapEmailsReplaceEmails'] = '/([^a-zA-Z])/';

		// Wrap URLs.
		$this->regex['wrapUrlsPattern']     = "`{$this->components['urlPattern']}`xi";
		$this->regex['wrapUrlsDomainParts'] = '#(\-|\.)#';

		// Style caps.
		$this->regex['styleCaps'] = "/{$this->components['styleCaps']}/xu";

		// Style numbers.
		$this->regex['styleNumbers'] = '/([0-9]+)/u';

		// Style hanging punctuation.
		$this->regex['styleHangingPunctuationDouble'] = "/(\s)([{$this->components['doubleHangingPunctuation']}])(\w+)/u";
		$this->regex['styleHangingPunctuationSingle'] = "/(\s)([{$this->components['singleHangingPunctuation']}])(\w+)/u";
		$this->regex['styleHangingPunctuationInitialDouble'] = "/(?:\A)([{$this->components['doubleHangingPunctuation']}])(\w+)/u";
		$this->regex['styleHangingPunctuationInitialSingle'] = "/(?:\A)([{$this->components['singleHangingPunctuation']}])(\w+)/u";

		// Style ampersands.
		$this->regex['styleAmpersands'] = '/(\&amp\;)/u';

		// Dewidowing.
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

		// Utility patterns for splitting string parameter lists into arrays.
		$this->regex['parameterSplitting'] = '/[\s,]+/';

		// Add the "study" flag to all our regular expressions.
		foreach ( $this->regex as &$regex ) {
			$regex .= 'S';
		}
	}

	/**
	 * Enable lenient parser error handling (HTML is "best guess" if enabled).
	 *
	 * @param bool $on Optional. Default false.
	 */
	function set_ignore_parser_errors( $on = false ) {
		$this->data['ignoreParserErrors'] = $on;
	}

	/**
	 * Enable usage of true "no-break narrow space" (&#8239;) instead of the normal no-break space (&nbsp;).
	 *
	 * @param boolean $on Optional. Default false.
	 */
	function set_true_no_break_narrow_space( $on = false ) {

		if ( $on ) {
			$this->chr['noBreakNarrowSpace'] = uchr( 8239 );
		} else {
			$this->chr['noBreakNarrowSpace'] = uchr( 160 );
		}

		// Update French guillemets.
		$this->quote_styles['doubleGuillemetsFrench'] = array(
			'open'  => $this->chr['guillemetOpen'] . $this->chr['noBreakNarrowSpace'],
			'close' => $this->chr['noBreakNarrowSpace'] . $this->chr['guillemetClose'],
		);
	}

	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_tags_to_ignore( $tags = array( 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ) ) {
		if ( ! is_array( $tags ) ) {
			$tags = preg_split( $this->regex['parameterSplitting'], $tags, -1, PREG_SPLIT_NO_EMPTY );
		}

		// Ensure that we pass only lower-case tag names to XPath.
		$tags = array_filter( array_map( 'strtolower', $tags ), 'ctype_alnum' );

		// Self closing tags shouldn't be in $tags.
		$this->data['ignoreTags'] = array_unique( array_merge( array_diff( $tags, $this->self_closing_tags ), $this->inappropriate_tags ) );
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|array $classes A comma separated list or an array of class names.
	 */
	 function set_classes_to_ignore( $classes = array( 'vcard', 'noTypo' ) ) {
		if ( ! is_array( $classes ) ) {
			$classes = preg_split( $this->regex['parameterSplitting'], $classes, -1, PREG_SPLIT_NO_EMPTY );
		}
		$this->data['ignoreClasses'] = $classes;
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|array $ids A comma separated list or an array of tag names.
	 */
	function set_ids_to_ignore( $ids = array() ) {
		if ( ! is_array( $ids ) ) {
			$ids = preg_split( $this->regex['parameterSplitting'], $ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		$this->data['ignoreIDs'] = $ids;
	}

	/**
	 * Enable/disable typographic quotes.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_quotes( $on = true ) {
		$this->data['smartQuotes'] = $on;
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

			// Update brackets component.
			$this->update_smart_quotes_brackets();
		} else {
			trigger_error( "Invalid quote style $style.", E_USER_WARNING ); // @codingStandardsIgnoreLine.
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

			// Update brackets component.
			$this->update_smart_quotes_brackets();
		} else {
			trigger_error( "Invalid quote style $style.", E_USER_WARNING ); // @codingStandardsIgnoreLine.
		}
	}

	/**
	 * Enable/disable replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_dashes( $on = true ) {
		$this->data['smartDashes'] = $on;
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

			// Update dash spacing regex.
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
			trigger_error( "Invalid dash style $style.", E_USER_WARNING ); // @codingStandardsIgnoreLine.
		}
	}

	/**
	 * Enable/disable replacement of "..." with "…".
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_ellipses( $on = true ) {
		$this->data['smartEllipses'] = $on;
	}

	/**
	 * Enable/disable replacement "creme brulee" with "crème brûlée".
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_diacritics( $on = true ) {
		$this->data['smartDiacritics'] = $on;
	}

	/**
	 * Set the language used for diacritics replacements.
	 *
	 * @param string $lang Has to correspond to a filename in 'diacritics'. Optional. Default 'en-US'.
	 */
	function set_diacritic_language( $lang = 'en-US' ) {
		if ( isset( $this->data['diacriticLanguage'] ) && $this->data['diacriticLanguage'] === $lang ) {
			return;
		}

		$this->data['diacriticLanguage'] = $lang;
		$language_file_name = dirname( __FILE__ ) . '/diacritics/' . $lang . '.json';

		if ( file_exists( $language_file_name ) ) {
			$diacritics_file = json_decode( file_get_contents( $language_file_name ), true );
			$this->data['diacriticWords'] = $diacritics_file['diacritic_words'];
		} else {
			unset( $this->data['diacriticWords'] );
		}

		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Set up custom diacritics replacements.
	 *
	 * @param string|array $custom_replacements An array formatted array(needle=>replacement, needle=>replacement...),
	 *                                          or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 */
	function set_diacritic_custom_replacements( $custom_replacements = array() ) {
		if ( ! is_array( $custom_replacements ) ) {
			$custom_replacements = preg_split( '/,/', $custom_replacements, -1, PREG_SPLIT_NO_EMPTY );
		}

		$replacements = array();
		foreach ( $custom_replacements as $custom_key => $custom_replacement ) {
			// Account for single and double quotes.
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

		$this->data['diacriticCustomReplacements'] = $replacements;
		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Update the pattern and replacement arrays in $settings['diacriticReplacement'].
	 *
	 * Should be called whenever a new diacritics replacement language is selected or
	 * when the custom replacements are updated.
	 */
	private function update_diacritics_replacement_arrays() {
		$patterns = array();
		$replacements = array();

		if ( ! empty( $this->data['diacriticCustomReplacements'] ) ) {
			foreach ( $this->data['diacriticCustomReplacements'] as $needle => $replacement ) {
				$patterns[] = "/\b$needle\b/u";
				$replacements[ $needle ] = $replacement;
			}
		}
		if ( ! empty( $this->data['diacriticWords'] ) ) {
	 		foreach ( $this->data['diacriticWords'] as $needle => $replacement ) {
				$patterns[] = "/\b$needle\b/u";
				$replacements[ $needle ] = $replacement;
	 		}
		}

		$this->data['diacriticReplacement'] = array( 'patterns' => $patterns, 'replacements' => $replacements );
	}

	/**
	 * Enable/disable replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_marks( $on = true ) {
		$this->data['smartMarks'] = $on;
	}

	/**
	 * Enable/disable proper mathematical symbols.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_math( $on = true ) {
		$this->data['smartMath'] = $on;
	}

	/**
	 * Enable/disable replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_exponents( $on = true ) {
		$this->data['smartExponents'] = $on;
	}

	/**
	 * Enable/disable replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_fractions( $on = true ) {
		$this->data['smartFractions'] = $on;
	}

	/**
	 * Enable/disable replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_smart_ordinal_suffix( $on = true ) {
		$this->data['smartOrdinalSuffix'] = $on;
	}

	/**
	 * Enable/disable forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_single_character_word_spacing( $on = true ) {
		$this->data['singleCharacterWordSpacing'] = $on;
	}

	/**
	 * Enable/disable fraction spacing.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_fraction_spacing( $on = true ) {
		$this->data['fractionSpacing'] = $on;
	}

	/**
	 * Enable/disable keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_unit_spacing( $on = true ) {
		$this->data['unitSpacing'] = $on;
	}

	/**
	 * Enable/disable extra whitespace before certain punction marks, as is the French custom.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_french_punctuation_spacing( $on = true ) {
		$this->data['frenchPunctuationSpacing'] = $on;
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

		$this->data['units'] = $units;
		$this->update_unit_pattern( $units );
	}

	/**
	 * Update components and pattern for matching both standard and custom units.
	 *
	 * @param array $units An array of unit names.
	 */
	private function update_unit_pattern( array $units ) {
		// Update components & regex pattern.
		foreach ( $units as $index => $unit ) {
			// Escape special chars.
			$units[ $index ] = preg_replace( $this->regex['unitSpacingEscapeSpecialChars'], '\\\\$1', $unit );
		}
		$custom_units = implode( '|', $units );
		$custom_units .= ( $custom_units ) ? '|' : '';
		$this->components['unitSpacingUnits'] = $custom_units . $this->components['unitSpacingStandardUnits'];
		$this->regex['unitSpacingUnitPattern'] = "/(\d\.?)\s({$this->components['unitSpacingUnits']})\b/x";
	}

	/**
	 * Enable/disable wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_dash_spacing( $on = true ) {
		$this->data['dashSpacing'] = $on;
	}

	/**
	 * Enable/disable removal of extra whitespace characters.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_space_collapse( $on = true ) {
		$this->data['spaceCollapse'] = $on;
	}

	/**
	 * Enable/disable widow handling.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_dewidow( $on = true ) {
		$this->data['dewidow'] = $on;
	}

	/**
	 * Set the maximum length of widows that will be protected.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_length( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['dewidowMaxLength'] = $length;
	}

	/**
	 * Set the maximum length of pulled text to keep widows company.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_max_dewidow_pull( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['dewidowMaxPull'] = $length;
	}

	/**
	 * Enable/disable wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_wrap_hard_hyphens( $on = true ) {
		$this->data['hyphenHardWrap'] = $on;
	}

	/**
	 * Enable/disable wrapping of urls.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_url_wrap( $on = true ) {
		$this->data['urlWrap'] = $on;
	}

	/**
	 * Enable/disable wrapping of email addresses.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_email_wrap( $on = true ) {
		$this->data['emailWrap'] = $on;
	}

	/**
	 * Set the minimum character requirement after an URL wrapping point.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_url_wrap( $length = 5 ) {
		$length = ( $length > 0 ) ? $length : 5;

		$this->data['urlMinAfterWrap'] = $length;
	}

	/**
	 * Enable/disable wrapping of ampersands in <span class="amp">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_ampersands( $on = true ) {
		$this->data['styleAmpersands'] = $on;
	}

	/**
	 * Enable/disable wrapping caps in <span class="caps">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_caps( $on = true ) {
		$this->data['styleCaps'] = $on;
	}

	/**
	 * Enable/disable wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_initial_quotes( $on = true ) {
		$this->data['styleInitialQuotes'] = $on;
	}

	/**
	 * Enable/disable wrapping of numbers in <span class="numbers">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_numbers( $on = true ) {
		$this->data['styleNumbers'] = $on;
	}

	/**
	 * Enable/disable wrapping of punctiation and wide characters in <span class="pull-*">.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_style_hanging_punctuation( $on = true ) {
		$this->data['styleHangingPunctuation'] = $on;
	}

	/**
	 * Set the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	function set_initial_quote_tags( $tags = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ) ) {
		// Make array if handed a list of tags as a string.
		if ( ! is_array( $tags ) ) {
			$tags = preg_split( '/[^a-z0-9]+/', $tags, -1, PREG_SPLIT_NO_EMPTY );
		}

		// Store the tag array inverted (with the tagName as its index for faster lookup).
		$this->data['initialQuoteTags'] = array_change_key_case( array_flip( $tags ), CASE_LOWER );
	}

	/**
	 * Enable/disable hyphenation.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenation( $on = true ) {
		$this->data['hyphenation'] = $on;
	}

	/**
	 * Set the hyphenation pattern language.
	 *
	 * @param string $lang Has to correspond to a filename in 'lang'. Optional. Default 'en-US'.
	 */
	function set_hyphenation_language( $lang = 'en-US' ) {
		if ( isset( $this->data['hyphenLanguage'] ) && $this->data['hyphenLanguage'] === $lang ) {
			return; // Bail out, no need to do anything.
		}

		$this->data['hyphenLanguage'] = $lang;
	}

	/**
	 * Set the minimum length of a word that may be hyphenated.
	 *
	 * @param number $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	function set_min_length_hyphenation( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['hyphenMinLength'] = $length;
	}

	/**
	 * Set the minimum character requirement before a hyphenation point.
	 *
	 * @param number $length Defaults to 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_before_hyphenation( $length = 3 ) {
		$length = ( $length > 0 ) ? $length : 3;

		$this->data['hyphenMinBefore'] = $length;
	}

	/**
	 * Set the minimum character requirement after a hyphenation point.
	 *
	 * @param number $length Defaults to 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	function set_min_after_hyphenation( $length = 2 ) {
		$length = ( $length > 0 ) ? $length : 2;

		$this->data['hyphenMinAfter'] = $length;
	}

	/**
	 * Enable/disable hyphenation of titles and headings.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_headings( $on = true ) {
		$this->data['hyphenateTitle'] = $on;
	}

	/**
	 * Enable/disable hyphenation of words set completely in capital letters.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_all_caps( $on = true ) {
		$this->data['hyphenateAllCaps'] = $on;
	}

	/**
	 * Enable/disable hyphenation of words starting with a capital letter.
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_title_case( $on = true ) {
		$this->data['hyphenateTitleCase'] = $on;
	}

	/**
	 * Enable/disable hyphenation of compound words (e.g. "editor-in-chief").
	 *
	 * @param boolean $on Optional. Default true.
	 */
	function set_hyphenate_compounds( $on = true ) {
		$this->data['hyphenateCompounds'] = $on;
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

		$this->data['hyphenationCustomExceptions'] = $exceptions;
	}

	/**
	 * Retrieve a unique hash value for the current settings.
	 *
	 * @param number $max_length The maximum number of bytes returned. Optional. Default 16.
	 *
	 * @return string A binary hash value for the current settings limited to $max_length.
	 */
	public function get_hash( $max_length = 16 ) {
		$hash = md5( json_encode( $this->data ), true );

		if ( $max_length < strlen( $hash ) ) {
			$hash = substr( $hash, 0, $max_length );
		}

		return $hash;
	}
}
