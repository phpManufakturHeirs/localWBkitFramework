<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;
use phpManufaktur\miniShop\Control\Admin\Admin;
use phpManufaktur\Contact\Control\Pattern\Implement\ContactEdit;

class ContactCompany extends Admin
{

    /**
     * Controller to show a Contact Edit dialog
     *
     * @param Application $app
     * @param integer $contact_id
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function Controller(Application $app, $contact_id=null)
    {
        $this->initialize($app);

        $ContactEdit = new ContactEdit($app);

        $ContactEdit->setContactType('COMPANY');
        if (!is_null($contact_id)) {
            $ContactEdit->setContactID($contact_id);
        }

        // set template namespace and file name
        $ContactEdit->setTemplate('@phpManufaktur/miniShop/Template', 'admin/contact/edit.contact.twig');
        $ContactEdit->setFieldDefinition(self::$config['contact']['admin']['company']['field']);

        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $ContactEdit->Execute($extra);
    }
}
