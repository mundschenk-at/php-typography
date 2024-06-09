<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2024 Peter Putzer.
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

use BadMethodCallException;
use OutOfRangeException;
use TypeError;

use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Dashes;
use PHP_Typography\Settings\Quote_Style;
use PHP_Typography\Settings\Quotes;

/**
 * Store settings for the PHP_Typography class.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 4.0.0
 * @since 6.5.0 The protected property $no_break_narrow_space has been deprecated.
 * @since 7.0.0 Deprecated properties and methods relating to $no_break_narrow_space have been removed.
 *              Most setter methods have been virtualized via `__call`. The class no longer allows array
 *              access.
 *
 *              Additional removed methods:
 *                - array_map_assoc (previously deprecated)
 *                - custom_unit
 *                - get_style
 *                - get_quote_style
 *                - update_unit_pattern
 *                - offsetSet
 *                - offsetExists
 *                - offsetUnset
 *                - offsetGet
 *
 * General attributes:
 * @property-read string[] $tags_to_ignore An array of tags to ignore.
 * @property-read string[] $classes_to_ignore An array of HTML classes to ignore.
 * @property-read string[] $ids_to_ignore An array of HTML IDs to ignore.
 *
 * Smart characters:
 * @property-read bool $smart_quotes Whether typographic quotes are enabled.
 * @property-read array{patterns: string[], replacements: string[]} $smart_quotes_exceptions The specific search & replace patterns for non-standard smart quotes.
 * @property-read bool $smart_dashes Whether replacement of "a--a" with En Dash " -- " and "---" with Em Dash is enabled.
 * @property-read bool $smart_ellipses Whether replacement of "..." with "…" is enabled.
 * @property-read bool $smart_diacritics Whether replacement replacement "creme brulee" with "crème brûlée" is enabled.
 * @property-read array{patterns: string[], replacements: string[]} $diacritic_combined The specific search & replace patterns for "smart diacritics" (combined from the diacritic language and any custom replacements).
 * @property-read array{patterns: string[], replacements: string[]} $diacritic_words The search & replace patterns read from the diacritic language file.
 * @property-read array{patterns: string[], replacements: string[]} $diacritic_custom_replacements The custom search & replace patterns for "smart diacritics".
 * @property-read bool $smart_marks Whether replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗ is enabled.
 * @property-read bool $smart_math Whether proper mathematical symbols are enabled.
 * @property-read bool $smart_exponents Whether replacement of 2^2 with 2<sup>2</sup> is enabled.
 * @property-read bool $smart_fractions Whether replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub> is enabled.
 * @property-read bool $smart_ordinal_suffix Whether replacement of 1st with 1<sup>st</sup> is enabled.
 * @property-read bool $smart_ordinal_suffix_match_roman_numerals Whether replacement of XXe with XX<sup>e</sup> is enabled.
 * @property-read bool $smart_area_units Whether replacement of m2 with m³ and m3 with m³ is enabled.
 *
 * Smart spacing:
 * @property-read bool $single_character_word_spacing Whether single character words are forced to the next line with the insertion of &nbsp;.
 * @property-read bool $fraction_spacing Whether fraction spacing is enabled.
 * @property-read bool $unit_spacing Whether units and values are kept together with the insertion of &nbsp;.
 * @property-read string $custom_units A regex pattern for custom units (or the empty string).
 * @property-read bool $numbered_abbreviation_spacing Whether numbered abbreviations like "ISO 9000" are kept together with the insertion of &nbsp;.
 * @property-read bool $french_punctuation_spacing Whether extra whitespace before certain punction marks according to the French custom is enabled.
 * @property-read bool $dash_spacing Whether wrapping of Em and En dashes are in thin spaces is enabled.
 * @property-read bool $space_collapse Whether extra whitespace characters should be removed.
 * @property-read bool $dewidow Whether widow handling is enabled.
 * @property-read int $max_dewidow_length The maximum length of widows that will be protected.
 * @property-read int $dewidow_word_number The maximum number of words considered for dewidowing.
 * @property-read int $max_dewidow_pull The maximum length of pulled text to keep widows company.
 * @property-read bool $wrap_hard_hyphens Whether wrapping at internal hard hyphens with the insertion of a zero-width-space is enabled.
 * @property-read bool $wrap_urls Whether wrapping of URLs is enabled.
 * @property-read int $min_after_url_wrap The minimum number of characters required after an URL wrapping point.
 * @property-read bool $wrap_emails wrapping of email addresses is enabled.
 *
 * Characters styling:
 * @property-read bool $style_ampersands Whether wrapping of ampersands in <span class="amp"> is enabled.
 * @property-read bool $style_caps Whether wrapping caps in <span class="caps"> is enabled.
 * @property-read bool $style_initial_quotes Whether wrapping of initial quotes in <span class="quo"> or <span class="dquo"> is enabled.
 * @property-read array<string,int> $initial_quote_tags The inverted list of tags where initial quotes and guillemets should be styled (with lower-case tags as keys).
 * @property-read bool $style_numbers Whether wrapping of numbers in <span class="numbers"> is enabled.
 * @property-read bool $style_hanging_punctuation Whether wrapping of punctuation and wide characters in <span class="pull-*"> is enabled.
 *
 * Hyphenation:
 * @property-read bool $hyphenation Whether hyphenation is enabled.
 * @property-read string $hyphenation_language The hyphenation pattern language.
 * @property-read int<2,max> $min_length_hyphenation The minimum length of a word that may be hyphenated.
 * @property-read int<1,max> $min_before_hyphenation The minimum character requirement before a hyphenation point.
 * @property-read int<1,max> $min_after_hyphenation The minimum character requirement after a hyphenation point.
 * @property-read string[] $hyphenation_exceptions A list of custom hyphenations (hyphenation points marked by hard hyphens).
 * @property-read bool $hyphenate_headings Whether hyphenation of titles and headings is enabled.
 * @property-read bool $hyphenate_all_caps Whether hyphenation of words set completely in capital letters is enabled.
 * @property-read bool $hyphenate_title_case Whether hyphenation of words starting with a capital letter is enabled.
 * @property-read bool $hyphenate_compounds Whether hyphenation of compound words (e.g. "editor-in-chief") is enabled.
 *
 * Parser error handling:
 * @property-read bool $ignore_parser_errors Whether lenient parser error handling (output "best guess" HTML) is enabled.
 * @property-read ?callable $parser_errors_handler An optional handler for parser errors. The callable takes an array of error strings as its parameter.
 *
 * Setters for general attributes:
 * @method void set_classes_to_ignore( string[] $classes = ['vcard','noTypo'] )  Sets classes for which the typography of their children will be left untouched.
 * @method void set_ids_to_ignore( string[] $ids = [] ) Sets IDs for which the typography of their children will be left untouched.
 *
 * Setters for smart characters:
 * @method void set_smart_quotes( bool $on = true ) Enables/disables typographic quotes.
 * @method void set_smart_dashes( bool $on = true ) Enables/disables replacement of "a--a" with En Dash " -- " and "---" with Em Dash.
 * @method void set_smart_ellipses( bool $on = true ) Enables/disables replacement of "..." with "…".
 * @method void set_smart_diacritics( bool $on = true ) Enables/disables replacement "creme brulee" with "crème brûlée".
 * @method void set_smart_marks( bool $on = true ) Enables/disables replacement of (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗.
 * @method void set_smart_math( bool $on = true ) Enables/disables proper mathematical symbols.
 * @method void set_smart_exponents( bool $on = true ) Enables/disables replacement of 2^2 with 2<sup>2</sup>.
 * @method void set_smart_fractions( bool $on = true ) Enables/disables replacement of 1/4 with <sup>1</sup>&#8260;<sub>4</sub>.
 * @method void set_smart_ordinal_suffix( bool $on = true ) Enables/disables replacement of 1st with 1<sup>st</sup>.
 * @method void set_smart_ordinal_suffix_match_roman_numerals( bool $on = false ) Enables/disables replacement of XXe with XX<sup>e</sup>.
 * @method void set_smart_area_units( bool $on = true ) Enables/disables replacement of m2 with m³ and m3 with m³.
 *
 * Setters for smart spacing:
 * @method void set_single_character_word_spacing( bool $on = true ) Enables/disables forcing single character words to next line with the insertion of &nbsp;.
 * @method void set_fraction_spacing( bool $on = true ) Enables/disables fraction spacing.
 * @method void set_unit_spacing( bool $on = true ) Enables/disables keeping units and values together with the insertion of &nbsp;.
 * @method void set_numbered_abbreviation_spacing( bool $on = true ) Enables/disables numbered abbreviations like "ISO 9000" together with the insertion of &nbsp;.
 * @method void set_french_punctuation_spacing( bool $on = false ) Enables/disables extra whitespace before certain punction marks, as is the French custom.
 * @method void set_dash_spacing( bool $on = true ) Enables/disables wrapping of Em and En dashes are in thin spaces.
 * @method void set_space_collapse( bool $on = true ) Enables/disables removal of extra whitespace characters.
 * @method void set_dewidow( bool $on = true ) Enables/disables widow handling.
 * @method void set_max_dewidow_length( int $length = 5 ) Sets the maximum length of widows that will be protected. The length cannot be less than 2.
 * @method void set_dewidow_word_number( int $number = 1 ) Sets the maximum number of words considered for dewidowing. Only 1, 2 and 3 are valid arguments.
 * @method void set_max_dewidow_pull( int $length = 5 ) Sets the maximum length of pulled text to keep widows company. The length cannot be less than 2.
 * @method void set_wrap_hard_hyphens( bool $on = true ) Enables/disables wrapping at internal hard hyphens with the insertion of a zero-width-space.
 * @method void set_wrap_urls( bool $on = true ) Enables/disables wrapping of URLs.
 * @method void set_min_after_url_wrap( int $length = 5 ) Sets the minimum character requirement after an URL wrapping point. The length cannot be less than 1.
 * @method void set_wrap_emails( bool $on = true ) Enables/disables wrapping of email addresses.
 *
 * Setters for character styling:
 * @method void set_style_ampersands( bool $on = true ) Enables/disables wrapping of ampersands in <span class="amp">.
 * @method void set_style_caps( bool $on = true ) Enables/disables wrapping caps in <span class="caps">.
 * @method void set_style_initial_quotes( bool $on = true ) Enables/disables wrapping of initial quotes in <span class="quo"> or <span class="dquo">.
 * @method void set_style_numbers( bool $on = true ) Enables/disables wrapping of numbers in <span class="numbers">.
 * @method void set_style_hanging_punctuation( bool $on = true ) Enables/disables wrapping of punctuation and wide characters in <span class="pull-*">.
 *
 * Setters for hyphenation:
 * @method void set_hyphenation( bool $on = true ) Enables/disables hyphenation.
 * @method void set_hyphenation_language( string $lang = 'en-US' ) Sets the hyphenation pattern language.
 * @method void set_min_length_hyphenation( int $length = 5 ) Sets the minimum length of a word that may be hyphenated. The length cannot be less than 2.
 * @method void set_min_before_hyphenation( int $length = 3 ) Sets the minimum character requirement before a hyphenation point. The length cannot be less than 1.
 * @method void set_min_after_hyphenation( int $length = 2 ) Sets the minimum character requirement after a hyphenation point. The length cannot be less than 1.
 * @method void set_hyphenate_headings( bool $on = true ) Enables/disables hyphenation of titles and headings.
 * @method void set_hyphenate_all_caps( bool $on = true ) Enables/disables hyphenation of words set completely in capital letters.
 * @method void set_hyphenate_title_case( bool $on = true ) Enables/disables hyphenation of words starting with a capital letter.
 * @method void set_hyphenate_compounds( bool $on = true ) Enables/disables hyphenation of compound words (e.g. "editor-in-chief").
 * @method void set_hyphenation_exceptions( string[] $exceptions = [] ) Sets custom word hyphenations. Takes an array of words with all hyphenation points marked with a hard hyphen.
 *
 * Setters for parser error handling:
 * @method void set_ignore_parser_errors( bool $on = false ) Enable lenient parser error handling (HTML is "best guess" if enabled).
 * @method void set_parser_errors_handler( callable $handler = null ) Sets an optional handler for parser errors. The callable takes an array of error strings as its parameter. Invalid callbacks will be silently ignored.
 */
class Settings implements \JsonSerializable {

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
	const DIACRITIC_LANGUAGE                  = 'diacriticLanguage'; // public for defaults only.
	const DIACRITIC_WORDS                     = 'diacriticWords'; // public for testing.
	const DIACRITIC_REPLACEMENT_DATA          = 'diacriticReplacement';
	const DIACRITIC_CUSTOM_REPLACEMENTS       = 'diacriticCustomReplacements'; // public for testing.
	const SMART_MARKS                         = 'smartMarks';
	const SMART_MATH                          = 'smartMath';
	const SMART_EXPONENTS                     = 'smartExponents';
	const SMART_FRACTIONS                     = 'smartFractions';
	const SMART_ORDINAL_SUFFIX                = 'smartOrdinalSuffix';
	const SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS = 'smartOrdinalSuffixRomanNumerals';
	const SMART_AREA_UNITS                    = 'smartAreaVolumeUnits';

	// Smart spacing.
	const SINGLE_CHARACTER_WORD_SPACING = 'singleCharacterWordSpacing';
	const FRACTION_SPACING              = 'fractionSpacing';
	const UNIT_SPACING                  = 'unitSpacing';
	const CUSTOM_UNITS                  = 'units';
	const NUMBERED_ABBREVIATION_SPACING = 'numberedAbbreviationSpacing';
	const FRENCH_PUNCTUATION_SPACING    = 'frenchPunctuationSpacing';
	const DASH_SPACING                  = 'dashSpacing';
	const SPACE_COLLAPSE                = 'spaceCollapse';
	const DEWIDOW                       = 'dewidow';
	const DEWIDOW_MAX_LENGTH            = 'dewidowMaxLength';
	const DEWIDOW_MAX_PULL              = 'dewidowMaxPull';
	const DEWIDOW_WORD_NUMBER           = 'dewidowWordNumber';
	const HYPHEN_HARD_WRAP              = 'hyphenHardWrap';
	const URL_WRAP                      = 'urlWrap';
	const URL_MIN_AFTER_WRAP            = 'urlMinAfterWrap';
	const EMAIL_WRAP                    = 'emailWrap';

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
	 * A hashmap of settings for the various typographic options.
	 *
	 * @var mixed[]
	 */
	protected $data = [];

	/**
	 * The current dash style.
	 *
	 * @var Dashes
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
	 * Definitions of the properties that use a virtual setter function.
	 */
	protected const VIRTUAL_PROPERTIES = [
		[
			'property' => self::PARSER_ERRORS_IGNORE,
			'name'     => 'ignore_parser_errors',
			'default'  => false,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::PARSER_ERRORS_HANDLER,
			'name'     => 'parser_errors_handler',
			'default'  => null,
			'verify'   => 'is_callable',
		],
		[
			'property' => self::IGNORE_TAGS,
			'name'     => 'tags_to_ignore',
		],
		[
			'property' => self::IGNORE_CLASSES,
			'name'     => 'classes_to_ignore',
			'default'  => [ 'vcard', 'noTypo' ],
			'verify'   => 'is_array',
		],
		[
			'property' => self::IGNORE_IDS,
			'name'     => 'ids_to_ignore',
			'default'  => [],
			'verify'   => 'is_array',
		],
		[
			'property' => self::SMART_QUOTES,
			'name'     => 'smart_quotes',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_QUOTES_EXCEPTIONS,
			'name'     => 'smart_quotes_exceptions',
		],
		[
			'property' => self::SMART_DASHES,
			'name'     => 'smart_dashes',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_ELLIPSES,
			'name'     => 'smart_ellipses',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_DIACRITICS,
			'name'     => 'smart_diacritics',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::DIACRITIC_REPLACEMENT_DATA,
			'name'     => 'diacritic_combined',
		],
		[
			'property' => self::DIACRITIC_WORDS,
			'name'     => 'diacritic_words',
		],
		[
			'property' => self::DIACRITIC_CUSTOM_REPLACEMENTS,
			'name'     => 'diacritic_custom_replacements',
		],
		[
			'property' => self::SMART_MARKS,
			'name'     => 'smart_marks',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_MATH,
			'name'     => 'smart_math',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_EXPONENTS,
			'name'     => 'smart_exponents',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_FRACTIONS,
			'name'     => 'smart_fractions',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_ORDINAL_SUFFIX,
			'name'     => 'smart_ordinal_suffix',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_ORDINAL_SUFFIX_ROMAN_NUMERALS,
			'name'     => 'smart_ordinal_suffix_match_roman_numerals',
			'default'  => false,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SMART_AREA_UNITS,
			'name'     => 'smart_area_units',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SINGLE_CHARACTER_WORD_SPACING,
			'name'     => 'single_character_word_spacing',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::FRACTION_SPACING,
			'name'     => 'fraction_spacing',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::UNIT_SPACING,
			'name'     => 'unit_spacing',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::CUSTOM_UNITS,
			'name'     => 'custom_units',
		],
		[
			'property' => self::NUMBERED_ABBREVIATION_SPACING,
			'name'     => 'numbered_abbreviation_spacing',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::FRENCH_PUNCTUATION_SPACING,
			'name'     => 'french_punctuation_spacing',
			'default'  => false,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::DASH_SPACING,
			'name'     => 'dash_spacing',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::SPACE_COLLAPSE,
			'name'     => 'space_collapse',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::DEWIDOW,
			'name'     => 'dewidow',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::DEWIDOW_MAX_LENGTH,
			'name'     => 'max_dewidow_length',
			'default'  => 5,
			'verify'   => 'is_int',
			'min'      => 2,
		],
		[
			'property' => self::DEWIDOW_WORD_NUMBER,
			'name'     => 'dewidow_word_number',
			'default'  => 1,
			'verify'   => 'is_int',
			'min'      => 1,
			'max'      => 3,
		],
		[
			'property' => self::DEWIDOW_MAX_PULL,
			'name'     => 'max_dewidow_pull',
			'default'  => 5,
			'verify'   => 'is_int',
			'min'      => 2,
		],
		[
			'property' => self::HYPHEN_HARD_WRAP,
			'name'     => 'wrap_hard_hyphens',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::URL_WRAP,
			'name'     => 'wrap_urls',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::URL_MIN_AFTER_WRAP,
			'name'     => 'min_after_url_wrap',
			'default'  => 5,
			'verify'   => 'is_int',
			'min'      => 1,
		],
		[
			'property' => self::EMAIL_WRAP,
			'name'     => 'wrap_emails',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::STYLE_AMPERSANDS,
			'name'     => 'style_ampersands',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::STYLE_CAPS,
			'name'     => 'style_caps',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::STYLE_INITIAL_QUOTES,
			'name'     => 'style_initial_quotes',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::INITIAL_QUOTE_TAGS,
			'name'     => 'initial_quote_tags',
		],
		[
			'property' => self::STYLE_NUMBERS,
			'name'     => 'style_numbers',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::STYLE_HANGING_PUNCTUATION,
			'name'     => 'style_hanging_punctuation',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::HYPHENATION,
			'name'     => 'hyphenation',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::HYPHENATION_LANGUAGE,
			'name'     => 'hyphenation_language',
			'default'  => 'en-US',
			'verify'   => 'is_string',
		],
		[
			'property' => self::HYPHENATION_MIN_LENGTH,
			'name'     => 'min_length_hyphenation',
			'default'  => 5,
			'verify'   => 'is_int',
			'min'      => 2,
		],
		[
			'property' => self::HYPHENATION_MIN_BEFORE,
			'name'     => 'min_before_hyphenation',
			'default'  => 3,
			'verify'   => 'is_int',
			'min'      => 1,
		],
		[
			'property' => self::HYPHENATION_MIN_AFTER,
			'name'     => 'min_after_hyphenation',
			'default'  => 2,
			'verify'   => 'is_int',
			'min'      => 1,
		],
		[
			'property' => self::HYPHENATION_CUSTOM_EXCEPTIONS,
			'name'     => 'hyphenation_exceptions',
			'default'  => [],
			'verify'   => 'is_array',
		],
		[
			'property' => self::HYPHENATE_HEADINGS,
			'name'     => 'hyphenate_headings',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::HYPHENATE_ALL_CAPS,
			'name'     => 'hyphenate_all_caps',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::HYPHENATE_TITLE_CASE,
			'name'     => 'hyphenate_title_case',
			'default'  => true,
			'verify'   => 'is_bool',
		],
		[
			'property' => self::HYPHENATE_COMPOUNDS,
			'name'     => 'hyphenate_compounds',
			'default'  => true,
			'verify'   => 'is_bool',
		],
	];

	/**
	 * An index of virtual method names to properties.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,mixed>
	 */
	protected array $virtual_setters = [];

	/**
	 * An index of virtual property names data array indices.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,string>
	 */
	protected array $virtual_properties = [];

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
	public function __construct( bool $set_defaults = true, array $mapping = [ U::NO_BREAK_NARROW_SPACE => U::NO_BREAK_SPACE, U::APOSTROPHE => U::SINGLE_QUOTE_CLOSE ] ) { // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
		// Set up virtualized set_* methods.
		foreach ( self::VIRTUAL_PROPERTIES as $definition ) {
			// Add virtual setter method if none is defined.
			$setter = "set_{$definition['name']}";
			if ( isset( $definition['verify'] ) && ! \method_exists( $this, $setter ) ) {
				$this->virtual_setters[ $setter ] = $definition;
			}

			// Add virtual read-only properties.
			$this->virtual_properties[ $definition['name'] ] = $definition['property'];
		}

		if ( $set_defaults ) {
			$this->set_defaults();
		} else {
			$this->data[ self::CUSTOM_UNITS ] = '';
		}

		// Merge default character mapping with given mapping.
		$this->unicode_mapping = $mapping;
	}

	/**
	 * Provides virtual setter methods.
	 *
	 * @since 7.0.0
	 *
	 * @param  string  $name      The method name.
	 * @param  mixed[] $arguments The method arguments.
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException Throws an exception if an invalid method is called.
	 * @throws TypeError Throws an error if a given argument is of an incorrect type.
	 * @throws OutOfRangeException Throws an exception if a given integer argument is out of bounds.
	 */
	public function __call( string $name, array $arguments ) {
		if ( isset( $this->virtual_setters[ $name ] ) ) {
			$def   = $this->virtual_setters[ $name ];
			$value = isset( $arguments[0] ) ? $arguments[0] : $def['default'];

			// Check argument type.
			if ( ! $def['verify']( $value ) ) {
				throw new TypeError( "Argument for {$name} should be compatible with {$def['verify']}." );
			}

			// Check argument lower bounds.
			if ( isset( $def['min'] ) && $value < $def['min'] ) {
				throw new OutOfRangeException( "Argument for {$name} should not be less than {$def['min']}, {$value} given." );
			}

			// Check argument upper bounds.
			if ( isset( $def['max'] ) && $value > $def['max'] ) {
				throw new OutOfRangeException( "Argument for {$name} should be no more than {$def['max']}, {$value} given." );
			}

			$this->data[ $def['property'] ] = $value;
		} else {
			throw new BadMethodCallException( "Invalid method {$name} called." );
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
		if ( isset( $this->virtual_properties[ $key ] ) ) {
			return $this->data[ $this->virtual_properties[ $key ] ];
		}

		return $this->data[ $key ];
	}

	/**
	 * Changes a named setting (object syntax).
	 *
	 * @param string $key   The settings key.
	 * @param mixed  $value The settings value.
	 */
	public function __set( $key, $value ): void {
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
		if ( isset( $this->virtual_properties[ $key ] ) ) {
			return isset( $this->data[ $this->virtual_properties[ $key ] ] );
		}

		return isset( $this->data[ $key ] );
	}

	/**
	 * Unsets a named setting.
	 *
	 * @param string $key The settings key.
	 */
	public function __unset( $key ): void {
		unset( $this->data[ $key ] );
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
	public function remap_character( string $char, string $new_char ): void {
		if ( $char !== $new_char ) {
			$this->unicode_mapping[ $char ] = $new_char;
		} else {
			unset( $this->unicode_mapping[ $char ] );
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
	 * Retrieves the primary (double) quote style.
	 *
	 * @return Quotes
	 */
	public function primary_quote_style(): Quotes {
		return $this->primary_quote_style;
	}

	/**
	 * Retrieves the secondary (single) quote style.
	 *
	 * @return Quotes
	 */
	public function secondary_quote_style(): Quotes {
		return $this->secondary_quote_style;
	}

	/**
	 * Retrieves the dash style.
	 *
	 * @return Dashes
	 */
	public function dash_style(): Dashes {
		return $this->dash_style;
	}

	/**
	 * (Re)set various options to their default values.
	 */
	public function set_defaults(): void {
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
		$this->set_wrap_urls();
		$this->set_wrap_emails();
		$this->set_min_after_url_wrap();
		$this->set_space_collapse();

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
	 * Sets tags for which the typography of their children will be left untouched.
	 *
	 * @since 7.0.0 The parameter $tags can now only be passed as an array.
	 *
	 * @param string[] $tags An array of tag names.
	 */
	public function set_tags_to_ignore( array $tags = [ 'code', 'head', 'kbd', 'object', 'option', 'pre', 'samp', 'script', 'noscript', 'noembed', 'select', 'style', 'textarea', 'title', 'var', 'math' ] ): void {
		// Ensure that we pass only lower-case tag names to XPath.
		$tags = array_filter( array_map( 'strtolower', $tags ), 'ctype_alnum' );

		$this->data[ self::IGNORE_TAGS ] = array_unique( array_merge( $tags, array_flip( DOM::inappropriate_tags() ) ) );
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
	public function set_smart_quotes_primary( $style = Quote_Style::DOUBLE_CURLED ): void {
		$this->primary_quote_style = Quote_Style::get_styled_quotes( $style );
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
	public function set_smart_quotes_secondary( $style = Quote_Style::SINGLE_CURLED ): void {
		$this->secondary_quote_style = Quote_Style::get_styled_quotes( $style );
	}

	/**
	 * Sets the list of exceptional words for smart quotes replacement. Mainly,
	 * this is used for contractions beginning with an apostrophe.
	 *
	 * @param string[] $exceptions Optional. An array of replacements indexed by the ”non-smart" form.
	 *                             Default a list of English words beginning with an apostrophy.
	 */
	public function set_smart_quotes_exceptions( array $exceptions = [
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
	] ): void {
		$this->data[ self::SMART_QUOTES_EXCEPTIONS ] = [
			'patterns'     => \array_keys( $exceptions ),
			'replacements' => \array_values( $exceptions ),
		];
	}

	/**
	 * Sets the typographical conventions used by smart_dashes.
	 *
	 * Allowed values for $style:
	 * - "traditionalUS"
	 * - "international"
	 *
	 * @param string|Dashes $style Optional. Default Dash_Style::TRADITIONAL_US.
	 *
	 * @throws \DomainException Thrown if $style constant is invalid.
	 */
	public function set_smart_dashes_style( $style = Dash_Style::TRADITIONAL_US ): void {
		$this->dash_style = Dash_Style::get_styled_dashes( $style );
	}

	/**
	 * Sets the language used for diacritics replacements.
	 *
	 * @param string $lang Has to correspond to a filename in 'diacritics'. Optional. Default 'en-US'.
	 */
	public function set_diacritic_language( string $lang = 'en-US' ): void {
		if ( isset( $this->data[ self::DIACRITIC_LANGUAGE ] ) && $this->data[ self::DIACRITIC_LANGUAGE ] === $lang ) {
			return;
		}

		$this->data[ self::DIACRITIC_LANGUAGE ] = $lang;
		$language_file_name                     = __DIR__ . '/diacritics/' . $lang . '.json';
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
	 * @since 7.0.0 The parameter $units can now only be passed as an array.
	 *
	 * @param array<string,string> $custom_replacements An array formatted [needle=>replacement, needle=>replacement...].
	 */
	public function set_diacritic_custom_replacements( array $custom_replacements = [] ): void {
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
	 * Update the pattern and replacement arrays in $settings['diacriticReplacement'].
	 *
	 * Should be called whenever a new diacritics replacement language is selected or
	 * when the custom replacements are updated.
	 */
	private function update_diacritics_replacement_arrays(): void {
		$patterns     = [];
		$replacements = [];

		/**
		 * Parse an array of diacritics rules.
		 */
		$parse_rules = function ( array $rules ) use ( &$patterns, &$replacements ): void {
			foreach ( $rules as $needle => $replacement ) {
				$patterns[]              = '/\b(?<!\w[' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . '])' . $needle . '\b(?![' . U::NO_BREAK_SPACE . U::SOFT_HYPHEN . ']\w)/u';
				$replacements[ $needle ] = $replacement;
			}
		};

		// First parse included diacritic replaments ...
		if ( ! empty( $this->data[ self::DIACRITIC_WORDS ] ) ) {
			$parse_rules( $this->data[ self::DIACRITIC_WORDS ] );
		}

		// ... then custom replacements to allow them to override the standard ones.
		if ( ! empty( $this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ] ) ) {
			$parse_rules( $this->data[ self::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		}

		$this->data[ self::DIACRITIC_REPLACEMENT_DATA ] = [
			'patterns'     => $patterns,
			'replacements' => $replacements,
		];
	}

	/**
	 * Sets the list of units to keep together with their values.
	 *
	 * @since 7.0.0 The parameter $units can now only be passed as an array.
	 *
	 * @param string[] $units An array of unit names.
	 */
	public function set_units( array $units = [] ): void {
		$this->data[ self::CUSTOM_UNITS ] = '';

		foreach ( $units as $unit ) {
			$this->data[ self::CUSTOM_UNITS ] .= \preg_quote( $unit, '/' ) . '|';
		}
	}

	/**
	 * Sets the list of tags where initial quotes and guillemets should be styled.
	 *
	 * @since 7.0.0 The parameter $tags can now only be passed as an array.
	 *
	 * @param string[] $tags An array of tag names.
	 */
	public function set_initial_quote_tags( array $tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'li', 'dd', 'dt' ] ): void {
		// Store the tag array inverted (with the tagName as its index for faster lookup).
		$this->data[ self::INITIAL_QUOTE_TAGS ] = \array_change_key_case( \array_flip( $tags ), \CASE_LOWER );
	}

	/**
	 * Retrieves a unique hash value for the current settings.
	 *
	 * @since 5.2.0 The new parameter $raw_output has been added.
	 * @since 7.0.0 The default value of $max_length has been increased to 64.
	 *              The parameter $raw_output has been renamed to $binary and it now defaults to `false`.
	 *
	 * @param int  $max_length Optional. The maximum number of bytes returned (0 for unlimited). Default 64.
	 * @param bool $binary     Optional. Whether to return raw binary data for the hash. Default false.
	 *
	 * @return string A binary hash value for the current settings limited to $max_length.
	 */
	public function get_hash( int $max_length = 64, bool $binary = false ): string {
		$hash = \hash( 'sha256', (string) \json_encode( $this ), $binary );

		if ( $max_length < \strlen( $hash ) && $max_length > 0 ) {
			$hash = \substr( $hash, 0, $max_length );
		}

		return $hash;
	}
}
