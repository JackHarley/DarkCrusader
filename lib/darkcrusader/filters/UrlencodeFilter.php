<?php
namespace darkcrusader\filters;

use hydrogen\view\engines\hydrogen\Filter;

class UrlencodeFilter implements Filter {

        public static function applyTo($string, $args, &$escape, $phpfile) {
            return "urlencode($string)";
        }
}

?>