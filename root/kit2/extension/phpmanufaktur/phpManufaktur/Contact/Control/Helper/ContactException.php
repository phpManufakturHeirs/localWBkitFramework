<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Helper;

class ContactException extends \Exception
{

    public static function notImplemented($method)
    {
        return new self("The method '$method' is not implemented - please contact the support to get more information!");
    }

    public static function contactTypeNotSupported($type)
    {
        return new self("The contact type '$type' is not supported!");
    }
}