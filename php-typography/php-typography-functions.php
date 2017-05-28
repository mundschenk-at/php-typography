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
 * HTML5 element introspection
 */
require_once( __DIR__ . '/../vendor/Masterminds/HTML5/Elements.php' ); // @codeCoverageIgnore

/**
 * Determines whether two object arrays intersect. The second array is expected
 * to use the spl_object_hash for its keys.
 *
 * @param array $array1 The keys are ignored.
 * @param array $array2 This array has to be in the form ( $spl_object_hash => $object ).
 * @return boolean
 */
function arrays_intersect( array $array1, array $array2 ) {
	foreach ( $array1 as $value ) {
		if ( isset( $array2[ spl_object_hash( $value ) ] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Calculates binary-safe hash from data object.
 *
 * @param mixed $object Any datatype.
 *
 * @return string
 */
function get_object_hash( $object ) {
	return md5( json_encode( $object ), false );
}

/**
 * Include debugging helpers.
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { // @codeCoverageIgnoreStart
	define( 'WP_TYPOGRAPHY_DEBUG', true );
}
if ( defined( 'WP_TYPOGRAPHY_DEBUG' ) && WP_TYPOGRAPHY_DEBUG ) {
	include_once 'php-typography-debug.php';
} // @codeCoverageIgnoreEnd
