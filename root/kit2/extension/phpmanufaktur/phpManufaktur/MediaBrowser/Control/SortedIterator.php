<?php

/**
 * MediaBrowser
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/MediaBrowser
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\MediaBrowser\Control;

class SortedIterator extends \SplHeap
{
    public function __construct(\Iterator $iterator)
    {
        foreach ($iterator as $item) {
            $this->insert($item);
        }
    }

    public function compare($b,$a)
    {
        return strcasecmp($a->getRealpath(), $b->getRealpath());
    }
}
