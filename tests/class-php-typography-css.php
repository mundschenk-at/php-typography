<?php

class PHP_Typography_CSS_Classes extends \PHP_Typography\PHP_Typography {

	function __construct( $set_defaults = true, $init = 'now' )	{
		parent::__construct( $set_defaults, $init );

		$this->css_classes['numerator']   = 'num';
		$this->css_classes['denominator'] = 'denom';
	}
}