<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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
		$double     = $settings->primary_quote_style();
		$single     = $settings->secondary_quote_style();
		$regex      = $settings->get_regular_expressions();
		$components = $settings->get_components();

		// Before primes, handle quoted numbers (and quotes ending in numbers).
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuotedNumbers'], "{$single->open()}\$1{$single->close()}", $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuotedNumbers'], "{$double->open()}\$1{$double->close()}", $textnode->data );

		// Guillemets.
		$textnode->data = str_replace( '<<',       U::GUILLEMET_OPEN,  $textnode->data );
		$textnode->data = str_replace( '&lt;&lt;', U::GUILLEMET_OPEN,  $textnode->data );
		$textnode->data = str_replace( '>>',       U::GUILLEMET_CLOSE, $textnode->data );
		$textnode->data = str_replace( '&gt;&gt;', U::GUILLEMET_CLOSE, $textnode->data );

		// Primes.
		$textnode->data = preg_replace( $regex['smartQuotesSingleDoublePrime'],         '$1' . U::SINGLE_PRIME . '$2$3' . U::DOUBLE_PRIME, $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleDoublePrime1Glyph'],   '$1' . U::SINGLE_PRIME . '$2$3' . U::DOUBLE_PRIME, $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime'],               '$1' . U::DOUBLE_PRIME,                            $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( $regex['smartQuotesSinglePrime'],               '$1' . U::SINGLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSinglePrimeCompound'],       '$1' . U::SINGLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrimeCompound'],       '$1' . U::DOUBLE_PRIME,                            $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime1Glyph'],         '$1' . U::DOUBLE_PRIME,                            $textnode->data ); // should not interfere with regular quote matching.
		$textnode->data = preg_replace( $regex['smartQuotesDoublePrime1GlyphCompound'], '$1' . U::DOUBLE_PRIME,                            $textnode->data );

		// Backticks.
		$textnode->data = str_replace( '``', $double->open(),  $textnode->data );
		$textnode->data = str_replace( '`',  $single->open(),  $textnode->data );
		$textnode->data = str_replace( "''", $double->close(), $textnode->data );

		// Comma quotes.
		$textnode->data = str_replace( ',,', U::DOUBLE_LOW_9_QUOTE, $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesCommaQuote'], U::SINGLE_LOW_9_QUOTE, $textnode->data ); // like _,¿hola?'_.

		// Apostrophes.
		$textnode->data = preg_replace( $regex['smartQuotesApostropheWords'],   U::APOSTROPHE,        $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesApostropheDecades'], U::APOSTROPHE . '$1', $textnode->data ); // decades: '98.
		$textnode->data = str_replace( $components['smartQuotesApostropheExceptionMatches'], $components['smartQuotesApostropheExceptionReplacements'], $textnode->data );

		// Quotes.
		$textnode->data = str_replace( $components['smartQuotesBracketMatches'], $components['smartQuotesBracketReplacements'], $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteOpen'],         $single->open(),  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteClose'],        $single->close(), $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteOpenSpecial'],  $single->open(),  $textnode->data ); // like _'¿hola?'_.
		$textnode->data = preg_replace( $regex['smartQuotesSingleQuoteCloseSpecial'], $single->close(), $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteOpen'],         $double->open(),  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteClose'],        $double->close(), $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteOpenSpecial'],  $double->open(),  $textnode->data );
		$textnode->data = preg_replace( $regex['smartQuotesDoubleQuoteCloseSpecial'], $double->close(), $textnode->data );

		// Quote catch-alls - assume left over quotes are closing - as this is often the most complicated position, thus most likely to be missed.
		$textnode->data = str_replace( "'", $single->close(), $textnode->data );
		$textnode->data = str_replace( '"', $double->close(), $textnode->data );

		// If we have adjacent characters remove them from the text.
		$textnode->data = self::remove_adjacent_characters( $textnode->data, $previous_character, $next_character );
	}
}
