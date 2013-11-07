<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control;

use phpManufaktur\Contact\Control\Helper\ContactParent;
use phpManufaktur\Contact\Data\Contact\Overview;

class ContactList extends ContactParent
{
    protected $Overview = null;
    protected $CountRows = null;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->Overview = new Overview($this->app);
    }

    public function selectAll()
    {
        $count = $this->Overview->count(array('ACTIVE','LOCKED'));
        echo "$count<br>";
        echo ceil($count/3);
        return $this->Overview->selectAll();
    }


    public function getList(&$list_page, $rows_per_page, $select_status=null, &$max_pages=null, $order_by=null, $order_direction='ASC', $select_type=null)
    {
        // count rows
        $count_rows = $this->Overview->count($select_status, $select_type);
        if ($count_rows < 1) {
            return null;
        }
        $max_pages = ceil($count_rows/$rows_per_page);
        if ($list_page < 1) {
            $list_page = 1;
        }
        if ($list_page > $max_pages) {
            $list_page = $max_pages;
        }
        $limit_from = ($list_page * $rows_per_page) - $rows_per_page;

        return $this->Overview->selectList($limit_from, $rows_per_page, $select_status, $order_by, $order_direction, $select_type);
    }

    public function rebuildList()
    {
        $this->Overview->rebuildOverview();
    }

    public function getColumns()
    {
        return $this->Overview->getColumns();
    }
}
