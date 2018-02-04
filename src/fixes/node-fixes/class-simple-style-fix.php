<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

use PHP_Typography\DOM;
use PHP_Typography\RE;
use PHP_Typography\Settings;

/**
 * An abstract base class for adding simple wrapping spans to style certain elements.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Simple_Style_Fix extends Classes_Dependent_Fix {

	/**
	 * The setting string used to enable/disable the fix (e.g. 'styleAmpersands').

	 * @var string
	 */
	protected $settings_switch;

	/**
	 * The regular expressions used to match the text that should be wrapped in spans.
	 *
	 * It must contain a single matching expression.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * The css class name to include in the generated markup.
	 *
	 * @var string
	 */
	protected $css_class;

	/**
	 * Creates a new node fix with a class.
	 *
	 * @param string $regex           Regular expression to match the text.
	 * @param string $settings_switch On/off switch for fix.
	 * @param string $css_class       HTML class used in markup.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $regex, $settings_switch, $css_class, $feed_compatible = false ) {
		parent::__construct( $css_class, $feed_compatible );

		$this->regex           = $regex;
		$this->settings_switch = $settings_switch;
		$this->css_class       = $css_class;
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 6.0.0 The method was accidentally made public and is now protected.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_internal( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings[ $this->settings_switch ] ) ) {
			return;
		}

		$textnode->data = \preg_replace( $this->regex, RE::escape_tags( "<span class=\"{$this->css_class}\">\$1</span>" ), $textnode->data );
	}
}
