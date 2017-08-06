<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests;

/**
 * Subclass of PHP_Typography for setting custom CSS classes.
 */
class PHP_Typography_CSS_Classes extends \PHP_Typography\PHP_Typography {

	/**
	 * Create new instance of PHP_Typography_CSS_Classes.
	 *
	 * @param string $init         Optional. Initialize immediately. Default 'now'.
	 * @param array  $css_classes  Optional. An array of CSS classes. Default [].
	 */
	public function __construct( $init = 'now', $css_classes = [] ) {
		$this->css_classes = array_merge( $this->css_classes, $css_classes );

		parent::__construct( $init );
	}
}
