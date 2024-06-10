<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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

namespace PHP_Typography\Settings;

/**
 * An abstract class encapsulating quote styles.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 * @since 7.0.0 Changed to abstract class.
 */
abstract class Quotes implements \JsonSerializable {

	/**
	 * Retrieves the styles opening quote characters.
	 *
	 * @return string
	 */
	abstract public function open(): string;

	/**
	 * Retrieves the styles closing quote characters.
	 *
	 * @return string
	 */
	abstract public function close(): string;

	/**
	 * Provides a JSON serialization of the settings.
	 *
	 * @since 7.0.0
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'open'  => $this->open(),
			'close' => $this->close(),
		];
	}
}
