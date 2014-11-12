<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Pattern\Implement\ContactEdit;

class ContactCompany extends Backend {

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
        $ContactEdit->setTemplate('@phpManufaktur/Contact/Template', 'admin/edit.contact.twig');
        $ContactEdit->setFieldDefinition();

        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $ContactEdit->Execute($extra);
    }
}
