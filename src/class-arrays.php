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

namespace PHP_Typography;

/**
 * A utility class to help with array manipulation.
 */
abstract class Arrays {
	/**
	 * Provides an array_map implementation with control over resulting array's keys.
	 *
	 * @param  callable $callable A callback function that needs to $key, $value pairs.
	 *                            The callback should return tuple where the first part
	 *                            will be used as the key and the second as the value.
	 * @param  array    $array    The array.
	 *
	 * @return array
	 */
	public static function array_map_assoc( callable $callable, array $array ) {
		return array_column( array_map( $callable, array_keys( $array ), $array ), 1, 0 );
	}
}
