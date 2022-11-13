<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2022 Peter Putzer.
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

use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;
use PHP_Typography\Settings\Quotes;

/**
 * Store settings for the PHP_Typography class.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 4.0.0
 * @since 6.5.0 The protected property $no_break_narrow_space has been deprecated.
 *
 * @implements \ArrayAccess<string,mixed>
 */
class Settings implements \ArrayAccess, \JsonSerializable {

	// General attributes.
	const IGNORE_TAGS    = 'ignoreTags';
	const IGNORE_CLASSES = 'ignoreClasses';
	const IGNORE_IDS     = 'ignoreIDs';

	// Smart characters.
	const SMART_QUOTES                        = 'smartQuotes';
	const SMART_QUOTES_EXCEPTIONS             = 'smartQuotesExceptions';
	const SMART_DASHES                        = 'smartDashes';
	const SMART_ELLIPSES                      = 'smartEllipses';
	const SMART_DIACRITICS                    = 'smartDiacritics';
	const DIACRITIC_LANGUAGE                  = 'diacriticLanguage';
	const DIACRITIC_WORDS                     = 'diacriticWords';
	const DIACRITIC_REPLACEMENT_DATA          = 'diacriticReplacement';
	const DIACRITIC_CUSTOM_REPLACEMENTS       = 'diacriticCustomReplacements';
	const SMART_MARKS                         = 'smartMarks';
	const SMART_ORDINAL_SUFFIX                = 'smartOrdinalSuffix';
	const SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS = 'smartOrdinalSuffixRomanNumerals';
	const SMART_MATH                          = 'smartMath';
	const SMART_FRACTIONS                     = 'smartFractions';
	const SMART_EXPONENTS                     = 'smartExponents';
	const SMART_AREA_UNITS                    = 'smartAreaVolumeUnits';

	// Smart spacing.
	const SINGLE_CHARACTER_WORD_SPACING = 'singleCharacterWordSpacing';
	const FRACTION_SPACING              = 'fractionSpacing';
	const UNIT_SPACING                  = 'unitSpacing';
	const UNITS                         = 'units';
	const NUMBERED_ABBREVIATION_SPACING = 'numberedAbbreviationSpacing';
	const FRENCH_PUNCTUATION_SPACING    = 'frenchPunctuationSpacing';
	const DASH_SPACING                  = 'dashSpacing';
	const DEWIDOW                       = 'dewidow';
	const DEWIDOW_MAX_LENGTH            = 'dewidowMaxLength';
	const DEWIDOW_MAX_PULL              = 'dewidowMaxPull';
	const DEWIDOW_WORD_NUMBER           = 'dewidowWordNumber';
	const HYPHEN_HARD_WRAP              = 'hyphenHardWrap';
	const URL_WRAP                      = 'urlWrap';
	const URL_MIN_AFTER_WRAP            = 'urlMinAfterWrap';
	const EMAIL_WRAP                    = 'emailWrap';
	const SPACE_COLLAPSE                = 'spaceCollapse';

	// Character styling.
	const STYLE_AMPERSANDS          = 'styleAmpersands';
	const STYLE_CAPS                = 'styleCaps';
	const STYLE_INITIAL_QUOTES      = 'styleInitialQuotes';
	const INITIAL_QUOTE_TAGS        = 'initialQuoteTags';
	const STYLE_NUMBERS             = 'styleNumbers';
	const STYLE_HANGING_PUNCTUATION = 'styleHangingPunctuation';

	// Hyphenation.
	const HYPHENATION                   = 'hyphenation';
	const HYPHENATION_LANGUAGE          = 'hyphenLanguage';
	const HYPHENATION_MIN_LENGTH        = 'hyphenMinLength';
	const HYPHENATION_MIN_BEFORE        = 'hyphenMinBefore';
	const HYPHENATION_MIN_AFTER         = 'hyphenMinAfter';
	const HYPHENATION_CUSTOM_EXCEPTIONS = 'hyphenationCustomExceptions';
	const HYPHENATE_HEADINGS            = 'hyphenateTitle';
	const HYPHENATE_ALL_CAPS            = 'hyphenateAllCaps';
	const HYPHENATE_TITLE_CASE          = 'hyphenateTitleCase';
	const HYPHENATE_COMPOUNDS           = 'hyphenateCompounds';

	// Parser error handling.
	const PARSER_ERRORS_IGNORE  = 'parserErrorsIgnore';
	const PARSER_ERRORS_HANDLER = 'parserErrorsHandler';

	/**
	 * The current no-break narrow space character.
	 *
	 * @deprecated 6.5.0
	 *
	 * @var string
	 */
	protected $no_break_narrow_space;

	/**
	 * Primary quote style.
	 *
	 * @var Quotes
	 */
	protected $primary_quote_style;

	/**
	 * Secondary quote style.
	 *
	 * @var Quotes
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
	 * @var mixed[]
	 */
	protected $data = [];

	/**
	 * The current dash style.
	 *
	 * @var Settings\Dashes
	 */
	protected $dash_style;

	/**
	 * The Unicode character mapping (some characters still have compatibility issues).
	 *
	 * @since 6.5.0
	 *
	 * @var string[]
	 */
	protected $unicode_mapping;

	/**
	 * An array containing just remapped characters (for optimization).
	 *
	 * @since 6.5.0
	 *
	 * @var string[]
	 */
	protected $remapped_characters;

	/**
	 * Sets up a new Settings object.
	 *
	 * @since 6.0.0 If $set_defaults is `false`, the settings object is not fully
	 *              initialized unless `set_smart_quotes_primary`,
	 *              `set_smart_quotes_secondary`, `set_smart_dashes_style` and
	 *              `set_true_no_break_narrow_space` are called explicitly.
	 * @since 6.5.0 A (partial) character mapping can be given to remap certain
	 *              characters.
	 *
	 * @param bool     $set_defaults Optional. If true, set default values for various properties. Default true.
	 * @param string[] $mapping      Optional. Unicode characters to remap. The default maps the narrow no-break space to the normal NO-BREAK SPACE and the apostrophe to the RIGHT SINGLE QUOTATION MARK.
	 */
	public function __construct( $set_defaults = true, array $mapping = [ U::NO_BREAK_NARROW_SPACE => U::NO_BREAK_SPACE, U::APOSTROPHE => U::SINGLE_QUOTE_CLOSE ] ) { // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
		if ( $set_defaults ) {
			$this->set_defaults();
		}

		// Merge default character mapping with given mapping.
		$this->unicode_mapping = $mapping;

		// Keep backwards compatibility.
		if ( isset( $this->unicode_mapping[ U::NO_BREAK_NARROW_SPACE ] ) ) {
			$this->no_break_narrow_space = $this->unicode_mapping[ U::NO_BREAK_NARROW_SPACE ];
		}
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
	public function __set( $key, $value ) : void {
		$this->data[ $key ] = $value;
	}

	/**
	 * Checks if a named setting exists (object syntax).
	 *
	 * @param string $key The settings key.
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Unsets a named setting.
	 *
	 * @param string $key The settings key.
	 */
	public function __unset( $key ) : void {
		unset( $this->data[ $key ] );
	}

	/**
	 * Changes a named setting (array syntax).
	 *
	 * @param string $offset The settings key.
	 * @param mixed  $value  The settings value.
	 */
	public function offsetSet( $offset, $value ) : void {
		if ( ! empty( $offset ) ) {
			$this->data[ $offset ] = $value;
		}
	}

	/**
	 * Checks if a named setting exists (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetExists( $offset ) : bool {
		return isset( $this->data[ $offset ] );
	}

	/**
	 * Unsets a named setting (array syntax).
	 *
	 * @param string $offset The settings key.
	 */
	public function offsetUnset( $offset ) : void {
		unset( $this->data[ $offset ] );
	}

	/**
	 * Provides access to named settings (array syntax).
	 *
	 * @param string $offset The settings key.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
	}

	/**
	 * Provides a JSON serialization of the settings.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return \array_merge(
			$this->data,
			[
				'unicode_mapping'       => $this->unicode_mapping,
				'primary_quotes'        => "{$this->primary_quote_style->open()}|{$this->primary_quote_style->close()}",
				'secondary_quotes'      => "{$this->secondary_quote_style->open()}|{$this->secondary_quote_style->close()}",
				'dash_style'            => "{$this->dash_style->interval_dash()}|{$this->dash_style->interval_space()}|{$this->dash_style->parenthetical_dash()}|{$this->dash_style->parenthetical_space()}",
				'custom_units'          => $this->custom_units,
			]
		);
	}

	/**
	 * Remaps a unicode character to another one.
	 *
	 * @since 6.5.0
	 *
	 * @param  string $char     The remapped character.
	 * @param  string $new_char The character to actually use.
	 */
	public function remap_character( $char, $new_char ) : void {
		if ( $char !== $new_char ) {
			$this->unicode_mapping[ $char ] = $new_char;
		} else {
			unset( $this->unicode_mapping[ $char ] );
		}

		// Compatibility with the old way of setting the no-break narrow space.
		if ( U::NO_BREAK_NARROW_SPACE === $char ) {
			$this->no_break_narrow_space = $new_char;
		}
	}

	/**
	 * Remaps one or more strings.
	 *
	 * @since 6.5.0
	 *
	 * @template T of string|string[]
	 *
	 * @param  T $input The input string(s).
	 *
	 * @return T
	 */
	public function apply_character_mapping( $input ) {

		// Nothing for us to do.
		if ( empty( $input ) || empty( $this->unicode_mapping ) ) {
			return $input;
		}

		$native_array = \is_array( $input );
		$data         = (array) $input;

		foreach ( $data as $key => $string ) {
			$data[ $key ] = \strtr( $string, $this->unicode_mapping );
		}

		return $native_array ? $data : $data[0]; // @phpstan-ignore-line -- Ignore generics/array clash
	}

	/**
	 * Retrieves the current non-breaking narrow space character (either the
	 * regular non-breaking space &nbsp; or the the true non-breaking narrow space &#8239;).
	 *
	 * @deprecated 6.5.0 Use U::NO_BREAK_NARROW_SPACE instead and let Settings::apply_character_mapping() do the rest.
	 *
	 * @return string
	 */
	public function no_break_narrow_space() {
		return $this->no_break_narrow_space;
	}

	/**
	 * Retrieves the primary (double) quote style.
	 *
	 * @return Quotes
	 */
	public function primary_quote_style() {
		return $this->primary_quote_style;
	}

	/**
	 * Retrieves the secondary (single) quote style.
	 *
	 * @return Quotes
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
	 * (Re)set various options to their default values.
	 */
	public function set_defaults() : void {
		// General attributes.
		$this->set_tags_to_ignore();
		$this->set_classes_to_ignore();
		$this->set_ids_to_ignore();

		// Smart characters.
		$this->set_smart_quotes();
		$this->set_smart_quotes_primary();
		$this->set_smart_quotes_secondary();
		$this->set_smart_quotes_exceptions();
		$this->set_smart_dashes();
		$this->set_smart_dashes_style();
		$this->set_smart_ellipses();
		$this->set_smart_diacritics();
		$this->set_diacritic_language();
		$this->set_diacritic_custom_replacements();
		$this->set_smart_marks();
		$this->set_smart_ordinal_suffix();
		$this->set_smart_ordinal_suffix_match_roman_numerals();
		$this->set_smart_math();
		$this->set_smart_fractions();
		$this->set_smart_exponents();
		$this->set_smart_area_units();

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
	public function set_ignore_parser_errors( $on = false ) : void {
		$this->data[ self::PARSER_ERRORS_IGNORE ] = $on;
	}

	/**
	 * Sets an optional handler for parser errors. Invalid callbacks will be silently ignored.
	 *
	 * @since 6.0.0. callable type is enforced via typehinting.
	 *
	 * @param callable|null $handler Optional. A callable that takes an array of error strings as its parameter. Default null.
	 */
	public function set_parser_errors_handler( callable $handler = null ) : void {
		$this->data[ self::PARSER_ERRORS_HANDLER ] = $handler;
	}

	/**
	 * Enable usage of true "no-break narrow space" (&#8239;) instead of the normal no-break space (&nbsp;).
	 *
	 * @deprecated 6.5.0 Use ::remap_character() instead.
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_true_no_break_narrow_space( $on = false ) : void {

		if ( $on ) {
			$this->remap_character( U::NO_BREAK_NARROW_SPACE, U::NO_BREAK_NARROW_SPACE );
		} else {
			$this->remap_character( U::NO_BREAK_NARROW_SPACE, U::NO_BREAK_SPACE );
		}
	}

	/**
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @param string|string[] $tags A comma separated list or an array of tag names.
	 */
	public function set_tags_to_ignore( $tags = [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ] ) : void {
		// Ensure that we pass only lower-case tag names to XPath.
		$tags = array_filter( array_map( 'strtolower', Strings::maybe_split_parameters( $tags ) ), 'ctype_alnum' );

		$this->data[ self::IGNORE_TAGS ] = array_unique( array_merge( $tags, array_flip( DOM::inappropriate_tags() ) ) );
	}

	/**
	 * Sets classes for which the typography of their children will be left untouched.
	 *
	 * @param string|string[] $classes A comma separated list or an array of class names.
	 */
	public function set_classes_to_ignore( $classes = [ 'vcard', 'noTypo' ] ) : void {
		$this->data[ self::IGNORE_CLASSES ] = Strings::maybe_split_parameters( $classes );
	}

	/**
	 * Sets IDs for which the typography of their children will be left untouched.
	 *
	 * @param string|string[] $ids A comma separated list or an array of tag names.
	 */
	public function set_ids_to_ignore( $ids = [] ) : void {
		$this->data[ self::IGNORE_IDS ] = Strings::maybe_split_parameters( $ids );
	}

	/**
	 * Enables/disables typographic quotes.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_quotes( $on = true ) : void {
		$this->data[ self::SMART_QUOTES ] = $on;
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
	 * @param  Quotes|string $style Optional. A Quotes instance or a quote style constant. Defaults to 'doubleCurled'.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_quotes_primary( $style = Quote_Style::DOUBLE_CURLED ) : void {
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
	 * @param  Quotes|string $style Optional. A Quotes instance or a quote style constant. Defaults to 'singleCurled'.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_quotes_secondary( $style = Quote_Style::SINGLE_CURLED ) : void {
		$this->secondary_quote_style = $this->get_quote_style( $style );
	}

	/**
	 * Retrieves a Quotes instance from a given style.
	 *
	 * @param  Quotes|string $style A Quotes instance or a quote style constant.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 *
	 * @return Quotes
	 */
	protected function get_quote_style( $style ) {
		return $this->get_style( $style, Quotes::class, [ Quote_Style::class, 'get_styled_quotes' ], 'quote' );
	}

	/**
	 * Sets the list of exceptional words for smart quotes replacement. Mainly,
	 * this is used for contractions beginning with an apostrophe.
	 *
	 * @param string[] $exceptions Optional. An array of replacements indexed by the ”non-smart" form.
	 *                             Default a list of English words beginning with an apostrophy.
	 */
	public function set_smart_quotes_exceptions( $exceptions = [
		"'tain't"   => U::APOSTROPHE . 'tain' . U::APOSTROPHE . 't',
		"'twere"    => U::APOSTROPHE . 'twere',
		"'twas"     => U::APOSTROPHE . 'twas',
		"'tis"      => U::APOSTROPHE . 'tis',
		"'til"      => U::APOSTROPHE . 'til',
		"'bout"     => U::APOSTROPHE . 'bout',
		"'nuff"     => U::APOSTROPHE . 'nuff',
		"'round"    => U::APOSTROPHE . 'round',
		"'cause"    => U::APOSTROPHE . 'cause',
		"'splainin" => U::APOSTROPHE . 'splainin',
		"'em'"      => U::APOSTROPHE . 'em',
	] ) : void {
		$this->data[ self::SMART_QUOTES_EXCEPTIONS ] = [
			'patterns'     => \array_keys( $exceptions ),
			'replacements' => \array_values( $exceptions ),
		];
	}

	/**
	 * Retrieves an object from a given style.
	 *
	 * @template T
	 *
	 * @param  object|string   $style          A style object instance or a style constant.
	 * @param  class-string<T> $expected_class A class name.
	 * @param  callable        $get_style      A function that returns a style object from a given style constant.
	 * @param  string          $description    Style description for the exception message.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 *
	 * @return T An instance of $expected_class.
	 */
	protected function get_style( $style, $expected_class, callable $get_style, $description ) {
		if ( $style instanceof $expected_class ) {
			$object = $style;
		} else {
			$object = $get_style( $style, $this );
		}

		if ( ! \is_object( $object ) || ! $object instanceof $expected_class ) {
			$style = \is_string( $style ) ? $style : \get_class( $style );
			throw new \DomainException( "Invalid $description style $style." );
		}

		return $object;
	}

	/**
	 * Enables/disables replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_dashes( $on = true ) : void {
		$this->data[ self::SMART_DASHES ] = $on;
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
	public function set_smart_dashes_style( $style = Dash_Style::TRADITIONAL_US ) : void {
		$this->dash_style = $this->get_style( $style, Settings\Dashes::class, [ Dash_Style::class, 'get_styled_dashes' ], 'dash' );
	}

	/**
	 * Enables/disables replacement of "..." with "…".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ellipses( $on = true ) : void {
		$this->data[ self::SMART_ELLIPSES ] = $on;
	}

	/**
	 * Enables/disables replacement "creme brulee" with "crème brûlée".
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_diacritics( $on = true ) : void {
		$this->data[ self::SMART_DIACRITICS ] = $on;
	}

	/**
	 * Sets the language used for diacritics replacements.
	 *
	 * @param string $lang Has to correspond to a filename in 'diacritics'. Optional. Default 'en-US'.
	 */
	public function set_diacritic_language( $lang = 'en-US' ) : void {
		if ( isset( $this->data[ self::DIACRITIC_LANGUAGE ] ) && $this->data[ self::DIACRITIC_LANGUAGE ] === $lang ) {
			return;
		}

		$this->data[ self::DIACRITIC_LANGUAGE ] = $lang;
		$language_file_name                     = \dirname( __FILE__ ) . '/diacritics/' . $lang . '.json';
		$diacritics                             = [];

		if ( \file_exists( $language_file_name ) ) {
			$diacritics = \json_decode( (string) \file_get_contents( $language_file_name ), true );
		}

		if ( ! empty( $diacritics['diacritic_words'] ) ) {
			$this->data[ self::DIACRITIC_WORDS ] = $diacritics['diacritic_words'];
		} else {
			unset( $this->data[ self::DIACRITIC_WORDS ] );
		}

		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Sets up custom diacritics replacements.
	 *
	 * @param string|array<string,string> $custom_replacements An array formatted [needle=>replacement, needle=>replacement...],
	 *                                                         or a string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 */
	public function set_diacritic_custom_replacements( $custom_replacements = [] ) : void {
		if ( ! \is_array( $custom_replacements ) ) {
			$custom_replacements = $this->parse_diacritics_replacement_string( $custom_replacements );
		}

		$this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ] = [];
		foreach ( $custom_replacements as $key => $replacement ) {
			$key         = \strip_tags( \trim( $key ) );
			$replacement = \strip_tags( \trim( $replacement ) );

			if ( ! empty( $key ) && ! empty( $replacement ) ) {
				$this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ][ $key ] = $replacement;
			}
		}

		$this->update_diacritics_replacement_arrays();
	}

	/**
	 * Parses a custom diacritics replacement string into an array.
	 *
	 * @param string $custom_replacements A string formatted `"needle"=>"replacement","needle"=>"replacement",...
	 *
	 * @return array<string,string>
	 */
	private function parse_diacritics_replacement_string( $custom_replacements ) {
		$replacements = [];
		foreach ( ( \preg_split( '/,/', $custom_replacements, -1, \PREG_SPLIT_NO_EMPTY ) ?: [] ) as $replacement ) { // phpcs:ignore WordPress.PHP.DisallowShortTernary -- Ensure array type in case of error.
			if ( \preg_match( '/(?<kquo>"|\')(?<key>(?:(?!\k<kquo>).)+)\k<kquo>\s*=>\s*(?<rquo>"|\')(?<replacement>(?:(?!\k<rquo>).)+)\k<rquo>/', $replacement, $match ) ) {
				$replacements[ $match['key'] ] = $match['replacement'];
			}
		}

		return $replacements;
	}

	/**
	 * Provides an array_map implementation with control over resulting array's keys.
	 *
	 * Based on https://gist.github.com/jasand-pereza/84ecec7907f003564584.
	 *
	 * @since 6.0.0
	 * @deprecated 6.7.0
	 *
	 * @template T
	 *
	 * @param  callable $callback A callback function that needs to return [ $key => $value ] pairs.
	 * @param  array<T> $array    The array.
	 *
	 * @return array<T>
	 */
	protected static function array_map_assoc( callable $callback, array $array ) : array {
		$new = [];

		foreach ( $array as $k => $v ) {
			$u = $callback( $k, $v );

			if ( ! empty( $u ) ) {
				$new[ \key( $u ) ] = \current( $u );
			}
		}

		return $new;
	}

	/**
	 * Update the pattern and replacement arrays in $settings['diacriticReplacement'].
	 *
	 * Should be called whenever a new diacritics replacement language is selected or
	 * when the custom replacements are updated.
	 */
	private function update_diacritics_replacement_arrays() : void {
		$patterns     = [];
		$replacements = [];

		if ( ! empty( $this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ] ) ) {
			$this->parse_diacritics_rules( $this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ], $patterns, $replacements );
		}
		if ( ! empty( $this->data[ self::DIACRITIC_WORDS ] ) ) {
			$this->parse_diacritics_rules( $this->data[ self::DIACRITIC_WORDS ], $patterns, $replacements );
		}

		$this->data[ self::DIACRITIC_REPLACEMENT_DATA ] = [
			'patterns'     => $patterns,
			'replacements' => $replacements,
		];
	}

	/**
	 * Parse an array of diacritics rules.
	 *
	 * @param array<string,string> $diacritics_rules The rules ( $word => $replacement ).
	 * @param string[]             $patterns         Resulting patterns. Passed by reference.
	 * @param array<string,string> $replacements     Resulting replacements. Passed by reference.
	 */
	private function parse_diacritics_rules( array $diacritics_rules, array &$patterns, array &$replacements ) : void {

		foreach ( $diacritics_rules as $needle => $replacement ) {
			$patterns[]              = '/\b(?<!\w[' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . '])' . $needle . '\b(?![' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . ']\w)/u';
			$replacements[ $needle ] = $replacement;
		}
	}

	/**
	 * Enables/disables replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_marks( $on = true ) : void {
		$this->data[ self::SMART_MARKS ] = $on;
	}

	/**
	 * Enables/disables proper mathematical symbols.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_math( $on = true ) : void {
		$this->data[ self::SMART_MATH ] = $on;
	}

	/**
	 * Enables/disables replacement of 2^2 with 2<sup>2</sup>
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_exponents( $on = true ) : void {
		$this->data[ self::SMART_EXPONENTS ] = $on;
	}

	/**
	 * Enables/disables replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_fractions( $on = true ) : void {
		$this->data[ self::SMART_FRACTIONS ] = $on;
	}

	/**
	 * Enables/disables replacement of 1st with 1<sup>st</sup>.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_ordinal_suffix( $on = true ) : void {
		$this->data[ self::SMART_ORDINAL_SUFFIX ] = $on;
	}

	/**
	 * Enables/disables replacement of XXe with XX<sup>e</sup>.
	 *
	 * @since 6.5.0
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_smart_ordinal_suffix_match_roman_numerals( $on = false ) : void {
		$this->data[ self::SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS ] = $on;
	}

	/**
	 * Enables/disables replacement of m2 with m³ and m3 with m³.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_smart_area_units( $on = true ) : void {
		$this->data[ self::SMART_AREA_UNITS ] = $on;
	}

	/**
	 * Enables/disables forcing single character words to next line with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_single_character_word_spacing( $on = true ) : void {
		$this->data[ self::SINGLE_CHARACTER_WORD_SPACING ] = $on;
	}

	/**
	 * Enables/disables fraction spacing.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_fraction_spacing( $on = true ) : void {
		$this->data[ self::FRACTION_SPACING ] = $on;
	}

	/**
	 * Enables/disables keeping units and values together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_unit_spacing( $on = true ) : void {
		$this->data[ self::UNIT_SPACING ] = $on;
	}

	/**
	 * Enables/disables numbered abbreviations like "ISO 9000" together with the insertion of &nbsp;.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_numbered_abbreviation_spacing( $on = true ) : void {
		$this->data[ self::NUMBERED_ABBREVIATION_SPACING ] = $on;
	}

	/**
	 * Enables/disables extra whitespace before certain punction marks, as is the French custom.
	 *
	 * @since 6.0.0 The default value is now `false`.`
	 *
	 * @param bool $on Optional. Default false.
	 */
	public function set_french_punctuation_spacing( $on = false ) : void {
		$this->data[ self::FRENCH_PUNCTUATION_SPACING ] = $on;
	}

	/**
	 * Sets the list of units to keep together with their values.
	 *
	 * @param string|string[] $units A comma separated list or an array of units.
	 */
	public function set_units( $units = [] ) : void {
		$this->data[ self::UNITS ] = Strings::maybe_split_parameters( $units );
		$this->custom_units        = $this->update_unit_pattern( $this->data[ self::UNITS ] );
	}

	/**
	 * Update pattern for matching custom units.
	 *
	 * @since 6.4.0 Visibility changed to protected, return value added.
	 *
	 * @param string[] $units An array of unit names.
	 *
	 * @return string
	 */
	protected function update_unit_pattern( array $units ) {
		// Update unit regex pattern.
		foreach ( $units as $index => $unit ) {
			$units[ $index ] = \preg_quote( $unit, '/' );
		}

		$custom_units  = \implode( '|', $units );
		$custom_units .= ! empty( $custom_units ) ? '|' : '';

		return $custom_units;
	}

	/**
	 * Enables/disables wrapping of Em and En dashes are in thin spaces.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dash_spacing( $on = true ) : void {
		$this->data[ self::DASH_SPACING ] = $on;
	}

	/**
	 * Enables/disables removal of extra whitespace characters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_space_collapse( $on = true ) : void {
		$this->data[ self::SPACE_COLLAPSE ] = $on;
	}

	/**
	 * Enables/disables widow handling.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_dewidow( $on = true ) : void {
		$this->data[ self::DEWIDOW ] = $on;
	}

	/**
	 * Sets the maximum length of widows that will be protected.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_max_dewidow_length( $length = 5 ) : void {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data[ self::DEWIDOW_MAX_LENGTH ] = $length;
	}

	/**
	 * Sets the maximum number of words considered for dewidowing.
	 *
	 * @param int $number Defaults to 1. Only 1, 2 and 3 are valid.
	 */
	public function set_dewidow_word_number( $number = 1 ) : void {
		$number = ( $number > 3 || $number < 1 ) ? 1 : $number;

		$this->data[ self::DEWIDOW_WORD_NUMBER ] = $number;
	}

	/**
	 * Sets the maximum length of pulled text to keep widows company.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_max_dewidow_pull( $length = 5 ) : void {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data[ self::DEWIDOW_MAX_PULL ] = $length;
	}

	/**
	 * Enables/disables wrapping at internal hard hyphens with the insertion of a zero-width-space.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_wrap_hard_hyphens( $on = true ) : void {
		$this->data[ self::HYPHEN_HARD_WRAP ] = $on;
	}

	/**
	 * Enables/disables wrapping of urls.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_url_wrap( $on = true ) : void {
		$this->data[ self::URL_WRAP ] = $on;
	}

	/**
	 * Enables/disables wrapping of email addresses.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_email_wrap( $on = true ) : void {
		$this->data[ self::EMAIL_WRAP ] = $on;
	}

	/**
	 * Sets the minimum character requirement after an URL wrapping point.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_url_wrap( $length = 5 ) : void {
		$length = ( $length > 0 ) ? $length : 5;

		$this->data[ self::URL_MIN_AFTER_WRAP ] = $length;
	}

	/**
	 * Enables/disables wrapping of ampersands in <span class="amp">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_ampersands( $on = true ) : void {
		$this->data[ self::STYLE_AMPERSANDS ] = $on;
	}

	/**
	 * Enables/disables wrapping caps in <span class="caps">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_caps( $on = true ) : void {
		$this->data[ self::STYLE_CAPS ] = $on;
	}

	/**
	 * Enables/disables wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_initial_quotes( $on = true ) : void {
		$this->data[ self::STYLE_INITIAL_QUOTES ] = $on;
	}

	/**
	 * Enables/disables wrapping of numbers in <span class="numbers">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_numbers( $on = true ) : void {
		$this->data[ self::STYLE_NUMBERS ] = $on;
	}

	/**
	 * Enables/disables wrapping of punctiation and wide characters in <span class="pull-*">.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_style_hanging_punctuation( $on = true ) : void {
		$this->data[ self::STYLE_HANGING_PUNCTUATION ] = $on;
	}

	/**
	 * Sets the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @param string|string[] $tags A comma separated list or an array of tag names.
	 */
	public function set_initial_quote_tags( $tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ] ) : void {
		// Make array if handed a list of tags as a string.
		if ( ! \is_array( $tags ) ) {
			$tags = \preg_split( '/[^a-z0-9]+/', $tags, -1, \PREG_SPLIT_NO_EMPTY ) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary -- Ensure array type.
		}

		// Store the tag array inverted (with the tagName as its index for faster lookup).
		$this->data[ self::INITIAL_QUOTE_TAGS ] = \array_change_key_case( \array_flip( $tags ), \CASE_LOWER );
	}

	/**
	 * Enables/disables hyphenation.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenation( $on = true ) : void {
		$this->data[ self::HYPHENATION ] = $on;
	}

	/**
	 * Sets the hyphenation pattern language.
	 *
	 * @param string $lang Has to correspond to a filename in 'lang'. Optional. Default 'en-US'.
	 */
	public function set_hyphenation_language( $lang = 'en-US' ) : void {
		if ( isset( $this->data[ self::HYPHENATION_LANGUAGE ] ) && $this->data[ self::HYPHENATION_LANGUAGE ] === $lang ) {
			return; // Bail out, no need to do anything.
		}

		$this->data[ self::HYPHENATION_LANGUAGE ] = $lang;
	}

	/**
	 * Sets the minimum length of a word that may be hyphenated.
	 *
	 * @param int $length Defaults to 5. Trying to set the value to less than 2 resets the length to the default.
	 */
	public function set_min_length_hyphenation( $length = 5 ) : void {
		$length = ( $length > 1 ) ? $length : 5;

		$this->data[ self::HYPHENATION_MIN_LENGTH ] = $length;
	}

	/**
	 * Sets the minimum character requirement before a hyphenation point.
	 *
	 * @param int $length Defaults to 3. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_before_hyphenation( $length = 3 ) : void {
		$length = ( $length > 0 ) ? $length : 3;

		$this->data[ self::HYPHENATION_MIN_BEFORE ] = $length;
	}

	/**
	 * Sets the minimum character requirement after a hyphenation point.
	 *
	 * @param int $length Defaults to 2. Trying to set the value to less than 1 resets the length to the default.
	 */
	public function set_min_after_hyphenation( $length = 2 ) : void {
		$length = ( $length > 0 ) ? $length : 2;

		$this->data[ self::HYPHENATION_MIN_AFTER ] = $length;
	}

	/**
	 * Enables/disables hyphenation of titles and headings.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_headings( $on = true ) : void {
		$this->data[ self::HYPHENATE_HEADINGS ] = $on;
	}

	/**
	 * Enables/disables hyphenation of words set completely in capital letters.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_all_caps( $on = true ) : void {
		$this->data[ self::HYPHENATE_ALL_CAPS ] = $on;
	}

	/**
	 * Enables/disables hyphenation of words starting with a capital letter.
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_title_case( $on = true ) : void {
		$this->data[ self::HYPHENATE_TITLE_CASE ] = $on;
	}

	/**
	 * Enables/disables hyphenation of compound words (e.g. "editor-in-chief").
	 *
	 * @param bool $on Optional. Default true.
	 */
	public function set_hyphenate_compounds( $on = true ) : void {
		$this->data[ self::HYPHENATE_COMPOUNDS ] = $on;
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param string|string[] $exceptions An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
	 *        In the latter case, only alphanumeric characters and hyphens are recognized. The default is empty.
	 */
	public function set_hyphenation_exceptions( $exceptions = [] ) : void {
		$this->data[ self::HYPHENATION_CUSTOM_EXCEPTIONS ] = Strings::maybe_split_parameters( $exceptions );
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
		$hash = \md5( (string) \json_encode( $this ), $raw_output );

		if ( $max_length < \strlen( $hash ) && $max_length > 0 ) {
			$hash = \substr( $hash, 0, $max_length );
		}

		return $hash;
	}
}
