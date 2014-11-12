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
use phpManufaktur\miniShop\Data\Shop\Group as DataGroup;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;

class Group extends Admin
{

    protected $dataGroup = null;
    protected $dataBase = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\miniShop\Control\Admin\Admin::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->dataGroup = new DataGroup($app);
        $this->dataBase = new DataBase($app);
    }

    /**
     * Get the form to create or edit a article group
     *
     * @param array $data
     */
    protected function getGroupForm($data=array())
    {
        $status_array = array();
        $types = $this->dataGroup->getStatusTypes();
        foreach ($types as $type) {
            $type_name = $this->app['utils']->humanize($type);
            $status_array[$type] = $this->app['translator']->trans($type_name);
        }

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('name', 'text', array(
            'data' => isset($data['name']) ? $data['name'] : ''
        ))
        ->add('status', 'choice', array(
            'choices' => $status_array,
            'empty_value' => false,
            'data' => isset($data['status']) ? $data['status'] : 'ACTIVE'
        ));
        if (isset($data['id']) && ($data['id'] > 0)) {
            $form->add('article_group_delete_checkbox', 'checkbox', array(
                'required' => false
            ));
        }
        else {
            $form->add('article_group_delete_checkbox', 'hidden');
        }

        $form->add('description', 'textarea', array(
            'data' => isset($data['description']) ? $data['description'] : '',
            'required' => false
        ));
        $form->add('base_name', 'choice', array(
            'choices' => $this->dataBase->selectBaseNames(),
            'empty_value' => '- please select -',
            'data' => isset($data['base_name']) ? $data['base_name'] : null,
            'label' => 'Base configuration'
        ));
        $form->add('base_id', 'hidden', array(
            'data' => isset($data['base_id']) ? $data['base_id'] : -1
        ));



        return $form->getForm();
    }

    /**
     * Controller to check the article group dialog
     *
     * @param Application $app
     * @return string
     */
    public function ControllerEditCheck(Application $app)
    {
        $this->initialize($app);

        $form = $this->getGroupForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if ($data['article_group_delete_checkbox']) {
                // delete the article group
                $this->dataGroup->delete($data['id']);
                $this->setAlert('The article group with the ID %id% has successfull deleted',
                    array('%id%' => $data['id']), self::ALERT_TYPE_SUCCESS);
                return $this->Controller($app);
            }
            else {
                // delete this item to avoid conflicts with the data table
                unset($data['article_group_delete_checkbox']);
            }

            // sanitize the name
            $data['name'] = strtoupper($this->app['utils']->sanitizeLink($data['name']));
            $data['name'] = str_replace('-', '_', $data['name']);

            if ($data['id'] < 1) {
                // this is a new record
                if ($this->dataGroup->existsName($data['name'])) {
                    $this->setAlert('The name <strong>%name%</strong> is already in use, please select another one.',
                        array('%name%' => $data['name']), self::ALERT_TYPE_WARNING);
                }
                else {
                    // insert the record
                    $data['base_id'] = $this->dataBase->getIDbyName($data['base_name']);
                    $data['id'] = $this->dataGroup->insert($data);
                    $this->setAlert('Succesful created a new article group', array(), self::ALERT_TYPE_SUCCESS);
                }
            }
            else {
                $old = $this->dataGroup->select($data['id']);
                if (($old['name'] !== $data['name']) && $this->dataGroup->existsName($data['name'], $data['id'])) {
                    $this->setAlert('The name <strong>%name%</strong> is already in use, please select another one.',
                        array('%name%' => $data['name']), self::ALERT_TYPE_WARNING);
                }
                else {
                    $this->dataGroup->update($data['id'], $data);
                    $this->setAlert('The article group has successful updated.', array(), self::ALERT_TYPE_SUCCESS);
                }
            }
            // get the form with the actual data
            $form = $this->getGroupForm($data);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.group.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('group'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to create or edit a article group
     *
     * @param Application $app
     * @param integer $group_id
     */
    public function ControllerEdit(Application $app, $group_id)
    {
        $this->initialize($app);

        $data = array();
        if ($group_id > 0) {
            if (false === ($data = $this->dataGroup->select($group_id))) {
                $this->setAlert('The record with the ID %id% does not exist!',
                    array('%id%' => $group_id), self::ALERT_TYPE_DANGER);
            }
        }
        $form = $this->getGroupForm($data);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.group.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('group'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Show the article groups list for the miniShop
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        if (false === ($groups = $this->dataGroup->selectAll())) {
            // no article groups available, check if a base is defined
            if ($this->dataBase->count() < 1) {
                $this->setAlert('Please create a base configuration to start with your miniShop!', array(), self::ALERT_TYPE_INFO);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/list.group.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('group'),
                'groups' => $groups,
                'alert' => $this->getAlert()
            ));
    }

}
