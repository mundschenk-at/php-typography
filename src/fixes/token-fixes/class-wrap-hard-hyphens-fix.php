<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser\Token;
use PHP_Typography\U;

/**
 * Wraps hard hypens with zero-width spaces (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Wrap_Hard_Hyphens_Fix extends Abstract_Token_Fix {

	/**
	 * An array of "hyphen-like" characters.
	 *
	 * @var array
	 */
	protected $hyphens_array;

	/**
	 * The regular expression to strip the space from hyphen-like characters at the end of a string.
	 *
	 * @var string
	 */
	protected $remove_ending_space_regex;

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( Token_Fix::MIXED_WORDS, $feed_compatible );

		$this->hyphens_array             = \array_unique( [ '-', U::HYPHEN ] );
		$this->remove_ending_space_regex = '/(' . \implode( '|', $this->hyphens_array ) . ')' . U::ZERO_WIDTH_SPACE . '$/';
	}

	/**
	 * Apply the fix to a given set of tokens
	 *
	 * @since 7.0.0 The parameter order has been re-arranged to mirror Node_Fix.
	 *
	 * @param Token[]  $tokens   The set of tokens.
	 * @param \DOMText $textnode The context DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return Token[]           The fixed set of tokens.
	 */
	public function apply( array $tokens, \DOMText $textnode, Settings $settings, $is_title ) {
		if ( ! empty( $settings['hyphenHardWrap'] ) || ! empty( $settings['smartDashes'] ) ) {

			foreach ( $tokens as $index => $text_token ) {
				$value = $text_token->value;

				if ( isset( $settings['hyphenHardWrap'] ) && $settings['hyphenHardWrap'] ) {
					$value = \str_replace( $this->hyphens_array, '-' . U::ZERO_WIDTH_SPACE, $value );
					$value = \str_replace( '_', '_' . U::ZERO_WIDTH_SPACE, $value );
					$value = \str_replace( '/', '/' . U::ZERO_WIDTH_SPACE, $value );

					$value = \preg_replace( $this->remove_ending_space_regex, '$1', $value );
				}

				if ( ! empty( $settings['smartDashes'] ) ) {
					// Handled here because we need to know we are inside a word and not a URL.
					$value = \str_replace( '-', U::HYPHEN, $value );
				}

				$tokens[ $index ] = $text_token->with_value( $value );
			}
		}

		return $tokens;
	}
}
