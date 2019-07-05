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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\Settings;
use PHP_Typography\DOM;

/**
 * Wraps ampersands in <span class="amp"> (i.e. H&amp;J becomes H<span class="amp">&amp;</span>J),
 * if enabled.
 *
 * Call after style_caps so H&amp;J becomes <span class="caps">H<span class="amp">&amp;</span>J</span>.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 * @since 6.0.0 The replacement now assumes decoded ampersands (i.e. plain "&" instead of "&amp;").
 */
class Style_Ampersands_Fix extends Simple_Style_Fix {

	/**
	 * Creates a new node fix with a class.
	 *
	 * @param string $css_class       HTML class used in markup.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_class, $feed_compatible = false ) {
		parent::__construct( '/(&)/S', Settings::STYLE_AMPERSANDS, $css_class, $feed_compatible );
	}
}
