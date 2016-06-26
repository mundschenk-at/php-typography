<?php

class PHP_Typography_CSS_Classes extends \PHP_Typography\PHP_Typography {

	function __construct( $set_defaults = true, $init = 'now', $css_classes = array() )	{
		parent::__construct( $set_defaults, $init );

		$this->css_classes = array_merge( $this->css_classes, $css_classes );
	}
}
