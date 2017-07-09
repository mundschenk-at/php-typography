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

use \PHP_Typography\Settings;
use \PHP_Typography\DOM;

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
		$textnode->data = self::remove_adjacent_characters( $textnode->data, $previous_character, $next_character );
	}
}
