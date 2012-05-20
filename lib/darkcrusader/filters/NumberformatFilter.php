<?php
namespace darkcrusader\filters;

use hydrogen\view\engines\hydrogen\Filter;

class NumberformatFilter implements Filter {

        public static function applyTo($string, $args, &$escape, $phpfile) {
            return "number_format($string)";
        }
}

?>