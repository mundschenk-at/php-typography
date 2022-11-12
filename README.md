# PHP-Typography

![Build Status](https://github.com/mundschenk-at/php-typography/actions/workflows/ci.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/mundschenk-at/php-typography/v/stable)](https://packagist.org/packages/mundschenk-at/php-typography)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=mundschenk-at_php-typography&metric=alert_status)](https://sonarcloud.io/dashboard?id=mundschenk-at_php-typography)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=mundschenk-at_php-typography&metric=coverage)](https://sonarcloud.io/dashboard?id=mundschenk-at_php-typography)
[![License](https://poser.pugx.org/mundschenk-at/php-typography/license)](https://packagist.org/packages/mundschenk-at/php-typography)

A PHP library for improving your web typography:

*   Hyphenation — over 50 languages supported
*   Space control, including:
    -   widow protection
    -   gluing values to units
    -   forced internal wrapping of long URLs & email addresses
*   Intelligent character replacement, including smart handling of:
    -   quote marks (‘single’, “double”)
    -   dashes ( – )
    -   ellipses (…)
    -   trademarks, copyright & service marks (™ ©)
    -   math symbols (5×5×5=53)
    -   fractions (<sup>1</sup>⁄<sub>16</sub>)
    -   ordinal suffixes (1<sup>st</sup>, 2<sup>nd</sup>)
*   CSS hooks for styling:
    -   ampersands,
    -   uppercase words,
    -   numbers,
    -   initial quotes & guillemets.

## Requirements

*   PHP 7.4.0 or above
*   The `mbstring` extension

## Installation

The best way to use this package is through Composer:

```BASH
$ composer require mundschenk-at/php-typography
$ vendor/bin/update-iana.php
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

## Roadmap

Please have a look at [ROADMAP](ROADMAP.md) file for upcoming releases.

## License

PHP-Typography is licensed under the GNU General Public License 2 or later - see the [LICENSE](LICENSE) file for details.
