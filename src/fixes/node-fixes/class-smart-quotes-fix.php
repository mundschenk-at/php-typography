<?php
/**
 *  This file is part of PHP-Typography.
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
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\DOM;
use \PHP_Typography\Settings;
use \PHP_Typography\U;

/**
 * Applies smart quotes (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Quotes_Fix extends Abstract_Node_Fix {

	const APOSTROPHE_EXCEPTIONS = [
		"'tain" . U::APOSTROPHE . 't' => U::APOSTROPHE . 'tain' . U::APOSTROPHE . 't',
		"'twere"                      => U::APOSTROPHE . 'twere',
		"'twas"                       => U::APOSTROPHE . 'twas',
		"'tis"                        => U::APOSTROPHE . 'tis',
		"'til"                        => U::APOSTROPHE . 'til',
		"'bout"                       => U::APOSTROPHE . 'bout',
		"'nuff"                       => U::APOSTROPHE . 'nuff',
		"'round"                      => U::APOSTROPHE . 'round',
		"'cause"                      => U::APOSTROPHE . 'cause',
		"'splainin"                   => U::APOSTROPHE . 'splainin',
	];

	const NUMBERS_BEFORE_PRIME = '\b(?:\d+\/)?\d{1,3}';

	const DOUBLE_PRIME                  = '/(' . self::NUMBERS_BEFORE_PRIME . ")''(?=\W|\Z)/u";
	const DOUBLE_PRIME_COMPOUND         = '/(' . self::NUMBERS_BEFORE_PRIME . ")''(?=-\w)/u";
	const DOUBLE_PRIME_1_GLYPH          = '/(' . self::NUMBERS_BEFORE_PRIME . ')"(?=\W|\Z)/u';
	const DOUBLE_PRIME_1_GLYPH_COMPOUND = '/(' . self::NUMBERS_BEFORE_PRIME . ')"(?=-\w)/u';
	const SINGLE_PRIME                  = '/(' . self::NUMBERS_BEFORE_PRIME . ")'(?=\W|\Z)/u";
	const SINGLE_PRIME_COMPOUND         = '/(' . self::NUMBERS_BEFORE_PRIME . ")'(?=-\w)/u";
	const SINGLE_DOUBLE_PRIME           = '/(' . self::NUMBERS_BEFORE_PRIME . ")'(\s*)(\b(?:\d+\/)?\d+)''(?=\W|\Z)/u";
	const SINGLE_DOUBLE_PRIME_1_GLYPH   = '/(' . self::NUMBERS_BEFORE_PRIME . ")'(\s*)(\b(?:\d+\/)?\d+)\"(?=\W|\Z)/u";

	const SINGLE_QUOTED_NUMBERS      = "/(?<=\W|\A)'([^\"]*\d+)'(?=\W|\Z)/u";
	const DOUBLE_QUOTED_NUMBERS      = '/(?<=\W|\A)"([^"]*\d+)"(?=\W|\Z)/u';
	const COMMA_QUOTE                = '/(?<=\s|\A),(?=\S)/';
	const APOSTROPHE_WORDS           = "/(?<=[\w])'(?=[\w])/u";
	const APOSTROPHE_DECADES         = "/'(\d\d\b)/";
	const SINGLE_QUOTE_OPEN          = "/'(?=[\w])/u";
	const SINGLE_QUOTE_CLOSE         = "/(?<=[\w])'/u";
	const SINGLE_QUOTE_OPEN_SPECIAL  = "/(?<=\s|\A)'(?=\S)/"; // like _'¿hola?'_.
	const SINGLE_QUOTE_CLOSE_SPECIAL = "/(?<=\S)'(?=\s|\Z)/";
	const DOUBLE_QUOTE_OPEN          = '/"(?=[\w])/u';
	const DOUBLE_QUOTE_CLOSE         = '/(?<=[\w])"/u';
	const DOUBLE_QUOTE_OPEN_SPECIAL  = '/(?<=\s|\A)"(?=\S)/';
	const DOUBLE_QUOTE_CLOSE_SPECIAL = '/(?<=\S)"(?=\s|\Z)/';


	/**
	 * Apostrophe exceptions matching array.
	 *
	 * @var array
	 */
	protected $apostrophe_exception_matches;

	/**
	 * Apostrophe exceptions replacement array.
	 *
	 * @var array
	 */
	protected $apostrophe_exception_replacements;

	/**
	 * Cached primary quote style.
	 *
	 * @var \PHP_Typography\Settings\Quotes|null
	 */
	protected $cached_primary_quotes;

	/**
	 * Cached secondary quote style.
	 *
	 * @var \PHP_Typography\Settings\Quotes|null
	 */
	protected $cached_secondary_quotes;

	/**
	 * Brackets matching array (depending on quote styles).
	 *
	 * @var array
	 */
	protected $brackets_matches;

	/**
	 * Brackets replacement array (depending on quote styles).
	 *
	 * @var array
	 */
	protected $brackets_replacements;

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		$this->apostrophe_exception_matches      = array_keys( self::APOSTROPHE_EXCEPTIONS );
		$this->apostrophe_exception_replacements = array_values( self::APOSTROPHE_EXCEPTIONS );
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['smartQuotes'] ) ) {
			return;
		}

		// Need to get context of adjacent characters outside adjacent inline tags or HTML comment
		// if we have adjacent characters add them to the text.
		$previous_character = DOM::get_prev_chr( $textnode );
		if ( '' !== $previous_character ) {
			$textnode->data = $previous_character . $textnode->data;
		}
		$next_character = DOM::get_next_chr( $textnode );
		if ( '' !== $next_character ) {
			$textnode->data = $textnode->data . $next_character;
		}

		// Various special characters and regular expressions.
		$double = $settings->primary_quote_style();
		$single = $settings->secondary_quote_style();

		$double_open  = $double->open();
		$double_close = $double->close();
		$single_open  = $single->open();
		$single_close = $single->close();

		if ( $double != $this->cached_primary_quotes || $single != $this->cached_secondary_quotes ) {
			$this->update_smart_quotes_brackets( $double_open, $double_close, $single_open, $single_close );
			$this->cached_primary_quotes   = $double;
			$this->cached_secondary_quotes = $single;
		}

		// Before primes, handle quoted numbers (and quotes ending in numbers).
		$textnode->data = preg_replace( self::SINGLE_QUOTED_NUMBERS, "{$single_open}\$1{$single_close}", $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_QUOTED_NUMBERS, "{$double_open}\$1{$double_close}", $textnode->data );

		// Guillemets.
		$textnode->data = str_replace( '<<',       U::GUILLEMET_OPEN,  $textnode->data );
		$textnode->data = str_replace( '&lt;&lt;', U::GUILLEMET_OPEN,  $textnode->data );
		$textnode->data = str_replace( '>>',       U::GUILLEMET_CLOSE, $textnode->data );
		$textnode->data = str_replace( '&gt;&gt;', U::GUILLEMET_CLOSE, $textnode->data );

		// Primes.
		$textnode->data = preg_replace( self::SINGLE_DOUBLE_PRIME,           '$1' . U::SINGLE_PRIME . '$2$3' . U::DOUBLE_PRIME, $textnode->data );
		$textnode->data = preg_replace( self::SINGLE_DOUBLE_PRIME_1_GLYPH,   '$1' . U::SINGLE_PRIME . '$2$3' . U::DOUBLE_PRIME, $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_PRIME,                  '$1' . U::DOUBLE_PRIME,                            $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( self::SINGLE_PRIME,                  '$1' . U::SINGLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( self::SINGLE_PRIME_COMPOUND,         '$1' . U::SINGLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_PRIME_COMPOUND,         '$1' . U::DOUBLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_PRIME_1_GLYPH,          '$1' . U::DOUBLE_PRIME,                            $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( self::DOUBLE_PRIME_1_GLYPH_COMPOUND, '$1' . U::DOUBLE_PRIME,                            $textnode->data );

		// Backticks.
		$textnode->data = str_replace( '``', $double_open,  $textnode->data );
		$textnode->data = str_replace( '`',  $single_open,  $textnode->data );
		$textnode->data = str_replace( "''", $double_close, $textnode->data );

		// Comma quotes.
		$textnode->data = str_replace( ',,', U::DOUBLE_LOW_9_QUOTE, $textnode->data );
		$textnode->data = preg_replace( self::COMMA_QUOTE, U::SINGLE_LOW_9_QUOTE, $textnode->data ); // like _,¿hola?'_.

		// Apostrophes.
		$textnode->data = preg_replace( self::APOSTROPHE_WORDS,   U::APOSTROPHE,        $textnode->data );
		$textnode->data = preg_replace( self::APOSTROPHE_DECADES, U::APOSTROPHE . '$1', $textnode->data ); // decades: '98.
		$textnode->data = str_replace( $this->apostrophe_exception_matches, $this->apostrophe_exception_replacements, $textnode->data );

		// Quotes.
		$textnode->data = str_replace( $this->brackets_matches, $this->brackets_replacements, $textnode->data );
		$textnode->data = preg_replace( self::SINGLE_QUOTE_OPEN,          $single_open,  $textnode->data );
		$textnode->data = preg_replace( self::SINGLE_QUOTE_CLOSE,         $single_close, $textnode->data );
		$textnode->data = preg_replace( self::SINGLE_QUOTE_OPEN_SPECIAL,  $single_open,  $textnode->data ); // like _'¿hola?'_.
		$textnode->data = preg_replace( self::SINGLE_QUOTE_CLOSE_SPECIAL, $single_close, $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_QUOTE_OPEN,          $double_open,  $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_QUOTE_CLOSE,         $double_close, $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_QUOTE_OPEN_SPECIAL,  $double_open,  $textnode->data );
		$textnode->data = preg_replace( self::DOUBLE_QUOTE_CLOSE_SPECIAL, $double_close, $textnode->data );

		// Quote catch-alls - assume left over quotes are closing - as this is often the most complicated position, thus most likely to be missed.
		$textnode->data = str_replace( "'", $single_close, $textnode->data );
		$textnode->data = str_replace( '"', $double_close, $textnode->data );

		// If we have adjacent characters remove them from the text.
		$textnode->data = self::remove_adjacent_characters( $textnode->data, $previous_character, $next_character );
	}

	/**
	 * Update smartQuotesBrackets component after quote style change.
	 *
	 * @param  string $primary_open    Primary quote style open.
	 * @param  string $primary_close   Primary quote style close.
	 * @param  string $secondary_open  Secondary quote style open.
	 * @param  string $secondary_close Secondary quote style close.
	 */
	private function update_smart_quotes_brackets( $primary_open, $primary_close, $secondary_open, $secondary_close ) {
		$brackets = [
			// Single quotes.
			"['"  => '[' . $secondary_open,
			"{'"  => '{' . $secondary_open,
			"('"  => '(' . $secondary_open,
			"']"  => $secondary_close . ']',
			"'}"  => $secondary_close . '}',
			"')"  => $secondary_close . ')',

			// Double quotes.
			'["'  => '[' . $primary_open,
			'{"'  => '{' . $primary_open,
			'("'  => '(' . $primary_open,
			'"]'  => $primary_close . ']',
			'"}'  => $primary_close . '}',
			'")'  => $primary_close . ')',

			// Quotes & quotes.
			"\"'" => $primary_open . $secondary_open,
			"'\"" => $secondary_close . $primary_close,
		];

		$this->brackets_matches      = array_keys( $brackets );
		$this->brackets_replacements = array_values( $brackets );
	}
}
