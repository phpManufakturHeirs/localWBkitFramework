<?php

/**
 * FacebookGallery
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\FacebookGallery;

class Install {

    protected $app = null;

    public function __construct()
    {
        global $app;
        $this->app = $app;
    }

    public function exec()
    {
        return 'gallery setup executed';
    }
}

$Install = new Install();
return $Install->exec();
