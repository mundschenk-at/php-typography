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

namespace PHP_Typography\Settings;

/**
 * A basic Dashes implementation.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
final class Simple_Dashes implements Dashes {

	/**
	 * The dash character used for parenthetical dashes.
	 *
	 * @var string
	 */
	private $parenthetical_dash;

	/**
	 * The space character used around parenthetical dashes.
	 *
	 * @var string
	 */
	private $parenthetical_space;

	/**
	 * The dash character used for interval dashes.
	 *
	 * @var string
	 */
	private $interval_dash;

	/**
	 * The space character used around interval dashes.
	 *
	 * @var string
	 */
	private $interval_space;

	/**
	 * Creates a new quotes object.
	 *
	 * @param string $parenthetical       The dash character used for parenthetical dashes.
	 * @param string $parenthetical_space The space character used around parenthetical dashes.
	 * @param string $interval            The dash character used for interval dashes.
	 * @param string $interval_space      The space character used around interval dashes.
	 */
	public function __construct( $parenthetical, $parenthetical_space, $interval, $interval_space ) {
		$this->parenthetical_dash  = $parenthetical;
		$this->parenthetical_space = $parenthetical_space;
		$this->interval_dash       = $interval;
		$this->interval_space      = $interval_space;
	}

	/**
	 * Retrieves the dash used for interval dashes.
	 *
	 * @return string
	 */
	public function interval_dash() {
		return $this->interval_dash;
	}

	/**
	 * Retrieves the space character used around interval dashes.
	 *
	 * @return string
	 */
	public function interval_space() {
		return $this->interval_space;
	}

	/**
	 * Retrieves the dash used for parenthetical dashes.
	 *
	 * @return string
	 */
	public function parenthetical_dash() {
		return $this->parenthetical_dash;
	}

	/**
	 * Retrieves the space character used around parenthetical dashes.
	 *
	 * @return string
	 */
	public function parenthetical_space() {
		return $this->parenthetical_space;
	}
}
