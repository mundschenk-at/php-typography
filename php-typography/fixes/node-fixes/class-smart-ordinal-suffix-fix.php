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

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\Settings;
use \PHP_Typography\DOM;

/**
 * Applies smart ordinal suffix (if enabled).
 *
 * Call before style_numbers.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Ordinal_Suffix_Fix extends Abstract_Node_Fix {

	/**
	 * The CSS class to use for the suffix markup.
	 *
	 * @var string|null
	 */
	private $css_class;

	/**
	 * Creates a new smart ordinal suffix fixer.
	 *
	 * @param string|null $css_class       Optional. Default null.
	 * @param bool        $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_class = null, $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		$this->css_class = $css_class;
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['smartOrdinalSuffix'] ) ) {
			return;
		}

		$ordinal_class  = empty( $this->css_class ) ? '' : ' class="' . $this->css_class . '"';
		$textnode->data = preg_replace( $settings->regex( 'smartOrdinalSuffix' ), '$1' . "<sup{$ordinal_class}>$2</sup>", $textnode->data );
	}
}
