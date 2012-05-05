<?php
namespace darkcrusader\filters;

use hydrogen\view\engines\hydrogen\Filter;

class LongtoipFilter implements Filter {

        public static function applyTo($string, $args, &$escape, $phpfile) {
                return "long2ip($string)";
        }
}

?>