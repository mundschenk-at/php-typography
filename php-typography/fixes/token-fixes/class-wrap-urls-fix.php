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

namespace PHP_Typography\Fixes\Token_Fixes;

use \PHP_Typography\Fixes\Token_Fix;
use \PHP_Typography\Settings;
use \PHP_Typography\Text_Parser;
use \PHP_Typography\U;

/**
 * Wraps URL parts zero-width spaces (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Wrap_URLs_Fix extends Hyphenate_Fix {

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( Token_Fix::OTHER, $feed_compatible );
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param array         $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return array An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings['urlWrap'] ) || empty( $settings['urlMinAfterWrap'] ) ) {
			return $tokens;
		}

		// Various special characters and regular expressions.
		$regex = $settings->get_regular_expressions();

		// Test for and parse urls.
		foreach ( $tokens as $token_index => $text_token ) {
			if ( preg_match( $regex['wrapUrlsPattern'], $text_token->value, $url_match ) ) {

				// $url_match['schema'] holds "http://".
				// $url_match['domain'] holds "subdomains.domain.tld".
				// $url_match['path']   holds the path after the domain.
				$http = ( $url_match['schema'] ) ? $url_match[1] . U::ZERO_WIDTH_SPACE : '';

				$domain_parts = preg_split( $regex['wrapUrlsDomainParts'], $url_match['domain'], -1, PREG_SPLIT_DELIM_CAPTURE );

				// This is a hack, but it works.
				// First, we hyphenate each part, we need it formated like a group of words.
				$parsed_words_like = [];
				foreach ( $domain_parts as $key => $part ) {
					$parsed_words_like[ $key ] = new Text_Parser\Token( $part, Text_Parser\Token::OTHER );
				}

				// Do the hyphenation.
				$parsed_words_like = $this->do_hyphenate( $parsed_words_like, $settings, U::ZERO_WIDTH_SPACE );

				// Restore format.
				foreach ( $parsed_words_like as $key => $parsed_word ) {
					$value = $parsed_word->value;

					if ( $key > 0 && 1 === strlen( $value ) ) {
						$domain_parts[ $key ] = U::ZERO_WIDTH_SPACE . $value;
					} else {
						$domain_parts[ $key ] = $value;
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
						$path .= U::ZERO_WIDTH_SPACE . $path_part;
					}
				}

				$tokens[ $token_index ] = $text_token->with_value( $http . $domain . $path );
			}
		}

		return $tokens;
	}
}
