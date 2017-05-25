<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015 Peter Putzer.
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

if ( ! function_exists( 'clean_html' ) ) {
	/**
	 * Return encoded HTML string (everything except <>"').
	 *
	 * @param string $html A HTML fragment.
	 */
	function clean_html( $html ) {
		// Convert everything except Latin and Cyrillic and Thai.
		static $convmap = array(
			// Simple Latin characters.
			0x80,   0x03ff,   0, 0xffffff, // @codingStandardsIgnoreLine.
			// Cyrillic characters.
			0x0514, 0x0dff, 0, 0xffffff, // @codingStandardsIgnoreLine.
			// Thai characters.
			0x0e7f, 0x10ffff, 0, 0xffffff, // @codingStandardsIgnoreLine.
		);

		return str_replace( array( '&lt;', '&gt;' ), array( '<', '>' ), mb_encode_numericentity( htmlentities( $html, ENT_NOQUOTES, 'UTF-8', false ), $convmap, 'UTF-8' ) );
	}
}

// Don't break without translation function.
if ( ! function_exists( '__' ) ) {
	/**
	 * Noop "translation" function for debugging.
	 *
	 * @param string $string Required.
	 * @param string $domain Optional. Default null.
	 */
	function &__( $string, $domain = null ) { return $string; } // // @codingStandardsIgnoreLine.
}
if ( ! function_exists( '_e' ) ) {
	/**
	 * Noop "translation" function for debugging.
	 *
	 * @param string $string Required.
	 * @param string $domain Optional. Default null.
	 */
	function &_e( $string, $domain = null ) { return $string; } // @codingStandardsIgnoreLine.
}
