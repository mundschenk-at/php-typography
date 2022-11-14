<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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
 * Named Unicode characters (in UTF-8 encoding).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
interface U {

	const NO_BREAK_SPACE             = "\xc2\xa0";
	const NO_BREAK_NARROW_SPACE      = "\xe2\x80\xaf";
	const COPYRIGHT                  = "\xc2\xa9";
	const GUILLEMET_OPEN             = "\xc2\xab";
	const SOFT_HYPHEN                = "\xc2\xad";
	const REGISTERED_MARK            = "\xc2\xae";
	const GUILLEMET_CLOSE            = "\xc2\xbb";
	const MULTIPLICATION             = "\xc3\x97";
	const DIVISION                   = "\xc3\xb7";
	const FIGURE_SPACE               = "\xe2\x80\x87";
	const THIN_SPACE                 = "\xe2\x80\x89";
	const HAIR_SPACE                 = "\xe2\x80\x8a";
	const ZERO_WIDTH_SPACE           = "\xe2\x80\x8b";
	const HYPHEN_MINUS               = '-';
	const HYPHEN                     = "\xe2\x80\x90";
	const NO_BREAK_HYPHEN            = "\xe2\x80\x91";
	const EN_DASH                    = "\xe2\x80\x93";
	const EM_DASH                    = "\xe2\x80\x94";
	const SINGLE_QUOTE_OPEN          = "\xe2\x80\x98";
	const SINGLE_QUOTE_CLOSE         = "\xe2\x80\x99";
	const APOSTROPHE                 = "\xca\xbc"; // This is the "MODIFIER LETTER APOSTROPHE".
	const SINGLE_LOW_9_QUOTE         = "\xe2\x80\x9a";
	const DOUBLE_QUOTE_OPEN          = "\xe2\x80\x9c";
	const DOUBLE_QUOTE_CLOSE         = "\xe2\x80\x9d";
	const DOUBLE_LOW_9_QUOTE         = "\xe2\x80\x9e";
	const ELLIPSIS                   = "\xe2\x80\xa6";
	const SINGLE_PRIME               = "\xe2\x80\xb2";
	const DOUBLE_PRIME               = "\xe2\x80\xb3";
	const SINGLE_ANGLE_QUOTE_OPEN    = "\xe2\x80\xb9";
	const SINGLE_ANGLE_QUOTE_CLOSE   = "\xe2\x80\xba";
	const FRACTION_SLASH             = "\xe2\x81\x84";
	const SOUND_COPY_MARK            = "\xe2\x84\x97";
	const SERVICE_MARK               = "\xe2\x84\xa0";
	const TRADE_MARK                 = "\xe2\x84\xa2";
	const MINUS                      = "\xe2\x88\x92";
	const LEFT_CORNER_BRACKET        = "\xe3\x80\x8c";
	const RIGHT_CORNER_BRACKET       = "\xe3\x80\x8d";
	const LEFT_WHITE_CORNER_BRACKET  = "\xe3\x80\x8e";
	const RIGHT_WHITE_CORNER_BRACKET = "\xe3\x80\x8f";
	const ZERO_WIDTH_JOINER          = "\u{200c}";
	const ZERO_WIDTH_NON_JOINER      = "\u{200d}";

}
