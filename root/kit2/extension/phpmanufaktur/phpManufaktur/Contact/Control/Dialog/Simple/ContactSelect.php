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

use Silex\Application;
use phpManufaktur\Contact\Control\Contact as ContactControl;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ContactSelect extends Dialog {

    protected static $contact_id = -1;
    protected $ContactControl = null;

    /**
     * Constructor
     *
     * @param Application $app
     * @param array $options
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        // set the form options

        if (!is_null($app)) {
            $this->initialize($app, $options);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Alert::initialize()
     */
    protected function initialize(Application $app, $options=null)
    {
        parent::initialize($app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'select' => isset($options['template']['select']) ? $options['template']['select'] : 'pattern/admin/simple/select.contact.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/select',
                'contact' => array(
                    'person' => array(
                        'create' => isset($options['route']['contact']['person']['create']) ? $options['route']['contact']['person']['create'] : '/admin/contact/person/edit',
                        'edit' => isset($options['route']['contact']['person']['edit']) ? $options['route']['contact']['person']['edit'] : '/admin/contact/person/edit/id/{contact_id}'
                    ),
                    'company' => array(
                        'create' => isset($options['route']['contact']['company']['create']) ? $options['route']['contact']['company']['create'] : '/admin/contact/company/edit',
                        'edit' => isset($options['route']['contact']['company']['edit']) ? $options['route']['contact']['company']['edit'] : '/admin/contact/company/edit/id/{contact_id}'
                    )
                )
            )
        ));

        $this->ContactControl = new ContactControl($this->app);
    }

    /**
     * Set the contact ID
     *
     * @param integer $contact_id
     */
    public function setContactID($contact_id)
    {
        self::$contact_id = $contact_id;
    }

    /**
     * Build the complete form with the form.factory
     *
     * @param array $contact flatten contact record
     * @return FormBuilder
     */
    protected function getForm()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('contact_id', 'hidden', array(
            'data' => self::$contact_id
        ))
        ->add('select_type', 'choice', array(
            'choices' => array('PERSON' => 'Person', 'COMPANY' => 'Company'),
            'empty_value' => false,
            'multiple' => false,
            'expanded' => true,
            'label' => 'Select contact type',
            'data' => 'PERSON'
        ))
        ->getForm();
    }

    /**
     * Execute the default controller
     *
     * @param Application $app
     * @return string contact select dialog
     */
    public function controller(Application $app, $contact_id=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($contact_id)) {
            $this->setContactID($contact_id);
        }
        return $this->exec();
    }

    /**
     * Return the complete contact dialog and handle requests
     *
     * @return string contact dialog
     */
    public function exec($extra=null)
    {
        if (self::$contact_id > 0) {
            // select a specific contact ID for editing
            if (false === ($type = $this->ContactControl->getContactType(self::$contact_id))) {
                $this->setAlert("The contact with the ID %contact_id% does not exists!",
                    array('%contact_id%' => self::$contact_id), self::ALERT_TYPE_WARNING);
            }
            elseif ($type == 'PERSON') {
                $subRequest = Request::create(str_replace('{contact_id}', self::$contact_id, self::$options['route']['contact']['person']['edit']));
                return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            else {
                // contact type == COMPANY
                $subRequest = Request::create(str_replace('{contact_id}', self::$contact_id, self::$options['route']['contact']['company']['edit']));
                return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
        }

        // create the form
        $form = $this->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                // get the form data
                $contact = $form->getData();

                if ($contact['select_type'] == 'PERSON') {
                    // create a new PERSON contact
                    $subRequest = Request::create(self::$options['route']['contact']['person']['create']);
                }
                else {
                    // create a new COMPANY contact
                    $subRequest = Request::create(self::$options['route']['contact']['company']['create']);
                }
                return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['select']),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
