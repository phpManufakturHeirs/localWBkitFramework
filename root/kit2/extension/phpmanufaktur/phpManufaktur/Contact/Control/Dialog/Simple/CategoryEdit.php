<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\ExtraType;


/**
 * Dialog to create and edit categories for PERSON and COMPANY contacts
 *
 * @author ralf.hertsch@phpmanufaktur.de
 *
 */
class CategoryEdit extends Dialog {

    protected $CategoryTypeData = null;
    protected static $category_type_id = -1;
    protected $ExtraType = null;
    protected $ExtraCategory = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($options);
        }
    }

    protected function initialize($options=null)
    {
        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'message' => isset($options['template']['message']) ? $options['template']['message'] : 'backend/message.twig',
                'edit' => isset($options['template']['edit']) ? $options['template']['edit'] : 'backend/simple/edit.category.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/simple/category/edit',
                'extra' => isset($options['route']['extra']) ? $options['route']['extra'] : '/admin/contact/simple/extra/list'
            )
        ));
        $this->CategoryTypeData = new CategoryType($this->app);
        $this->ExtraCategory = new ExtraCategory($this->app);
        $this->ExtraType = new ExtraType($this->app);
    }

    /**
     * @param number $category_type_id
     */
    public function setCategoryID($category_type_id)
    {
        self::$category_type_id = $category_type_id;
    }

    /**
     * Use form.factory to create the form for the categories
     *
     * @param form $category
     */
    protected function getForm($category)
    {
        // get the extra fields for this group
        $extra_field_ids = $this->ExtraCategory->selectTypeIDByCategoryTypeID(self::$category_type_id);

        $form = $this->app['form.factory']->createBuilder('form', $category)
        ->add('category_type_id', 'hidden', array(
            'data' => $category['category_type_id']
        ))
        ->add('category_type_name', 'text', array(
            'label' => 'Category name',
            'read_only' => ($category['category_type_id'] > 0) ? true : false,
            'data' => $category['category_type_name']
        ))
        ->add('category_type_description', 'textarea', array(
            'label' => 'Category description',
            'required' => false,
            'data' => $category['category_type_description']
        ))
        ->add('category_extra_fields', 'hidden', array(
            'data' => implode(',', $extra_field_ids)
        ));

        // insert the extra fields
        $choice_extra_field = $this->ExtraType->getArrayForTwig();
        foreach ($extra_field_ids as $type_id) {
            $type = $this->ExtraType->select($type_id);
            $form->add("extra_field_".$type_id, 'choice', array(
                'choices' => array($type['extra_type_type'] => ucfirst(strtolower($type['extra_type_type']))),
                'empty_value' => '- delete field -',
                'multiple' => false,
                'required' => false,
                'label' => ucfirst(str_replace('_', ' ', strtolower($type['extra_type_name']))),
                'data' => $type['extra_type_type']
            ));
            // remove the type name from the possible selections
            unset($choice_extra_field[$type['extra_type_name']]);
        }

        // add selection for an extra field
        $form->add('add_extra_field', 'choice', array(
            'choices' => $choice_extra_field,
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Add extra field'
        ));

        return $form->getForm();
    }

    /**
     * Return the category record for the actual category ID or a default record
     *
     * @return multitype:number string
     */
    protected function getCategory()
    {
        if (self::$category_type_id > 0) {
            if (false === ($category = $this->CategoryTypeData->select(self::$category_type_id))) {
                $this->setMessage('The category type with the ID %category_id% does not exists!',
                    array('%category_id%' => self::$category_type_id));
                self::$category_type_id = -1;
            }
        }

        if (self::$category_type_id < 1) {
            // set default values
            $category = array(
                'category_type_id' => -1,
                'category_type_name' => '',
                'category_type_description' => ''
            );
        }
        return $category;
    }

    /**
     * Default controller for the Categories
     *
     * @param Application $app
     * @param string $category_type_id
     * @return string
     */
    public function controller(Application $app, $category_type_id=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($category_type_id)) {
            $this->setCategoryID($category_type_id);
        }
        return $this->exec();
    }

    /**
     * Return the Categroy edit dialog
     *
     * @return string category list
     */
    public function exec($extra=null)
    {
        // check if a category ID isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['category_type_id'])) {
            self::$category_type_id = $form_request['category_type_id'];
        }

        // get the form with the actual category ID
        $form = $this->getForm($this->getCategory());

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $category = $form->getData();

                $category_extra_fields = (!empty($category['category_extra_fields'])) ? explode(',', $category['category_extra_fields']) : array();
                foreach ($category_extra_fields as $extra_type_id) {
                    if (is_null($category["extra_field_$extra_type_id"])) {
                        // delete the field
                        $this->ExtraCategory->deleteTypeByCategoryTypeID($extra_type_id, self::$category_type_id);
                    }
                }
                if (!is_null($category['add_extra_field'])) {
                    // ok - add an extra field!
                    if (false === ($type = $this->ExtraType->selectName($category['add_extra_field']))) {
                        throw new \Exception(sprintf('The extra type field %s does not exists!', $category['add_extra_field']));
                    }
                    $this->ExtraCategory->insert($type['extra_type_id'], self::$category_type_id);
                }


                if (!is_null($this->app['request']->request->get('delete', null))) {
                    // delete the category
                    $this->CategoryTypeData->delete($category['category_type_id']);
                    $this->setMessage('The category %category_type_name% was successfull deleted.',
                        array('%category_type_name%' => $category['category_type_name']));
                    self::$category_type_id = -1;
                }
                else {
                    // insert or edit a category
                    if ($category['category_type_id'] > 0) {
                        // update the record
                        $data = array(
                            'category_type_description' => !is_null($category['category_type_description']) ? $category['category_type_description'] : ''
                        );
                        $this->CategoryTypeData->update($data, self::$category_type_id);
                        $this->setMessage('The category %category_type_name% was successfull updated',
                            array('%category_type_name%' => $category['category_type_name']));
                    }
                    else {
                        // insert a new record
                        $category_type_name = str_replace(' ', '_', strtoupper(trim($category['category_type_name'])));
                        $matches = array();
                        if (preg_match_all('/[^A-Z0-9_$]/', $category_type_name, $matches)) {
                            // name check fail
                            $this->setMessage('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                                array('%identifier%' => 'Category'));
                        }
                        else {
                            // insert the record
                            $data = array(
                                'category_type_name' => $category_type_name,
                                'category_type_description' => !is_null($category['category_type_description']) ? $category['category_type_description'] : ''
                            );
                            $this->CategoryTypeData->insert($data, self::$category_type_id);
                            $this->setMessage('The category %category_type_name% was successfull inserted.',
                                array('%category_type_name%' => $category_type_name));
                        }
                    }
                }
                // get the form with the actual category ID
                $form = $this->getForm($this->getCategory());
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setMessage('The form is not valid, please check your input and try again!');
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['edit']),
            array(
                'message' => $this->getMessage(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra
            ));
    }
}
