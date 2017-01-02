<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2017 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Copyright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
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

namespace PHP_Typography;

/**
 * A class to parse plain text (such as the data of DOMText).
 *
 * Parse_Text assumes no HTML markup in the text (except for special html characters like &gt;).
 * If multibyte characters are passed, they must be encoded as UTF-8.
 */
class Text_Parser {

	/**
	 * An array of encodings to check.
	 *
	 * @var array $encodings An array of encoding names.
	 */
	private $encodings = array();

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @var array $str_functions {
	 * 		@type string $encoding The name of the strtoupper function to use.
	 * }
	 */
	private $str_functions = array(
		'UTF-8' => 'mb_strtoupper',
		'ASCII' => 'strtoupper',
		false   => false,
	);

	/**
	 * The current strtoupper function to use (either 'strtoupper' or 'mb_strtoupper').
	 *
	 * @var string
	 */
	private $current_strtoupper = false;

	/**
	 * The tokenized text.
	 *
	 * @var array $text {
	 * 		@type array $index {
	 * 			Tokenized text.
	 *
	 * 			@type string $type  'space' | 'punctuation' | 'word' | 'other'. Required.
	 * 			@type string $value Token content. Required.
	 * 		}
	 * }
	 */
	private $text = array();

	/**
	 * An array of various regex components (not complete patterns).
	 *
	 * @var array $components
	 */
	private $components = array();

	/**
	 * An array of regex patterns.
	 *
	 * @var array $regex
	 */
	private $regex = array();

	/**
	 * Creates a new parser object.
	 *
	 * @param array $encodings Optional. Default [ 'ASCII', 'UTF-8' ].
	 */
	function __construct( $encodings = array( 'ASCII', 'UTF-8' ) ) {
		$this->encodings = $encodings;

		/**
		 * Find spacing FIRST (as it is the primary delimiter)
		 *
		 * Find the HTML character representation for the following characters:
		 *		tab | line feed | carriage return | space | non-breaking space | ethiopic wordspace
		 *		ogham space mark | en quad space | em quad space | en-space | three-per-em space
		 *		four-per-em space | six-per-em space | figure space | punctuation space | em-space
		 *		thin space | hair space | narrow no-break space
		 *		medium mathematical space | ideographic space
		 * Some characters are used inside words, we will not count these as a space for the purpose
		 * of finding word boundaries:
		 *		zero-width-space ("&#8203;", "&#x200b;")
		 *		zero-width-joiner ("&#8204;", "&#x200c;", "&zwj;")
		 *		zero-width-non-joiner ("&#8205;", "&#x200d;", "&zwnj;")
		 */
		$this->components['htmlSpacing'] = '
				(?:
					(?:										# alpha matches
						&
						(?: nbsp|ensp|emsp|thinsp )
						;
					)
					|
					(?:										# decimal matches
						&\#
						(?: 09|1[03]|32|160|4961|5760|819[2-9]|820[0-2]|8239|8287|12288 )
						;
					)
					|
					(?:										# hexidecimal matches
						&\#x
						(?: 000[9ad]|0020|00a0|1361|1680|200[0-9a]|202f|205f|3000 )
						;
					)
					|
					(?:										# actual characters
						\x{0009}|\x{000a}|\x{000d}|\x{0020}|\x{00a0}|\x{1361}|\x{2000}|\x{2001}|\x{2002}|\x{2003}|
						\x{2004}|\x{2005}|\x{2006}|\x{2007}|\x{2008}|\x{2009}|\x{200a}|\x{202f}|\x{205f}|\x{3000}
					)
				)
			'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).
		$this->components['space'] = "(?:\s|{$this->components['htmlSpacing']})+"; // required modifiers: x (multiline pattern) i (case insensitive) $utf8.

		/**
		 * Find punctuation and symbols before words (to capture preceeding delimiating characters like hyphens or underscores)
		 *
		 * @see http://www.unicode.org/charts/PDF/U2000.pdf
		 *
		 * Find punctuation and symbols
		 *	dec matches =   33-44|46-47|58-60|62-64|91-94|96|123-126|161-172|174-191|215|247|710|732|977-978|982|8211-8231|8240-8286|8289-8292|8352-8399|8448-8527|8592-9215|9632-9983|11776-11903
		 * 	hex matches = 	0021-002c|002e-002f|003a-003c|003e-0040|005b-e|0060|007b-007e|00a1-00ac|00ae-00bf|00d7|00f7|02c6|02dc|03d1-03d2|
		 *					03d6|2013-2027|2030-205e|2061-2064|20a0-20cf|2100-214f|2190-23ff|25a0-26ff|2e00-2e7f
		 *
		 * Some characters are used inside words, we will not count these as a space for the purpose
		 * of finding word boundaries:
		 * 		hyphens ("&#45;", "&#173;", "&#8208;", "&#8209;", "&#8210;", "&#x002d;", "&#x00ad;", "&#x2010;", "&#x2011;", "&#x2012;", "&shy;")
	 	 *		underscore ("&#95;", "&#x005f;")
	 	 */
		$this->components['htmlPunctuation'] = '
				(?:
					(?:										# alpha matches
						&
						(?:quot|amp|frasl|lt|gt|iexcl|cent|pound|curren|yen|brvbar|sect|uml|pound|ordf|laquo|not|reg|macr|deg|plusmn|sup2|sup3|acute|micro|para|middot|cedil|sup1|ordm|raquo|frac14|frac12|frac34|iquest|times|divide|circ|tilde|thetasym|upsih|piv|ndash|mdash|lsquo|rsquo|sbquo|ldquo|rdquo|bdquo|dagger|Dagger|bull|hellip|permil|prime|Prime|lsaquo|rsaquo|oline|frasl|euro|trade|alefsym|larr|uarr|rarr|darr|harr|crarr|lArr|uArr|rArr|dArr|hArr|forall|part|exist|emptyn|abla|isin|notin|ni|prod|sum|minus|lowast|radic|prop|infin|ang|and|orc|ap|cup|int|there4|simc|ong|asymp|ne|equiv|le|ge|sub|supn|sub|sube|supe|oplus|otimes|perp|sdot|lceil|rceil|lfloor|rfloor|lang|rang|loz|spades|clubs|hearts|diams)
						;
					)
					|
					(?:										# decimal matches
						&\#
						(?: 3[3-9]|4[0-467]|5[89]|6[02-4]|9[1-46]|12[3-6]|16[1-9]|17[0-24-9]|18[0-9]|19[01]|215|247|710|732|97[78]|982|821[1-9]|822[0-9]|823[01]|82[4-7][0-9]|828[0-6]|8289|829[0-2]|835[2-9]|86[6-9][0-9]|844[89]|84[5-9][0-9]|851[0-9]|852[0-7]|859[2-9]|85[6-9][0-9]|8[6-9][0-9][0-9]|9[01][0-9][0-9]|920[0-9]|921[0-5]|963[2-9]|96[4-9][0-9]|9[78][0-9][0-9]|99[0-7][0-9]|998[0-3]|1177[6-9]|117[89][0-9]|118[0-9][0-9]|1190[0-3] )
						;
					)
					|
					(?:										# hexidecimal matches
						&\#x
						(?: 002[1-9a-cef]|003[a-cef]|0040|005[b-e]|0060|007[b-e]|00a[1-9a-cef]|00b[0-9a-f]|00d7|00f7|02c6|02dc|03d[126]|201[3-9a-f]|202[0-7]|20[34][0-9a-f]|205[0-9a-e]|206[1-4]|20[a-c][0-9a-f]|21[0-4][0-9a-f]|219[0-9a-f]|2[23][0-9a-f][0-9a-f]|25[a-f][0-9a-f]|23[0-9a-f][0-9a-f]|2e[0-7][0-9a-f] )
						;
					)
				)
			'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).
		$this->components['punctuation'] = "
		(?:
			(?:
				[^\w\s\&\/\@]			# assume characters that are not word spaces or whitespace are punctuation
				# exclude & as that is an illegal stand-alone character (and would interfere with HTML character representations
				# exclude slash \/as to not include the last slash in a URL
				# exclude @ as to keep twitter names together
				|
				{$this->components['htmlPunctuation']}			# catch any HTML reps of punctuation
			)+
		)
		";// required modifiers: x (multiline pattern) i (case insensitive) u (utf8).

		/**
		 * Letter connectors allowed in words
		 * 		hyphens ("&#45;", "&#173;", "&#8208;", "&#8209;", "&#8210;", "&#x002d;", "&#x00ad;", "&#x2010;", "&#x2011;", "&#x2012;", "&shy;")
		 *		underscore ("&#95;", "&#x005f;")
		 *		zero-width-space ("&#8203;", "&#x200b;")
		 *		zero-width-joiner ("&#8204;", "&#x200c;", "&zwj;")
		 *		zero-width-non-joiner ("&#8205;", "&#x200d;", "&zwnj;")
		 */
		$this->components['htmlLetterConnectors'] = '
			(?:
				(?:												# alpha matches
					&
					(?: shy|zwj|zwnj )
					;
				)
				|
				(?:												# decimal matches
					&\#
					(?: 45|95|173|820[3-589]|8210 )
					;
				)
				|
				(?:												# hexidecimal matches
					&\#x
					(?: 002d|005f|00ad|200[b-d]|201[0-2] )
					;
				)
				|
				(?:												# actual characters
					\x{002d}|\x{005f}|\x{00ad}|\x{200b}|\x{200c}|\x{200d}|\x{2010}|\x{2011}|\x{2012}
				)
			)
		'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).

		/**
		 * Word character html entities
		 *   characters  0-9__ A-Z__ a-z___ other_special_chrs_____
		 *   decimal	 48-57 65-90 97-122 192-214,216-246,248-255, 256-383
		 *   hex		 31-39 41-5a 61-7a  c0-d6   d8-f6   f8-ff    0100-017f
		 */
		$this->components['htmlLetters'] = '
			(?:
				(?:												# alpha matches
					&
					(?:Agrave|Aacute|Acirc|Atilde|Auml|Aring|AElig|Ccedil|Egrave|Eacute|Ecirc|Euml|Igrave|Iacute|Icirc|Iuml|ETH|Ntilde|Ograve|Oacute|Ocirc|Otilde|Ouml|Oslash|Ugrave|Uacute|Ucirc|Uuml|Yacute|THORN|szlig|agrave|aacute|acirc|atilde|auml|aring|aelig|ccedil|egrave|eacute|ecirc|euml|igrave|iacute|icirc|iuml|eth|ntilde|ograve|oacute|ocirc|otilde|ouml|oslash|ugrave|uacute|ucirc|uuml|yacute|thorn|yuml)
					;
				)
				|
				(?:												# decimal matches
					&\#
					(?: 4[89]|5[0-7]|9[7-9]|1[01][0-9]|12[0-2]|19[2-9]|20[0-9]|21[0-46-9]|2[23][0-9]|24[0-68-9]|2[5-9][0-9]|3[0-7][0-9]|38[0-3] )
					;
				)
				|
				(?:												# hexidecimal matches
					(?:
						&\#x00
						(?: 3[1-9]|4[1-9a-f]|5[0-9a]|6[1-9a-f]|7[0-9a]|c[0-9a-f]|d[0-689]|e[0-9a-f]|f[0-689a-f] )
						;
					)
					|
					(?:
						&\#x01[0-7][0-9a-f];
					)
				)
				|
				(?:												# actual characters
					[0-9A-Za-z]|\x{00c0}|\x{00c1}|\x{00c2}|\x{00c3}|\x{00c4}|\x{00c5}|\x{00c6}|\x{00c7}|\x{00c8}|\x{00c9}|
					\x{00ca}|\x{00cb}|\x{00cc}|\x{00cd}|\x{00ce}|\x{00cf}|\x{00d0}|\x{00d1}|\x{00d2}|\x{00d3}|\x{00d4}|
					\x{00d5}|\x{00d6}|\x{00d8}|\x{00d9}|\x{00da}|\x{00db}|\x{00dc}|\x{00dd}|\x{00de}|\x{00df}|\x{00e0}|
					\x{00e1}|\x{00e2}|\x{00e3}|\x{00e4}|\x{00e5}|\x{00e6}|\x{00e7}|\x{00e8}|\x{00e9}|\x{00ea}|\x{00eb}|
					\x{00ec}|\x{00ed}|\x{00ee}|\x{00ef}|\x{00f0}|\x{00f1}|\x{00f2}|\x{00f3}|\x{00f4}|\x{00f5}|\x{00f6}|
					\x{00f8}|\x{00f9}|\x{00fa}|\x{00fb}|\x{00fc}|\x{00fd}|\x{00fe}|\x{00ff}|\x{0100}|\x{0101}|\x{0102}|
					\x{0103}|\x{0104}|\x{0105}|\x{0106}|\x{0107}|\x{0108}|\x{0109}|\x{010a}|\x{010b}|\x{010c}|\x{010d}|
					\x{010e}|\x{010f}|\x{0110}|\x{0111}|\x{0112}|\x{0113}|\x{0114}|\x{0115}|\x{0116}|\x{0117}|\x{0118}|
					\x{0119}|\x{011a}|\x{011b}|\x{011c}|\x{011d}|\x{011e}|\x{011f}|\x{0120}|\x{0121}|\x{0122}|\x{0123}|
					\x{0124}|\x{0125}|\x{0126}|\x{0127}|\x{0128}|\x{0129}|\x{012a}|\x{012b}|\x{012c}|\x{012d}|\x{012e}|
					\x{012f}|\x{0130}|\x{0131}|\x{0132}|\x{0133}|\x{0134}|\x{0135}|\x{0136}|\x{0137}|\x{0138}|\x{0139}|
					\x{013a}|\x{013b}|\x{013c}|\x{013d}|\x{013e}|\x{013f}|\x{0140}|\x{0141}|\x{0142}|\x{0143}|\x{0144}|
					\x{0145}|\x{0146}|\x{0147}|\x{0148}|\x{0149}|\x{014a}|\x{014b}|\x{014c}|\x{014d}|\x{014e}|\x{014f}|
					\x{0150}|\x{0151}|\x{0152}|\x{0153}|\x{0154}|\x{0155}|\x{0156}|\x{0157}|\x{0158}|\x{0159}|\x{015a}|
					\x{015b}|\x{015c}|\x{015d}|\x{015e}|\x{015f}|\x{0160}|\x{0161}|\x{0162}|\x{0163}|\x{0164}|\x{0165}|
					\x{0166}|\x{0167}|\x{0168}|\x{0169}|\x{016a}|\x{016b}|\x{016c}|\x{016d}|\x{016e}|\x{016f}|\x{0170}|
					\x{0171}|\x{0172}|\x{0173}|\x{0174}|\x{0175}|\x{0176}|\x{0177}|\x{0178}|\x{0179}|\x{017a}|\x{017b}|
					\x{017c}|\x{017d}|\x{017e}|\x{017f}
				)
			)
		'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).

		$this->components['word'] = "
		(?:
			(?<![\w\&])	 # negative lookbehind to ensure
		                 #	1) we are proceeded by a non-word-character, and
			             #	2) we are not inside an HTML character def
			(?:
				[\w\-\_\/]
				|
				{$this->components['htmlLetters']}
				|
				{$this->components['htmlLetterConnectors']}
			)+
		)
		"; // required modifiers: x (multiline pattern) u (utf8).

		// Find any text
		$this->components['anyText'] = "{$this->components['space']}|{$this->components['punctuation']}|{$this->components['word']}"; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8).

		// Store compiled regexes for later use & use S modifier ('study').
		$this->regex['anyText']              = "/({$this->components['anyText']})/Sixu";
		$this->regex['space']                = "/\A{$this->components['space']}\Z/Sxiu";
		$this->regex['punctuation']          = "/\A{$this->components['punctuation']}\Z/Ssxiu";
		$this->regex['word']                 = "/\A{$this->components['word']}\Z/Sxu";
		$this->regex['htmlLetterConnectors'] = "/{$this->components['htmlLetterConnectors']}|[0-9\-_&#;\/]/Sxu";
		$this->regex['maxStringLength']      = '@\w{500}@Ss';
	}

	/**
	 * Tokenizes a string and stores the tokens in $this->text.
	 *
	 * @param string $raw_text A text fragment without any HTML markup.
	 *
	 * @return bool Returns `true` on successful completion, `false` otherwise.
	 */
	function load( $raw_text ) {
		if ( ! is_string( $raw_text ) ) {
			return false; // we have an error, abort.
		}

		// Abort if a simple string exceeds 500 characters (security concern).
		if ( preg_match( $this->regex['maxStringLength'], $raw_text ) ) {
			return false;
		}

		// Detect encoding.
		$this->current_strtoupper = $this->str_functions[ mb_detect_encoding( $raw_text, $this->encodings, true ) ];
		if ( ! $this->current_strtoupper ) {
			return false; // unknown encoding.
		}

		$tokens = array();
		$parts = preg_split( $this->regex['anyText'], $raw_text, -1, PREG_SPLIT_DELIM_CAPTURE );

		$index = 0;
		foreach ( $parts as $part ) {
			if ( '' !== $part ) {

				if ( preg_match( $this->regex['space'], $part ) ) {
					$tokens[ $index ] = array(
						'type'	=> 'space',
						'value'	=> $part,
					);
				} elseif ( preg_match( $this->regex['punctuation'], $part ) ) {
					$tokens[ $index ] = array(
						'type'	=> 'punctuation',
						'value'	=> $part,
					);
				} elseif ( preg_match( $this->regex['word'], $part ) ) {
					// Make sure that things like email addresses and URLs are not broken up
					// into words and punctuation not preceeded by an 'other'.
					if ( $index - 1 >= 0 && 'other' === $tokens[ $index - 1 ]['type'] ) {
						$old_part = $tokens[ $index - 1 ]['value'];
						$tokens[ $index - 1 ] = array(
							'type'	=> 'other',
							'value'	=> $old_part . $part,
						);
						$index = $index - 1;

					// Not preceeded by a non-space + punctuation.
					} elseif ( $index - 2 >= 0 && 'punctuation' === $tokens[ $index - 1 ]['type'] && 'space' !== $tokens[ $index - 2 ]['type'] ) {
						$old_part   = $tokens[ $index - 1 ]['value'];
						$older_part = $tokens[ $index - 2 ]['value'];
						$tokens[ $index - 2 ] = array(
							'type'	=> 'other',
							'value'	=> $older_part . $old_part . $part,
						);
						unset( $tokens[ $index - 1 ] );
						$index = $index - 2;
					} else {
						$tokens[ $index ] = array(
							'type'	=> 'word',
							'value'	=> $part,
						);
					}
				} else {
					// Make sure that things like email addresses and URLs are not broken up into words
					// and punctuation not preceeded by an 'other' or 'word'.
					if ( $index - 1 >= 0 && ( 'word' === $tokens[ $index - 1 ]['type'] || 'other' === $tokens[ $index - 1 ]['type'] ) ) {
						$index = $index - 1;
						$old_part = $tokens[ $index ]['value'];
						$tokens[ $index ] = array(
							'type'	=> 'other',
							'value'	=> $old_part . $part,
						);
					// Not preceeded by a non-space + punctuation.
					} elseif ( $index - 2 >= 0 && 'punctuation' === $tokens[ $index - 1 ]['type'] && 'space' !== $tokens[ $index - 2 ]['type'] ) {
						$old_part   = $tokens[ $index - 1 ]['value'];
						$older_part = $tokens[ $index - 2 ]['value'];
						$tokens[ $index - 2 ] = array(
							'type'	=> 'other',
							'value'	=> $older_part . $old_part . $part,
						);
						unset( $tokens[ $index - 1 ] );
						$index = $index - 2;
					} else {
						$tokens[ $index ] = array(
							'type'	=> 'other',
							'value'	=> $part,
						);
					}
				}

				$index++;
			}
		}

		$this->text = $tokens;

		return true;
	}

	/**
	 * Reloads $this->text (i.e. capture new inserted text, or remove those tokens whose values have been deleted).
	 *
	 * Warning: Tokens previously acquired through 'get' methods may not match new tokenization.
	 *
	 * @return bool Returns true on successful completion.
	 */
	function reload() {
		return $this->load( $this->unload() );
	}

	/**
	 * Returns the complete text as a string and clears the parser.
	 *
	 * @return string
	 */
	function unload() {
		$reassembled_text = '';

		foreach ( $this->text as $token ) {
			$reassembled_text .= $token['value'];
		}

		$this->clear();

		return $reassembled_text;
	}

	/**
	 * Clears the currently set text from the parser.
	 */
	function clear() {
		$this->text = array();
		$this->current_strtoupper = false;
	}

	/**
	 * Updates the 'value' field for all matching tokens.
	 *
	 * @param array $tokens {
	 * 		An array of tokens.
	 *
	 * 		@type string $value
	 * }
	 */
	function update( $tokens ) {
		foreach ( $tokens as $index => $token ) {
			$this->text[ $index ]['value'] = $token['value'];
		}
	}

	/**
	 * Retrieves all tokens of the currently set text.
	 *
	 * @return array An array of tokens.
	 */
	function get_all() {
		return $this->text;
	}

	/**
	 * Retrieves all tokens of the type "space".
	 *
	 * @return array An array of tokens.
	 */
	function get_spaces() {
		return $this->get_type( 'space' );
	}

	/**
	 * Retrieves all tokens of the type "punctuation".
	 *
	 * @return array An array of tokens.
	 */
	function get_punctuation() {
		return $this->get_type( 'punctuation' );
	}

	/**
	 * Retrieves all tokens of the type "word".
	 *
	 * @param string $abc   Optional. Handling of all-letter words. Allowed values 'no-all-letters', 'allow-all-letters', 'require-all-letters'. Default 'allow-all-letters'.
	 * @param string $caps  Optional. Handling of capitalized words (setting does not affect non-letter characters). Allowed values 'no-all-caps', 'allow-all-caps', 'require-all-caps'. Default 'allow-all-caps'.
	 * @param string $comps Optional. Handling of compound words (setting does not affect all-letter words). Allowed values 'no-compounds', 'allow-compounds', 'require-compounds'. Default 'no-compounds'.
	 */
	function get_words( $abc = 'allow-all-letters', $caps = 'allow-all-caps', $comps = 'allow-compounds' ) {
		$tokens = array();
		$strtoupper = $this->current_strtoupper; // cannot call class properties.

		// Initialize helper variables outside the loop.
		$capped   = '';
		$lettered = '';
		$compound = '';

		foreach ( $this->get_type( 'word' ) as $index => $token ) {
			$capped   = $strtoupper( $token['value'] );
			$lettered = preg_replace( $this->regex['htmlLetterConnectors'], '', $token['value'] );
			$compound = preg_replace( '/[^\w-]/Su', '', $token['value'] );

			// @todo Refactor these tangled if statements.
			// @codingStandardsIgnoreStart.
			if ( 'no-all-letters' === $abc && $lettered !== $token['value'] ) {
				if ( ( ( 'no-all-caps'      === $caps && $capped !== $token['value'] ) ||
					   ( 'allow-all-caps'   === $caps ) ||
					   ( 'require-all-caps' === $caps && $capped === $token['value'] ) ) &&
					 ( ( 'no-compounds'      === $comps && $compound !== $token['value'] ) ||
					   ( 'allow-compounds'   === $comps ) ||
					   ( 'require-compounds' === $comps && $compound === $token['value'] ) ) ) {
					$tokens[ $index ] = $token;
				}
			} elseif ( 'allow-all-letters' === $abc ) {
				if ( ( ( 'no-all-caps'      === $caps && $capped !== $token['value'] ) ||
					   ( 'allow-all-caps'   === $caps ) ||
					   ( 'require-all-caps' === $caps && $capped === $token['value'] ) ) &&
				     ( ( 'no-compounds'      === $comps && $compound !== $token['value'] ) ||
					   ( 'allow-compounds'   === $comps ) ||
					   ( 'require-compounds' === $comps && $compound === $token['value'] ) ) ) {
					$tokens[ $index ] = $token;
				}
			} elseif ( 'require-all-letters' === $abc && $lettered === $token['value'] ) {
				if ( ( 'no-all-caps'      === $caps && $capped !== $token['value'] ) ||
					 ( 'allow-all-caps'   === $caps ) ||
					 ( 'require-all-caps' === $caps && $capped === $token['value'] ) ) {
				 	$tokens[ $index ] = $token;
				}
			}
			// @codingStandardsIgnoreEnd.
		}

		return $tokens;
	}

	/**
	 * Retrieves all tokens of the type "other".
	 *
	 * @return array An array of tokens.
	 */
	function get_other() {
		return $this->get_type( 'other' );
	}

	/**
	 * Retrieves all tokens of the given type.
	 *
	 * @param string $type The type to get.
	 */
	function get_type( $type ) {
		$tokens = array();

		foreach ( $this->text as $index => $token ) {
			if ( $token['type'] === $type ) {
				$tokens[ $index ] = $token;
			}
		}

		return $tokens;
	}
}
