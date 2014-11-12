<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\ExtraType;
use Silex\Application;
use phpManufaktur\Event\Data\Event\ExtraGroup;
use phpManufaktur\Event\Data\Event\Group;

class ExtraFieldEdit extends Backend {

    protected $ExtraType = null;
    protected static $type_id = -1;
    protected $ExtraGroup = null;
    protected $EventGroup = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->ExtraType = new ExtraType($app);
        $this->ExtraGroup = new ExtraGroup($app);
        $this->EventGroup = new Group($app);
    }

    public function setTypeID($type_id)
    {
        self::$type_id = $type_id;
    }

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
            'required' => true,
            'label' => 'Description'
        ))
        ->add('delete', 'choice', array(
            'choices' => array('DELETE' => 'delete this extra field'),
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'label' => 'Delete'
        ))
        ;
        return $fields;
    }

    public function exec(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
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
                            'extra_type_description' => $type['extra_type_description']
                        );
                        $this->ExtraType->insert($data, self::$type_id);
                        $this->setAlert('The record with the ID %id% was successfull inserted.',
                            array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                    }
                }
                elseif (!empty($type['delete'])) {
                    // delete the extra field
                    $extra_groups = $this->ExtraGroup->selectTypeID(self::$type_id);
                    if (!empty($extra_groups)) {
                        foreach ($extra_groups as $extra_group) {
                            if (false === ($group = $this->EventGroup->select($extra_group['group_id']))) {
                                throw new \Exception("Missing the event group with the ID {$extra_group['group_id']}");
                            }
                            $this->setAlert('This extra field is used in the event group %group%. First remove the extra field from the event group.',
                                array('%group%' => $group['group_name']), self::ALERT_TYPE_WARNING);
                        }
                    }
                    else {
                        $this->ExtraType->delete(self::$type_id);
                        $this->setAlert('The record with the ID %id% was successfull deleted.',
                            array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                        self::$type_id = -1;
                    }
                }
                else {
                    // update a extra field
                    $data = array(
                        'extra_type_type' => $type['extra_type_type'],
                        'extra_type_description' => $type['extra_type_description']
                    );
                    $this->ExtraType->update($data, self::$type_id);
                    $this->setAlert('The record with the ID %id% was successfull updated.',
                        array('%id%' => self::$type_id), self::ALERT_TYPE_SUCCESS);
                }

                if (self::$type_id > 0) {
                    // get the actual extra field
                    $type = $this->ExtraType->select(self::$type_id);
                    $fields = $this->getFormFields($type);
                    $form = $fields->getForm();
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
            '@phpManufaktur/Event/Template', 'admin/edit.extra.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('group'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

}
