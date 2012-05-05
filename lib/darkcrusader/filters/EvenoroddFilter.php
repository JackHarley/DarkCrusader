<?php
namespace darkcrusader\filters;

use hydrogen\view\engines\hydrogen\Filter;

class EvenoroddFilter implements Filter {

        public static function applyTo($string, $args, &$escape, $phpfile) {
                return "($string&1) ? 'odd' : 'even'";
        }
}

?>