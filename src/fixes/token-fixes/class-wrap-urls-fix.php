<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2026 Peter Putzer.
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
use PHP_Typography\Settings;
use PHP_Typography\Strings;
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
	// Constants from the URI and IRI specifications.
	const URI_SCHEME = '(?:[a-z][\-a-z0-9\+\.]*)';
	const URI_PORT   = '[0-9]*';

	const URI_HEX_DIGIT    = '0-9a-f';
	const URI_SUB_DELIMS   = '!\$&\'\(\)\*\+,;=';
	const URI_GEN_DELIMS   = ':/\?#\[\]@';
	const URI_RESERVED     = self::URI_SUB_DELIMS . self::URI_GEN_DELIMS;
	const URI_UNRESERVED   = 'a-z0-9\-\._~';
	const URI_PCT_ENCODED  = '%[' . self::URI_HEX_DIGIT . '][' . self::URI_HEX_DIGIT . ']';
	const URI_DEC_OCTET    = '[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]'; // 0-255
	const URI_IPV4_ADDRESS = self::URI_DEC_OCTET . '(?:\.' . self::URI_DEC_OCTET . '){3}';
	const URI_H16          = '[' . self::URI_HEX_DIGIT . ']{1,4}';
	const URI_LS32         = '(?:' . self::URI_H16 . ':' . self::URI_H16 . '|' . self::URI_IPV4_ADDRESS . ')';
	const URI_H16_PART     = '(?:' . self::URI_H16 . ':)';
	const URI_IPV6_ADDRESS = '(?:' .
			self::URI_H16_PART . '{6}' . self::URI_LS32 . '|' .
			'::' . self::URI_H16_PART . '{5}' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16 . '?)::' . self::URI_H16_PART . '{4}' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16_PART . self::URI_H16 . '?)::' . self::URI_H16_PART . '{3}' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16_PART . '{0,2}' . self::URI_H16 . '?)::' . self::URI_H16_PART . '{2}' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16_PART . '{0,3}' . self::URI_H16 . '?)::' . self::URI_H16_PART . '{1}' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16_PART . '{0,4}' . self::URI_H16 . '?)::' . self::URI_LS32 . '|' .
			'(?:' . self::URI_H16_PART . '{0,5}' . self::URI_H16 . '?)::' . self::URI_H16 . '|' .
			'(?:' . self::URI_H16_PART . '{0,6}' . self::URI_H16 . '?)::' .
		')';
	const URI_IPV_FUTURE   = 'v[' . self::URI_HEX_DIGIT . ']+\.(?:' . self::URI_UNRESERVED . self::URI_SUB_DELIMS . ':)+';
	const URI_IP_LITERAL   = '\[(?:' . self::URI_IPV6_ADDRESS . '|' . self::URI_IPV_FUTURE . ')\]';

	/**
	 * IRI_UCS_CHAR should be:
	 *    '\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}' .
	 *    '\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}' .
	 *    '\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}' .
	 *    '\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}' .
	 *    '\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}' .
	 *    '\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}'
	 * according to the specification, but that pattern somehow stops
	 * PCRE2 engine from correctly matching certain strings. Instead
	 * we use a simpler range that includes a few non-character
	 * code points that should be excluded, but probably won't make
	 * meaningful difference in the real world.
	 */
	const IRI_UCS_CHAR   = '\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{DFFFD}\x{E1000}-\x{EFFFD}';
	const IRI_PRIVATE    = '\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}\x{100000}-\x{10FFFD}';
	const IRI_UNRESERVED = 'a-z0-9\-\._~' . self::IRI_UCS_CHAR;

	const IRI_PCHAR         = '(?:' . self::URI_PCT_ENCODED . '|[' . self::IRI_UNRESERVED . self::URI_SUB_DELIMS . ':@])';
	const IRI_QUERY         = '(?:' . self::IRI_PCHAR . '|[' . self::IRI_PRIVATE . '\/\?])*';
	const IRI_FRAGMENT      = '(?:' . self::IRI_PCHAR . '|[\/\?])*';
	const IRI_SEGMENT       = self::IRI_PCHAR . '*';
	const IRI_SEGMENT_NZ    = self::IRI_PCHAR . '+';
	const IRI_SEGMENT_NZ_NC = '(?:' . self::URI_PCT_ENCODED . '|[' . self::IRI_UNRESERVED . self::URI_SUB_DELIMS . '@])+'; // non-zero-length segment without any colon ":".

	const IRI_PATH_ABEMPTY  = '(?:\/' . self::IRI_SEGMENT . ')*';
	const IRI_PATH_ABSOLUTE = '\/(?:(?:' . self::IRI_SEGMENT_NZ . ')(?:\/' . self::IRI_SEGMENT . ')*)?';
	const IRI_PATH_NOSCHEME = self::IRI_SEGMENT_NZ_NC . '(?:\\/' . self::IRI_SEGMENT . ')*';
	const IRI_PATH_ROOTLESS = self::IRI_SEGMENT_NZ . '(?:\\/' . self::IRI_SEGMENT . ')*';
	const IRI_PATH_EMPTY    = '(?!' . self::IRI_PCHAR . ')';

	const IRI_PATH = '(?:' .
		self::IRI_PATH_ABEMPTY . '|' . // begins with "/" or is empty.
		self::IRI_PATH_ABSOLUTE . '|' . // begins with "/" but not "//".
		self::IRI_PATH_NOSCHEME . '|' . // begins with a non-colon segment.
		self::IRI_PATH_ROOTLESS . '|' . // begins with a segment.
		self::IRI_PATH_EMPTY . // zero characters.
		')';

	const IRI_REG_NAME  = '(?:' . self::URI_PCT_ENCODED . '|[' . self::IRI_UNRESERVED . self::URI_SUB_DELIMS . '])*';
	const IRI_HOST      = '(?:' . self::URI_IP_LITERAL . '|' . self::URI_IPV4_ADDRESS . '|' . self::IRI_REG_NAME . ')';
	const IRI_USER_INFO = '(?:' . self::URI_PCT_ENCODED . '|[' . self::IRI_UNRESERVED . self::URI_SUB_DELIMS . ':])*';
	const IRI_AUTHORITY = '(?:' . self::IRI_USER_INFO . '@)?' . self::IRI_HOST . '(?::' . self::URI_PORT . ')?';

	const IRI_RELATIVE_PART = '(?:' .
		'\/\/' . self::IRI_AUTHORITY . self::IRI_PATH_ABEMPTY . '|' .
		self::IRI_PATH_ABSOLUTE . '|' .
		self::IRI_PATH_NOSCHEME . '|' .
		self::IRI_PATH_EMPTY .
		')';
	const IRI_HIER_PART     = '(?:' .
		'\/\/' . self::IRI_AUTHORITY . self::IRI_PATH_ABEMPTY . '|' .
		self::IRI_PATH_ABSOLUTE . '|' .
		self::IRI_PATH_ROOTLESS . '|' .
		self::IRI_PATH_EMPTY .
		')';
	const IRI_RELATIVE_REF  = self::IRI_RELATIVE_PART . '(?:?' . self::IRI_QUERY . ')?(?:#' . self::IRI_FRAGMENT . ')?';
	const IRI               = self::URI_SCHEME . ':' . self::IRI_HIER_PART . '(?:\?' . self::IRI_QUERY . ')?(?:#' . self::IRI_FRAGMENT . ')?';
	const IRI_REFERENCE     = '(?:' . self::IRI . ')|(?:' . self::IRI_RELATIVE_REF . ')';
	const IRI_ABSOLUTE_IRI  = self::URI_SCHEME . ':' . self::IRI_HIER_PART . '(?:\?' . self::IRI_QUERY . ')?';

	// Patterns for this fix.
	const DOMAIN_PARTS_PATTERN = '#(\-|\.)#';
	const IRI_PATTERN          = '`(?:
			\A
			(?<scheme>' . self::URI_SCHEME . ':\/\/)?	        # Subpattern 1: optional scheme ("https://")
			(?<userinfo>' . self::IRI_USER_INFO . '@)?          # Subpattern 2: optional username ("user@")
			(?<domain>' . self::IRI_HOST . ')                   # Subpattern 3: host/domain name ("subdomains.domain.tld")
			(?<port>:' . self::URI_PORT . ')?                   # Subpattern 4: optional port number (":8080")
			(?<path>' . self::IRI_PATH_ABEMPTY . ')             # Subpattern 5: path ("/some/path", non-optional, but can be empty)
			(?<query>\?' . self::IRI_QUERY . ')?                # Subpattern 6: optional query string ("?some=query")
			(?<fragment>\#' . self::IRI_FRAGMENT . ')?          # Subpattern 7: optional fragment ID ("#fragment")
			\Z
		)`xui'; // required modifiers: x (multiline pattern) i (case insensitive).

	/**
	 * Creates a new fix instance.
	 *
	 * @param Cache|null $cache           Optional. Default null.
	 * @param bool       $feed_compatible Optional. Default false.
	 */
	public function __construct( ?Cache $cache = null, $feed_compatible = false ) {
		parent::__construct( $cache, Token_Fix::OTHER, $feed_compatible );
	}

	/**
	 * Apply the fix to a given set of tokens
	 *
	 * @since 7.0.0 The parameter order has been re-arranged to mirror Node_Fix.
	 *
	 * @param Token[]  $tokens   The set of tokens.
	 * @param \DOMText $textnode The context DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return Token[]           The fixed set of tokens.
	 */
	public function apply( array $tokens, \DOMText $textnode, Settings $settings, $is_title ) {
		if ( empty( $settings->wrap_urls ) || empty( $settings->min_after_url_wrap ) ) {
			return $tokens;
		}

		// Test for and parse IRIs.
		foreach ( $tokens as $token_index => $text_token ) {
			if ( \preg_match( self::IRI_PATTERN, $text_token->value, $url_match ) ) {
				/**
				 * The URL is split into seven parts:
				 *  - $url_match['scheme']   holds "http://".
				 *  - $url_match['userinfo'] holds "foo@".
				 *  - $url_match['domain']   holds "subdomains.domain.tld".
				 *  - $url_match['port'].    holds ":8080".
				 *  - $url_match['path']     holds "/some/path".
				 *  - $url_match['query]     holds "?foo=bar".
				 *  - $url_match['fragment]  holds "#frag".
				 */

				// Bail early if this is an e-mail address.
				if ( $this->is_email_address( $url_match ) ) {
					$tokens[ $token_index ] = $text_token;
					continue;
				}

				$tokens[ $token_index ] = $text_token->with_value( $this->wrap_iri( $url_match, $settings ) );
			}
		}

		return $tokens;
	}

	/**
	 * Tests if the matched "IRI" is an e-mail address.
	 *
	 * @since 7.0.0
	 *
	 * @param string[] $iri The matched IRI parts.
	 *
	 * @return bool
	 */
	private function is_email_address( array $iri ): bool {
		return (
			! empty( $iri['userinfo'] ) &&
			! empty( $iri['domain'] ) &&
			empty( $iri['scheme'] ) &&
			empty( $iri['port'] ) &&
			empty( $iri['path'] ) &&
			empty( $iri['query'] ) &&
			empty( $iri['fragment'] )
		);
	}

	/**
	 * Inserts zero-width spaces into IRIs (and regular URLs) to allow
	 * them to wrap properly.
	 *
	 * @since 7.0.0
	 *
	 * @param string[] $iri      The matched IRI parts.
	 * @param Settings $settings The settings to apply.
	 *
	 * @return string
	 */
	private function wrap_iri( array $iri, Settings $settings ): string {
		$scheme   = ! empty( $iri['scheme'] ) ? $iri['scheme'] . U::ZERO_WIDTH_SPACE : '';
		$userinfo = ! empty( $iri['userinfo'] ) ? $iri['userinfo'] . U::ZERO_WIDTH_SPACE : '';
		$domain   = $this->split_domain( $iri['domain'], $settings );
		$port     = ! empty( $iri['port'] ) ? U::ZERO_WIDTH_SPACE . $iri['port'] : '';
		$path     = $this->split_path( $iri['path'], $settings );
		$query    = ! empty( $iri['query'] ) ? U::ZERO_WIDTH_SPACE . $iri['query'] : '';
		$fragment = ! empty( $iri['fragment'] ) ? U::ZERO_WIDTH_SPACE . $iri['fragment'] : '';

		return $scheme . $userinfo . $domain . $port . $path . $query . $fragment;
	}

	/**
	 * Splits the given domain name.
	 *
	 * @since  6.7.0
	 * @since  7.0.0 Method is now protected to allow for unit testing.
	 *
	 * @param  string   $domain   A domain/host name.
	 * @param  Settings $settings The settings to apply.
	 *
	 * @return string             The hyphenated domain name.
	 */
	protected function split_domain( string $domain, Settings $settings ): string {
		if ( \preg_match( '/' . self::URI_IP_LITERAL . '|' . self::URI_IPV4_ADDRESS . '/xui', $domain ) ) {
			// Don't split IP literals.
			return $domain;
		}

		$domain_parts = \preg_split( self::DOMAIN_PARTS_PATTERN, $domain, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( false === $domain_parts ) {
			// Should not happen.
			return $domain;  // @codeCoverageIgnore
		}

		// This is a hack, but it works.
		// First, we hyphenate each part, we need it formatted like a group of words.
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
	 * @since  7.0.0 Method is now protected to allow for unit testing.
	 *
	 * @param  string   $path     A URL path.
	 * @param  Settings $settings The settings to apply.
	 *
	 * @return string             The path with added zero-width break points.
	 */
	protected function split_path( string $path, Settings $settings ): string {
		if ( empty( $path ) ) {
			return $path;
		}

		$strlen = Strings::functions( $path )['strlen'];

		// Break up the URL path into individual parts at the path separator.
		$path_parts = \explode( '/', $path );
		$path_count = \count( $path_parts );
		$split_path = '';

		for ( $index = $path_count - 1; $index >= 0; --$index ) {
			switch ( $index ) {
				case 0:
					$joiner = '/';
					break;

				case $path_count - 1:
					$joiner = '';
					break;

				default:
					$joiner = U::ZERO_WIDTH_SPACE . '/';

			}

			if ( ! empty( $joiner ) && $strlen( $split_path ) < $settings->min_after_url_wrap ) {
				$joiner = '/';
			}

			$split_path = $path_parts[ $index ] . $joiner . $split_path;
		}

		return $split_path;
	}
}
