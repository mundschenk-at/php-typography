<?php
/**
 *  This file is part of wp-Typography.
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
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

/**
 * Common regular expression components.
 */
interface RE {
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

}
