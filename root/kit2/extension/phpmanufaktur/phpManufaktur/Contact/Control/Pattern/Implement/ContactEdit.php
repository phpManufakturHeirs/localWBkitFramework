<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Pattern\Implement;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use phpManufaktur\Contact\Control\Configuration;
use phpManufaktur\Contact\Control\Pattern\Form\Contact;

class ContactEdit extends Alert
{
    protected static $template = null;
    protected static $contact_type = null;
    protected static $contact_id = null;
    protected static $config = null;
    protected static $usage = null;
    protected static $field_definition = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        self::$usage = $this->app['request']->get('usage', 'framework');

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $form_request = $this->app['request']->get('form');
        self::$contact_id = (isset($form_request['contact_id'])) ? $form_request['contact_id'] : -1;
        self::$contact_type = (isset($form_request['contact_type'])) ? $form_request['contact_type'] : 'PERSON';

        // initialize the class
        $this->setTemplate();
        $this->setFieldDefinition();
    }

    /**
     * Set the template namespace and directory/name
     *
     * @param string $template_namespace
     * @param string $template_name
     */
    public function setTemplate($template_namespace=null, $template_name=null)
    {
        self::$template = array(
            'namespace' => !is_null($template_namespace) ? $template_namespace : '@phpManufaktur/Contact/Template',
            'name' => !is_null($template_name) ? $template_name : 'pattern/implement/edit.contact.twig'
        );
    }

    /**
     * Set the definition array for the contact fields
     *
     * @param array $field_definition
     */
    public function setFieldDefinition($field_definition=null)
    {
        if (is_null($field_definition)) {
            if (self::$contact_type == 'PERSON') {
                self::$field_definition = self::$config['dialog']['contact']['person']['field'];
            }
            else {
                self::$field_definition = self::$config['dialog']['contact']['company']['field'];
            }
        }
        else {
            self::$field_definition = $field_definition;
        }
    }

    /**
     * Set the ID for the contact record to use
     *
     * @param integer $contact_id
     */
    public function setContactID($contact_id=-1)
    {
        if (!filter_var($contact_id, FILTER_VALIDATE_INT)) {
            throw new \Exception('The contact ID must be an integer!');
        }
        self::$contact_id = $contact_id;
    }

    /**
     * Set the contact type to use
     *
     * @param string $contact_type can be PERSON or COMPANY
     */
    public function setContactType($contact_type='PERSON')
    {
        if (!in_array(strtoupper($contact_type), array('PERSON', 'COMPANY'))) {
            throw new \Exception('The contact type must be PERSON or COMPANY');
        }
        self::$contact_type = $contact_type;
    }

    public function Execute($extra_parameter=null)
    {
        $ContactForm = new Contact($this->app);

        if (isset($extra_parameter['usage'])) {
            self::$usage = $extra_parameter['usage'];
        }

        $data = $ContactForm->getData(self::$contact_id);

        if (false === ($form = $ContactForm->getFormContact($data, self::$field_definition))) {
            return $this->promptAlertFramework();
        }

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                // get the form data
                $data = $form->getData();

                if (false !== ($contact = $ContactForm->checkData($data))) {
                    // contact check was successfull
                    if ($contact['contact']['contact_id'] > 0) {
                        // update existing record
                        $this->app['contact']->update($contact, $contact['contact']['contact_id']);
                    }
                    else {
                        $this->app['contact']->insert($contact, $contact['contact']['contact_id']);
                    }
                    // reload the form with the current contact data
                    $data = $ContactForm->getData($contact['contact']['contact_id']);
                    if (false === ($form = $ContactForm->getFormContact($data, self::$field_definition))) {
                        return $this->promptAlertFramework();
                    }
                }
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            self::$template['namespace'], self::$template['name']),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'extra' => $extra_parameter,
                'form' => $form->createView(),
                'field' => self::$field_definition
            ));
    }
}
