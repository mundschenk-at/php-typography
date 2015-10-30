<?php
/**
 * This file contains an autoload function.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */

spl_autoload_register(
    function ($classname) {
    	  	
    	if (strpos($classname, "Mastermind") === false)
   			return;
    	
    	$classname = preg_replace('/^' . preg_quote("Masterminds\\HTML5\\", '/') . '/', '', $classname);
    	    	
        if (preg_match_all("/[A-Z][a-z,0-9]*/", $classname, $matches)) {
            // script filename
            $dir = __DIR__;
            $name = "";
            $items = $matches[0];
                        
            foreach ($items as $item) {
                $d = $dir . '/' . $item;
                if (is_dir($d)) {
                    $dir = $d;
                    $item = "";
                }
                
                $name = $name . $item;
            }
            $filename = $dir . '/' . $name . ".php";
                        
            if (!is_file($filename)) {
                throw new Exception("Script not found: $filename");
            }
            
            include_once $filename;
        }
    }
);
