# PHP-Typography

A PHP library for improving your web typography.

## Requirements

*   PHP 5.6.0 or above
*   The `mbstring` extension

## Installation

The best way to use this package is through Composer:

```BASH
$ composer require mundschenk-at/php-typography
```

## Basic Usage

1.  Create a `Settings` object and enable the fixes you want.
2.  Create a `PHP_Typography` instance and use it to process HTML fragments (or
	  whole documents) using your defined settings.

```PHP
$settings = new \PHP_Typography\Settings();
$settings->set_hyphenation( true );
$settings->set_hyphenation_language( 'en-US' );

$typo = new \PHP_Typography\PHP_Typography();

$hyphenated_html = $typo->process( $html_snippet, $settings );

```

## License

PHP-Typography is licensed under the GNU General Public License 2 or later - see the [LICENSE](LICENSE) file for details.
