<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 *  ***
 *
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;

/**
 * Store settings for the PHP_Typography class.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 4.0.0
 */
class Settings implements \ArrayAccess, \JsonSerializable {

	/**
	 * The current no-break narrow space character.
	 *
	 * @var string
	 */
	protected $no_break_narrow_space;

	/**
	 * Primary quote style.
	 *
	 * @var Settings\Quotes
	 */
	protected $primary_quote_style;

	/**
	 * Secondary quote style.
	 *
	 * @var Settings\Quotes
	 */
	protected $secondary_quote_style;

	/**
	 * A regex pattern for custom units (or the empty string).
	 *
	 * @var string
	 */
	protected $custom_units = '';

	/**
	 * A hashmap of settings for the various typographic options.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * An array containing all self-closing HTML5 tags.
	 *
	 * @deprecated 5.2.0 Not used anymore, will be removed in 6.0.0.
	 *
	 * @var array
	 */
	protected $self_closing_tags = [];

	/**
	 * A array of tags we should never touch.
	 *
	 * @deprecated 5.2.0 Use DOM::inappropriate_tags() if necessary. Will be removed in 6.0.0.
	 *
	 * @var array
	 */
	protected $inappropriate_tags = [];

	/**
	 * The current dash style.
	 *
	 * @var Settings\Dashes
	 */
	protected $dash_style;

	/**
	 * Sets up a new Settings object.
	 *
	 * @param bool $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	public function __construct( $set_defaults = true ) {
		$this->init( $set_defaults );
	}

	/**
	 * Provides access to named settings (object syntax).
	 *
	 * @param string $key The settings key.
	 *
	 * @return mixed
	 */
	public function &__get( $key ) {
		return $this->data[ $key ];
	}

	/**
	 * Changes a named setting (object syntax).
	 *
	 * @param string $key   The settings key.
	 * @param mixed  $value The settings value.
	 */
	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Checks if a named setting exists (object syntax).
	 *
	 * @param string $key The settings key.
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Unsets a named setting.
	 *
	 * @param string $key The settings key.
	 */
	public function __unset( $key ) {
		unset( $this->data[ $key ] );
	}

	/**
	 * Changes a named setting (array syntax).
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
	 * Checks if a named setting exists (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	/**
	 * Unsets a named setting (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}

	/**
	 * Provides access to named settings (array syntax).
	 *
	 * @param string $offset The settings key.
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
	}

	/**
	 * Provides a JSON serialization of the settings.
	 *
	 * @return mixed
	 */
	public function jsonSerialize() {
		return array_merge(
			$this->data,
			[
				'no_break_narrow_space'  => $this->no_break_narrow_space,
				'primary_quotes'         => "{$this->primary_quote_style->open()}|{$this->primary_quote_style->close()}",
				'secondary_quotes'       => "{$this->secondary_quote_style->open()}|{$this->secondary_quote_style->close()}",
				'dash_style'             => "{$this->dash_style->interval_dash()}|{$this->dash_style->interval_space()}|{$this->dash_style->parenthetical_dash()}|{$this->dash_style->parenthetical_space()}",
				'custom_units'           => $this->custom_units,
			]
		);
	}

	/**
	 * Retrieves the current non-breaking narrow space character (either the
	 * regular non-breaking space &nbsp; or the the true non-breaking narrow space &#8239;).
	 *
	 * @return string
	 */
	public function no_break_narrow_space() {
		return $this->no_break_narrow_space;
	}

	/**
	 * Retrieves the primary (double) quote style.
	 *
	 * @return Settings\Quotes
	 */
	public function primary_quote_style() {
		return $this->primary_quote_style;
	}

	/**
	 * Retrieves the secondary (single) quote style.
	 *
	 * @return Settings\Quotes
	 */
	public function secondary_quote_style() {
		return $this->secondary_quote_style;
	}

	/**
	 * Retrieves the dash style.
	 *
	 * @return Settings\Dashes
	 */
	public function dash_style() {
		return $this->dash_style;
	}

	/**
	 * Retrieves the custom units pattern.
	 *
	 * @return string The pattern is suitable for inclusion into a regular expression.
	 */
	public function custom_units() {
		return $this->custom_units;
	}

	/**
	 * Initialize the PHP_Typography object.
	 *
	 * @param bool $set_defaults If true, set default values for various properties. Defaults to true.
	 */
	private function init( $set_defaults = true ) {
		$this->no_break_narrow_space = U::NO_BREAK_SPACE;  // used in unit spacing - can be changed to 8239 via set_true_no_break_narrow_space.

		$this->dash_style = new Settings\Simple_Dashes( U::EM_DASH, U::THIN_SPACE, U::EN_DASH, U::THIN_SPACE );

		$this->primary_quote_style   = new Settings\Simple_Quotes( U::DOUBLE_QUOTE_OPEN, U::DOUBLE_QUOTE_CLOSE );
		$this->secondary_quote_style = new Settings\Simple_Quotes( U::SINGLE_QUOTE_OPEN, U::SINGLE_QUOTE_CLOSE );

		// Set up some arrays for quick HTML5 introspection.
		$this->self_closing_tags  = array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ), function( $tag ) {
				return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::VOID_TAG );
			} );
		$this->inappropriate_tags = array_flip( DOM::inappropriate_tags() );

		if ( $set_defaults ) {
			$this->set_defaults();
		}
	}

	/**
	 * (Re)set various options to their default values.
	 */
	public function set_defaults() {
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
		$this->set_dewidow_word_number();
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
	 * Enable lenient parser error handling (HTML is "best guess" if enabled).
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_ignore_parser_errors( $on = false ) {
		$this->data['parserErrorsIgnore'] = $on;
	}

	/**
	 * Sets an optional handler for parser errors. Invalid callbacks will be silently ignored.
	 *
	 * @param callable|null $handler Optional. A callable that takes an array of error strings as its parameter. Default null.
	 */
	public function set_parser_errors_handler( $handler = null ) {
		if ( ! empty( $handler ) && ! is_callable( $handler ) ) {
			return; // Invalid handler, abort.
		}

		$this->data['parserErrorsHandler'] = $handler;
	}

	/**
	 * Enable usage of true "no-break narrow space" (&#8239;) instead of the normal no-break space (&nbsp;).
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_true_no_break_narrow_space( $on = false ) {

		if ( $on ) {
			$this->no_break_narrow_space = U::NO_BREAK_NARROW_SPACE;
		} else {
			$this->no_break_narrow_space = U::NO_BREAK_SPACE;
		}
	}

	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	public function set_tags_to_ignore( $tags = [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ] ) {
		// Ensure that we pass only lower-case tag names to XPath.
		$tags = array_filter( array_map( 'strtolower', Strings::maybe_split_parameters( $tags ) ), 'ctype_alnum' );

		$this->data['ignoreTags'] = array_unique( array_merge( $tags, $this->inappropriate_tags ) );
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|array $classes A comma separated list or an array of class names.
	 */
	public function set_classes_to_ignore( $classes = [ 'vcard', 'noTypo' ] ) {
		$this->data['ignoreClasses'] = Strings::maybe_split_parameters( $classes );
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|array $ids A comma separated list or an array of tag names.
	 */
	public function set_ids_to_ignore( $ids = [] ) {
		$this->data['ignoreIDs'] = Strings::maybe_split_parameters( $ids );
	}

	/**
	 * Enables/disables typographic quotes.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_quotes( $on = true ) {
		$this->data['smartQuotes'] = $on;
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
	 * @param string $style Defaults to 'doubleCurled.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_quotes_primary( $style = Quote_Style::DOUBLE_CURLED ) {
		$this->primary_quote_style = $this->get_quote_style( $style );
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
	 * @param string $style Defaults to 'singleCurled'.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_quotes_secondary( $style = Quote_Style::SINGLE_CURLED ) {
		$this->secondary_quote_style = $this->get_quote_style( $style );
	}

	/**
	 * Retrieves a Quotes instance from a given style.
	 *
	 * @param  Settings\Quotes|string $style A Quotes instance or a quote style constant.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 *
	 * @return Settings\Quotes
	 */
	protected function get_quote_style( $style ) {
		return $this->get_style( $style, Settings\Quotes::class, [ Quote_Style::class, 'get_styled_quotes' ], 'quote' );
	}

	/**
	 * Retrieves an object from a given style.
	 *
	 * @param  object|string $style          A style object instance or a style constant.
	 * @param  string        $expected_class A class name.
	 * @param  callable      $get_style      A function that returns a style object from a given style constant.
	 * @param  string        $description    Style description for the exception message.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 *
	 * @return object An instance of $expected_class.
	 */
	protected function get_style( $style, $expected_class, callable $get_style, $description ) {
		if ( $style instanceof $expected_class ) {
			$object = $style;
		} else {
			$object = $get_style( $style, $this );
		}

		if ( ! $object instanceof $expected_class ) {
			throw new \DomainException( "Invalid $description style $style." );
		}

		return $object;
	}

	/**
	 * Enables/disables replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_dashes( $on = true ) {
		$this->data['smartDashes'] = $on;
	}

	/**
	 * Sets the typographical conventions used by smart_dashes.
	 *
	 * Allowed values for $style:
	 * - "traditionalUS"
	 * - "international"
	 *
	 * @param string|Settings\Dashes $style Optional. Default Dash_Style::TRADITIONAL_US.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_dashes_style( $style = Dash_Style::TRADITIONAL_US ) {
		$this->dash_style = $this->get_style( $style, Settings\Dashes::class, [ Dash_Style::class, 'get_styled_dashes' ], 'dash' );
	}

	/**
	 * Enables/disables replacement of "..." with "…".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ellipses( $on = true ) {
		$this->data['smartEllipses'] = $on;
	}

	/**
	 * Enables/disables replacement "creme brulee" with "crème brûlée".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_diacritics( $on = true ) {
		$this->data['smartDiacritics'] = $on;
	}

	/**
	 * Sets the language used for diacritics replacements.
	 *
	 * @param string $lang Has to correspond to a filename in 'diacritics'. Optional. Default 'en-US'.
	 */
	public function set_diacritic_language( $lang = 'en-US' ) {
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
	 * Sets up custom diacritics replacements.
	 *
	 * @param string|array $custom_replacements An array formatted [needle=>replacement, needle=>replacement...],
	 *                                          or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 */
	public function set_diacritic_custom_replacements( $custom_replacements = [] ) {
		if ( ! is_array( $custom_replacements ) ) {
			$custom_replacements = $this->parse_diacritics_replacement_string( $custom_replacements );
		}

		$this->data['diacriticCustomReplacements'] = Arrays::array_map_assoc( function( $key, $replacement ) {
			$key         = strip_tags( trim( $key ) );
			$replacement = strip_tags( trim( $replacement ) );

			if ( ! empty( $key ) && ! empty( $replacement ) ) {
				return [ $key, $replacement ];
			} else {
				return [];
			}
		}, $custom_replacements );

		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Parses a custom diacritics replacement string into an array.
	 *
	 * @param string $custom_replacements A string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 *
	 * @return array
	 */
	private function parse_diacritics_replacement_string( $custom_replacements ) {
		return Arrays::array_map_assoc( function( $key, $replacement ) {

			// Account for single and double quotes in keys ...
			if ( preg_match( '/("|\')((?:(?!\1).)+)(?:\1\s*=>)/', $replacement, $match ) ) {
				$key = $match[2];
			}

			// ... and values.
			if ( preg_match( '/(?:=>\s*("|\'))((?:(?!\1).)+)(?:\1)/', $replacement, $match ) ) {
				$replacement = $match[2];
			}

			return [ $key, $replacement ];
		}, preg_split( '/,/', $custom_replacements, -1, PREG_SPLIT_NO_EMPTY ) );
	}

	/**
	 * Update the pattern and replacement arrays in $settings['diacriticReplacement'].
	 *
	 * Should be called whenever a new diacritics replacement language is selected or
	 * when the custom replacements are updated.
	 */
	private function update_diacritics_replacement_arrays() {
		$patterns = [];
		$replacements = [];

		if ( ! empty( $this->data['diacriticCustomReplacements'] ) ) {
			$this->parse_diacritics_rules( $this->data['diacriticCustomReplacements'], $patterns, $replacements );
		}
		if ( ! empty( $this->data['diacriticWords'] ) ) {
			$this->parse_diacritics_rules( $this->data['diacriticWords'], $patterns, $replacements );
		}

		$this->data['diacriticReplacement'] = [
			'patterns'     => $patterns,
			'replacements' => $replacements,
		];
	}

	/**
	 * Parse an array of diacritics rules.
	 *
	 * @param array $diacritics_rules The rules ( $word => $replacement ).
	 * @param array $patterns         Resulting patterns. Passed by reference.
	 * @param array $replacements     Resulting replacements. Passed by reference.
	 */
	private function parse_diacritics_rules( array $diacritics_rules, array &$patterns, array &$replacements ) {

		foreach ( $diacritics_rules as $needle => $replacement ) {
			$patterns[] = '/\b(?<!\w[' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . '])' . $needle . '\b(?![' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . ']\w)/u';
			$replacements[ $needle ] = $replacement;
		}
	}

	/**
	 * Enables/disables replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_marks( $on = true ) {
		$this->data['smartMarks'] = $on;
	}

	/**
	 * Enables/disables proper mathematical symbols.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_math( $on = true ) {
		$this->data['smartMath'] = $on;
	}

	/**
	 * Enables/disables replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_exponents( $on = true ) {
		$this->data['smartExponents'] = $on;
	}

	/**
	 * Enables/disables replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_fractions( $on = true ) {
		$this->data['smartFractions'] = $on;
	}

	/**
	 * Enables/disables replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ordinal_suffix( $on = true ) {
		$this->data['smartOrdinalSuffix'] = $on;
	}

	/**
	 * Enables/disables forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_single_character_word_spacing( $on = true ) {
		$this->data['singleCharacterWordSpacing'] = $on;
	}

	/**
	 * Enables/disables fraction spacing.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_fraction_spacing( $on = true ) {
		$this->data['fractionSpacing'] = $on;
	}

	/**
	 * Enables/disables keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_unit_spacing( $on = true ) {
		$this->data['unitSpacing'] = $on;
	}

	/**
	 * Enables/disables numbered abbreviations like "ISO 9000" together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_numbered_abbreviation_spacing( $on = true ) {
		$this->data['numberedAbbreviationSpacing'] = $on;
	}

	/**
	 * Enables/disables extra whitespace before certain punction marks, as is the French custom.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_french_punctuation_spacing( $on = true ) {
		$this->data['frenchPunctuationSpacing'] = $on;
	}

	/**
	 * Sets the list of units to keep together with their values.
	 *
	 * @param string|array $units A comma separated list or an array of units.
	 */
	public function set_units( $units = [] ) {
		$this->data['units'] = Strings::maybe_split_parameters( $units );
		$this->update_unit_pattern( $this->data['units'] );
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
			$units[ $index ] = preg_replace( '#([\[\\\^\$\.\|\?\*\+\(\)\{\}])#', '\\\\$1', $unit );
		}
		$this->custom_units = implode( '|', $units );
		$this->custom_units .= ( $this->custom_units ) ? '|' : '';
	}

	/**
	 * Enables/disables wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dash_spacing( $on = true ) {
		$this->data['dashSpacing'] = $on;
	}

	/**
	 * Enables/disables removal of extra whitespace characters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_space_collapse( $on = true ) {
		$this->data['spaceCollapse'] = $on;
	}

	/**
	 * Enables/disables widow handling.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dewidow( $on = true ) {
		$this->data['dewidow'] = $on;
	}

	/**
	 * Sets the maximum length of widows that will be protected.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_max_dewidow_length( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['dewidowMaxLength'] = $length;
	}

	/**
	 * Sets the maximum number of words considered for dewidowing.
	 *
	 * @param int $number Defaults to 1. Only 1, 2 and 3 are valid.
	 */
	public function set_dewidow_word_number( $number = 1 ) {
		$number = ( $number > 3 || $number < 1 ) ? 1 : $number;

		$this->data['dewidowWordNumber'] = $number;
	}

	/**
	 * Sets the maximum length of pulled text to keep widows company.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_max_dewidow_pull( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['dewidowMaxPull'] = $length;
	}

	/**
	 * Enables/disables wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_wrap_hard_hyphens( $on = true ) {
		$this->data['hyphenHardWrap'] = $on;
	}

	/**
	 * Enables/disables wrapping of urls.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_url_wrap( $on = true ) {
		$this->data['urlWrap'] = $on;
	}

	/**
	 * Enables/disables wrapping of email addresses.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_email_wrap( $on = true ) {
		$this->data['emailWrap'] = $on;
	}

	/**
	 * Sets the minimum character requirement after an URL wrapping point.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_url_wrap( $length = 5 ) {
		$length = ( $length > 0 ) ? $length : 5;

		$this->data['urlMinAfterWrap'] = $length;
	}

	/**
	 * Enables/disables wrapping of ampersands in <span class="amp">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_ampersands( $on = true ) {
		$this->data['styleAmpersands'] = $on;
	}

	/**
	 * Enables/disables wrapping caps in <span class="caps">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_caps( $on = true ) {
		$this->data['styleCaps'] = $on;
	}

	/**
	 * Enables/disables wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_initial_quotes( $on = true ) {
		$this->data['styleInitialQuotes'] = $on;
	}

	/**
	 * Enables/disables wrapping of numbers in <span class="numbers">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_numbers( $on = true ) {
		$this->data['styleNumbers'] = $on;
	}

	/**
	 * Enables/disables wrapping of punctiation and wide characters in <span class="pull-*">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_hanging_punctuation( $on = true ) {
		$this->data['styleHangingPunctuation'] = $on;
	}

	/**
	 * Sets the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|array $tags A comma separated list or an array of tag names.
	 */
	public function set_initial_quote_tags( $tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ] ) {
		// Make array if handed a list of tags as a string.
		if ( ! is_array( $tags ) ) {
			$tags = preg_split( '/[^a-z0-9]+/', $tags, -1, PREG_SPLIT_NO_EMPTY );
		}

		// Store the tag array inverted (with the tagName as its index for faster lookup).
		$this->data['initialQuoteTags'] = array_change_key_case( array_flip( $tags ), CASE_LOWER );
	}

	/**
	 * Enables/disables hyphenation.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenation( $on = true ) {
		$this->data['hyphenation'] = $on;
	}

	/**
	 * Sets the hyphenation pattern language.
	 *
	 * @param string $lang Has to correspond to a filename in 'lang'. Optional. Default 'en-US'.
	 */
	public function set_hyphenation_language( $lang = 'en-US' ) {
		if ( isset( $this->data['hyphenLanguage'] ) && $this->data['hyphenLanguage'] === $lang ) {
			return; // Bail out, no need to do anything.
		}

		$this->data['hyphenLanguage'] = $lang;
	}

	/**
	 * Sets the minimum length of a word that may be hyphenated.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_min_length_hyphenation( $length = 5 ) {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data['hyphenMinLength'] = $length;
	}

	/**
	 * Sets the minimum character requirement before a hyphenation point.
	 *
	 * @param int $length Defaults to 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_before_hyphenation( $length = 3 ) {
		$length = ( $length > 0 ) ? $length : 3;

		$this->data['hyphenMinBefore'] = $length;
	}

	/**
	 * Sets the minimum character requirement after a hyphenation point.
	 *
	 * @param int $length Defaults to 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_hyphenation( $length = 2 ) {
		$length = ( $length > 0 ) ? $length : 2;

		$this->data['hyphenMinAfter'] = $length;
	}

	/**
	 * Enables/disables hyphenation of titles and headings.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_headings( $on = true ) {
		$this->data['hyphenateTitle'] = $on;
	}

	/**
	 * Enables/disables hyphenation of words set completely in capital letters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_all_caps( $on = true ) {
		$this->data['hyphenateAllCaps'] = $on;
	}

	/**
	 * Enables/disables hyphenation of words starting with a capital letter.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_title_case( $on = true ) {
		$this->data['hyphenateTitleCase'] = $on;
	}

	/**
	 * Enables/disables hyphenation of compound words (e.g. "editor-in-chief").
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_compounds( $on = true ) {
		$this->data['hyphenateCompounds'] = $on;
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param string|array $exceptions An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
	 *        In the latter case, only alphanumeric characters and hyphens are recognized. The default is empty.
	 */
	public function set_hyphenation_exceptions( $exceptions = [] ) {
		$this->data['hyphenationCustomExceptions'] = Strings::maybe_split_parameters( $exceptions );
	}

	/**
	 * Retrieves a unique hash value for the current settings.
	 *
	 * @since 5.2.0 The new parameter $raw_output has been added.
	 *
	 * @param int  $max_length Optional. The maximum number of bytes returned (0 for unlimited). Default 16.
	 * @param bool $raw_output Optional. Wether to return raw binary data for the hash. Default true.
	 *
	 * @return string A binary hash value for the current settings limited to $max_length.
	 */
	public function get_hash( $max_length = 16, $raw_output = true ) {
		$hash = md5( json_encode( $this ), $raw_output );

		if ( $max_length < strlen( $hash ) && $max_length > 0 ) {
			$hash = substr( $hash, 0, $max_length );
		}

		return $hash;
	}
}
