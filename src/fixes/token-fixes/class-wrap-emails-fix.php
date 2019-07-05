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

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser\Token;
use PHP_Typography\U;

/**
 * Wraps email parts zero-width spaces (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Wrap_Emails_Fix extends Abstract_Token_Fix {

	/**
	 * A regular expression matching email addresses.
	 *
	 * @var string
	 */
	protected $email_pattern;

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( Token_Fix::OTHER, $feed_compatible );

		$this->email_pattern = "/(?:
			\A
			[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			(?:
				\.
				[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			)*
			@
			(?:
				[a-z0-9]
				[a-z0-9\-]{0,61}
				[a-z0-9]
				\.
			)+
			(?:
				" . RE::top_level_domains() . '
			)
			\Z
		)/Sxi'; // required modifiers: x (multiline pattern) i (case insensitive).
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings[ Settings::EMAIL_WRAP ] ) ) {
			return $tokens;
		}

		// Test for and parse urls.
		foreach ( $tokens as $index => $token ) {
			$value = $token->value;
			if ( \preg_match( $this->email_pattern, $value, $email_match ) ) {
				$tokens[ $index ] = $token->with_value( \preg_replace( '/([^a-zA-Z0-9])/S', '$1' . U::ZERO_WIDTH_SPACE, $value ) );
			}
		}

		return $tokens;
	}
}
