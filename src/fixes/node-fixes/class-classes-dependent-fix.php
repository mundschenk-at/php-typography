<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify modify
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
 * All fixes that depend on certain HTML classes not being present should extend this baseclass.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Classes_Dependent_Fix extends Abstract_Node_Fix {

	/**
	 * An array of HTML classes to avoid applying the fix.
	 *
	 * @var string[]
	 */
	private $classes_to_avoid;

	/**
	 * Creates a new classes dependent fix.
	 *
	 * @param string[]|string $classes         HTML class(es).
	 * @param bool            $feed_compatible Optional. Default false.
	 */
	public function __construct( $classes, $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		if ( ! is_array( $classes ) ) {
			$classes = [ $classes ];
		}

		$this->classes_to_avoid = $classes;
	}

	/**
	 * Apply the fix to a given textnode if the nodes class(es) allow it.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 *
	 * @return void
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( ! DOM::has_class( $textnode, $this->classes_to_avoid ) ) {
			$this->apply_internal( $textnode, $settings, $is_title );
		}
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 6.0.0 The method was accidentally made public and is now protected.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 *
	 * @return void
	 */
	abstract protected function apply_internal( \DOMText $textnode, Settings $settings, $is_title = false );
}
