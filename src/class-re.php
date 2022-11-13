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

/**
 * Common regular expression components.
 *
 * @since 5.0.0
 */
abstract class RE {
	/**
	 * Find the HTML character representation for the following characters:
	 *      tab | line feed | carriage return | space | non-breaking space | ethiopic wordspace
	 *      ogham space mark | en quad space | em quad space | en-space | three-per-em space
	 *      four-per-em space | six-per-em space | figure space | punctuation space | em-space
	 *      thin space | hair space | narrow no-break space
	 *      medium mathematical space | ideographic space
	 * Some characters are used inside words, we will not count these as a space for the purpose
	 * of finding word boundaries:
	 *      zero-width-space ("&#8203;", "&#x200b;")
	 *      zero-width-joiner ("&#8204;", "&#x200c;", "&zwj;")
	 *      zero-width-non-joiner ("&#8205;", "&#x200d;", "&zwnj;")
	 */
	const HTML_SPACES = '
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

	const NORMAL_SPACES = ' \f\n\r\t\v'; // equivalent to \s in non-Unicode mode.

	// Marker for strings that should not be replaced.
	const ESCAPE_MARKER = '_E_S_C_A_P_E_D_';

	// Inserted HTML tags.
	const ESCAPED_HTML_OPEN  = '_B_E_G_I_N_H_T_M_L_';
	const ESCAPED_HTML_CLOSE = '_E_N_D_H_T_M_L_';

	/**
	 * A pattern matching top-level domains.
	 *
	 * @var string
	 */
	private static $top_level_domains_pattern;

	/**
	 * Load a list of top-level domains from a file.
	 *
	 * @param string $path The full path and filename.
	 *
	 * @return string A list of top-level domains concatenated with '|'.
	 */
	private static function get_top_level_domains_from_file( $path ) {
		$domains = [];

		if ( \file_exists( $path ) ) {
			$file = new \SplFileObject( $path );

			while ( ! $file->eof() ) {
				$line = $file->fgets();
				if ( ! \is_string( $line ) ) {
					break; // File could not be read, let's bail.
				}

				if ( \preg_match( '#^[a-zA-Z0-9][a-zA-Z0-9-]*$#', $line, $matches ) ) {
					$domains[] = \strtolower( $matches[0] );
				}
			}
		}

		if ( ! empty( $domains ) ) {
			return \implode( '|', $domains );
		} else {
			return 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
		}
	}

	/**
	 * Retrieves a pattern matching all valid top-level domains.
	 *
	 * @return string
	 */
	public static function top_level_domains() {
		if ( empty( self::$top_level_domains_pattern ) ) {
			// Initialize valid top level domains from IANA list.
			self::$top_level_domains_pattern = self::get_top_level_domains_from_file( __DIR__ . '/IANA/tlds-alpha-by-domain.txt' );
		}

		return self::$top_level_domains_pattern;
	}

	/**
	 * Replace < and > with escape markers.
	 *
	 * @param  string $tags A string containing HTML markup (all other < and > must be entity encoded).
	 *
	 * @return string
	 */
	public static function escape_tags( $tags ) {
		return str_replace( [ '<', '>' ], [ self::ESCAPED_HTML_OPEN, self::ESCAPED_HTML_CLOSE ], $tags );
	}

	/**
	 * Replace tag escape markers with < and >.
	 *
	 * @param  string $tags A string containing escaped HTML markup.
	 *
	 * @return string
	 */
	public static function unescape_tags( $tags ) {
		return str_replace( [ self::ESCAPED_HTML_OPEN, self::ESCAPED_HTML_CLOSE ], [ '<', '>' ], $tags );
	}
}
