<?php

/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015 Peter Putzer.
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License,
 *	version 2 as published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *  MA 02110-1301, USA.
 *
 *  ***
 *
 *  @package wpTypography
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! function_exists( 'clean_html' ) ) {
	/**
	 * Return encoded HTML string (everything except <>"').
	 *
	 * @param string $html
	 */
	function clean_html( $html ) {
		static $convmap = array(0x80, 0x10ffff, 0, 0xffffff);

		return str_replace( array('&lt;', '&gt;'), array('<', '>'), mb_encode_numericentity( htmlentities( $html, ENT_NOQUOTES, 'UTF-8', false ), $convmap, 'UTF-8' ) );
	}
}

// don't break without translation function
if ( ! function_exists( '__' ) ) {
	/**
	 * Noop "translation" function for debugging.
	 *
	 * @param string $string
	 * @param string $domain
	 */
	function &__( $string, $domain = null ) { return $string; }
}
if ( ! function_exists( '_e' ) ) {
	/**
	 * Noop "translation" function for debugging.
	 *
	 * @param string $string
	 * @param string $domain
	 */
	function &_e( $string, $domain = null ) { return $string; }
}
