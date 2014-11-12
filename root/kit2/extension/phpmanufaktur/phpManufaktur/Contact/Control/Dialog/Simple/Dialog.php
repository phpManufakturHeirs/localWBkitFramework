<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use phpManufaktur\Basic\Control\Pattern\Alert;

class Dialog extends Alert
{

    protected static $options = array();

    /**
     * @return the $options
     */
    public static function getOptions()
    {
        return self::$options;
    }

    /**
     * @param field_type $options
     */
    public static function setOptions($options)
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                self::$options[$key] = $value;
            }
        }
    }

}
