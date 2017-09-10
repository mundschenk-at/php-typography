<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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
 * Per-language cache of Hyphenator instances.
 *
 * @since 5.0.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Hyphenator_Cache {

	/**
	 * An array of Hyphenator instances indexed by language.
	 *
	 * @var array
	 */
	protected $cache = [];

	/**
	 * Caches a Hyphenator instance.
	 *
	 * @param string     $lang       A language code.
	 * @param Hyphenator $hyphenator The object to cache.
	 */
	public function set_hyphenator( $lang, Hyphenator $hyphenator ) {
		$this->cache[ $lang ] = $hyphenator;
	}

	/**
	 * Retrieves a cached Hyphenator.
	 *
	 * @param string $lang A language code.
	 *
	 * @return Hyphenator|null
	 */
	public function get_hyphenator( $lang ) {
		if ( isset( $this->cache[ $lang ] ) ) {
			return $this->cache[ $lang ];
		}

		return null;
	}
}
