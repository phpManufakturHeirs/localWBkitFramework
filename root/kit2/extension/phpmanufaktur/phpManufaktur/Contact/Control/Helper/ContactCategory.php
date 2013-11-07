<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Helper;

use Silex\Application;
use phpManufaktur\Contact\Control\Helper\ContactParent;
use phpManufaktur\Contact\Data\Contact\Category;
use phpManufaktur\Contact\Data\Contact\CategoryType;

class ContactCategory extends ContactParent
{

    protected $Category = null;
    protected $CategoryType = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->Category = new Category($this->app);
        $this->CategoryType = new CategoryType($this->app);
    }

    public function getDefaultRecord()
    {
        return $this->Category->getDefaultRecord();
    }

    public function validate(&$category_data, $contact_data=array(), $option=array())
    {
        if (!isset($category_data['category_type_name']) || empty($category_data['category_type_name'])) {
            $this->setMessage('Missing the key %field_name%, it must always set and not empty!',
                array('%field_name%' => 'category_type_name'));
            return false;
        }

        if (!$this->CategoryType->existsCategory($category_data['category_type_name'])) {
            $this->setMessage("The category %category% does not exists!",
                array('%category%' => $category_data['category_type_name']));
            return false;
        }
        return true;
    }

    /**
     * Insert a CATEGORY
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $category_id
     * @param reference boolean $has_inserted
     * @return boolean
     */
    public function insert($data, $contact_id, &$category_id=null, &$has_inserted=null)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;
        $has_inserted = false;

        if (is_null($data['category_type_name']) || !isset($data['category_type_name']) || empty($data['category_type_name'])) {
            // nothing to do ...
            return true;
        }

        if (!$this->validate($data)) {
            return false;
        }

        $this->Category->insert($data, $category_id);
        $has_inserted = true;
        return true;
    }

    public function delete($category_id)
    {
        $this->Category->delete($category_id);
    }

    public function selectCategoryTypeID($category_id)
    {
        return $this->Category->selectCategoryTypeID($category_id);
    }

}
