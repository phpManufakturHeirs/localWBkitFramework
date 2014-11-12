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
use phpManufaktur\Contact\Data\Contact\ExtraType;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class ExtraFieldEdit extends Dialog {

    protected $ExtraType = null;
    protected static $type_id = -1;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
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
                'edit' => isset($options['template']['edit']) ? $options['template']['edit'] : 'pattern/simple/edit.extra.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/extra/edit',
                'list' => isset($options['route']['list']) ? $options['route']['list'] : '/admin/contact/extra/list'
            )
        ));
        $this->ExtraType = new ExtraType($this->app);
    }

    /**
     * Set the type ID
     *
     * @param integer $type_id
     */
    public function setTypeID($type_id)
    {
        self::$type_id = $type_id;
    }

    /**
     * Get the form fields
     *
     * @param array $data
     * @return form.factory
     */
    protected function getFormFields($data)
    {
        $fields = $this->app['form.factory']->createBuilder('form', $data)
        ->add('extra_type_id', 'hidden')
        ->add('extra_type_name', 'text', array(
            'read_only' => ($data['extra_type_id'] > 0) ? true : false,
            'label' => 'Field name'
        ))
        ->add('extra_type_type', 'choice', array(
            'choices' => $this->ExtraType->getTypeArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'label' => 'Field type'
        ))
        ->add('extra_type_description', 'textarea', array(
            'required' => false,
            'label' => 'Description'
        ))
        ->add('delete', 'checkbox', array(
            'required' => false
        ))
        ;
        return $fields;
    }

    /**
     * Default controller for the Categories
     *
     * @param Application $app
     * @param string $category_id
     * @return string
     */
    public function controller(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
        return $this->exec();
    }

    /**
     * Execute the create and edit dialog
     *
     * @param array $extra
     */
    public function exec($extra=null)
    {
        if (self::$type_id < 1) {
            $type = $this->ExtraType->getDefaultRecord();
        }
        elseif (false === ($type = $this->ExtraType->select(self::$type_id))) {
            $type = $this->ExtraType->getDefaultRecord();
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => self::$type_id), self::ALERT_TYPE_WARNING);
            self::$type_id = -1;
        }

        $fields = $this->getFormFields($type);
        $form = $fields->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $type = $form->getData();
                self::$type_id = $type['extra_type_id'];

                if (self::$type_id < 1) {
                    // insert a new extra field
                    $matches = array();
                    $type_name = str_replace(' ', '_', strtoupper($type['extra_type_name']));
                    if (preg_match_all('/[^A-Z0-9_$]/', $type_name, $matches)) {
                        // name check fail
                        $this->setAlert('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                            array('%identifier%' => $this->app['translator']->trans('Extra field')), self::ALERT_TYPE_WARNING);
                    }
                    elseif ($this->ExtraType->existsTypeName($type_name)) {
                        // the tag already exists
                        $this->setAlert('The identifier %identifier% already exists!',
                            array('%identifier%' => $type_name), self::ALERT_TYPE_WARNING);
                    }
                    else {
                        $data = array(
                            'extra_type_type' => $type['extra_type_type'],
                            'extra_type_name' => $type_name,
                            'extra_type_description' => !is_null($type['extra_type_description']) ? $type['extra_type_description'] : ''
                        );
                        $this->ExtraType->insert($data, self::$type_id);
                        $this->setAlert('The record with the ID %id% was successfull inserted.',
                            array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                        // subrequest to the category list
                        $subRequest = Request::create(self::$options['route']['list'], 'GET');
                        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                    }
                }
                elseif (!empty($type['delete'])) {
                    // delete the extra field
                    $this->ExtraType->delete(self::$type_id);
                    $this->setAlert('The record with the ID %id% was successfull deleted.',
                        array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                    // subrequest to the category list
                    $subRequest = Request::create(self::$options['route']['list'], 'GET');
                    return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }
                else {
                    // update a extra field
                    $data = array(
                        'extra_type_type' => $type['extra_type_type'],
                        'extra_type_description' => !is_null($type['extra_type_description']) ? $type['extra_type_description'] : ''
                    );
                    $this->ExtraType->update($data, self::$type_id);
                    $this->setAlert('The record with the ID %id% was successfull updated.',
                        array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                    // subrequest to the category list
                    $subRequest = Request::create(self::$options['route']['list'], 'GET');
                    return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }

                if (self::$type_id > 0) {
                    // get the actual extra field
                    $type = $this->ExtraType->select(self::$type_id);
                }
                else {
                    // set default record
                    $type = $this->ExtraType->getDefaultRecord();
                }
                $fields = $this->getFormFields($type);
                $form = $fields->getForm();

            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            self::$options['template']['namespace'],
            self::$options['template']['edit']),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
