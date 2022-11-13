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

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Hyphenator\Cache;
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser;
use PHP_Typography\Text_Parser\Token;
use PHP_Typography\U;

/**
 * Wraps URL parts zero-width spaces (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Wrap_URLs_Fix extends Hyphenate_Fix {
	// Valid URL schemes.
	const URL_SCHEME = '(?:https?|ftps?|file|nfs|feed|itms|itpc)';

	const WRAP_URLS_DOMAIN_PARTS = '#(\-|\.)#';

	/**
	 * The URL matching regular expression.
	 *
	 * @var string
	 */
	protected $url_pattern;

	/**
	 * Creates a new fix instance.
	 *
	 * @param Cache|null $cache           Optional. Default null.
	 * @param bool       $feed_compatible Optional. Default false.
	 */
	public function __construct( Cache $cache = null, $feed_compatible = false ) {
		parent::__construct( $cache, Token_Fix::OTHER, $feed_compatible );

		// Combined URL pattern.
		$this->url_pattern = '`(?:
			\A
			(?<scheme>' . self::URL_SCHEME . ':\/\/)?	        # Subpattern 1: contains _http://_ if it exists
			(?<domain>											# Subpattern 2: contains subdomains.domain.tld
				(?:
					[a-z0-9]									# first chr of (sub)domain can not be a hyphen
					[a-z0-9\-]{0,61}							# middle chrs of (sub)domain may be a hyphen;
																# limit qty of middle chrs so total domain does not exceed 63 chrs
					[a-z0-9]									# last chr of (sub)domain can not be a hyphen
					\.											# dot separator
				)+
				(?:
					' . RE::top_level_domains() . '             # validates top level domain
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
		)`xi'; // required modifiers: x (multiline pattern) i (case insensitive).
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings The settings to apply.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings[ Settings::URL_WRAP ] ) || empty( $settings[ Settings::URL_MIN_AFTER_WRAP ] ) ) {
			return $tokens;
		}

		// Test for and parse urls.
		foreach ( $tokens as $token_index => $text_token ) {
			if ( \preg_match( $this->url_pattern, $text_token->value, $url_match ) ) {
				/**
				 * The URL is split into three parts:
				 *  - $url_match['scheme'] holds "http://".
				 *  - $url_match['domain'] holds "subdomains.domain.tld".
				 *  - $url_match['path']   holds the path after the domain.
				 *
				 * @var array{ 'scheme' : string, 'domain' : string,'path' : string } $url_match
				 */

				$scheme = ! empty( $url_match['scheme'] ) ? $url_match['scheme'] . U::ZERO_WIDTH_SPACE : '';
				$domain = $this->split_domain( $url_match['domain'], $settings );
				$path   = $this->split_path( $url_match['path'], $settings );

				$tokens[ $token_index ] = $text_token->with_value( $scheme . $domain . $path );
			}
		}

		return $tokens;
	}

	/**
	 * Splits the given domain name.
	 *
	 * @since  6.7.0
	 *
	 * @param  string   $domain   A domain/host name.
	 * @param  Settings $settings The settings to apply.
	 *
	 * @return string             The hyphenated domain name.
	 */
	private function split_domain( string $domain, Settings $settings ) : string {
		$domain_parts = \preg_split( self::WRAP_URLS_DOMAIN_PARTS, $domain, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( false === $domain_parts ) {
			// Should not happen.
			return $domain;  // @codeCoverageIgnore
		}

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

			if ( $key > 0 && 1 === \strlen( $value ) ) {
				$domain_parts[ $key ] = U::ZERO_WIDTH_SPACE . $value;
			} else {
				$domain_parts[ $key ] = $value;
			}
		}

		// Lastly let's recombine.
		return \implode( '', $domain_parts );
	}

	/**
	 * Splits the given URL path.
	 *
	 * @since  6.7.0
	 *
	 * @param  string   $path     A URL path.
	 * @param  Settings $settings The settings to apply.
	 *
	 * @return string             The hyphenated domain name.
	 */
	private function split_path( string $path, Settings $settings ) : string {
		// Break up the URL path to individual characters.
		$path_parts = \str_split( $path, 1 ); // TODO: Does not work with non-ASCII paths.
		$path_count = \count( $path_parts );
		$split_path = '';
		foreach ( $path_parts as $index => $part ) {
			if ( 0 === $index || $path_count - $index < $settings[ Settings::URL_MIN_AFTER_WRAP ] ) {
				$split_path .= $part;
			} else {
				$split_path .= U::ZERO_WIDTH_SPACE . $part;
			}
		}

		return $split_path;
	}
}
