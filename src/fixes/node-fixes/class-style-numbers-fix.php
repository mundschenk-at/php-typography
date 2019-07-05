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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\Settings;
use PHP_Typography\DOM;

/**
 * Wraps numbers in <span class="numbers"> (even numbers that appear inside a word,
 * i.e. A9 becomes A<span class="numbers">9</span>), if enabled.
 *
 * Call after style_caps so A9 becomes <span class="caps">A<span class="numbers">9</span></span>.
 * Call after smart_fractions and smart_ordinal_suffix.
 * Only call if you are certain that no html tags have been injected containing numbers.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Style_Numbers_Fix extends Simple_Style_Fix {

	/**
	 * Creates a new node fix with a class.
	 *
	 * @param string $css_class       HTML class used in markup.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_class, $feed_compatible = false ) {
		parent::__construct( '/([0-9]+)/S', Settings::STYLE_NUMBERS, $css_class, $feed_compatible );
	}
}
