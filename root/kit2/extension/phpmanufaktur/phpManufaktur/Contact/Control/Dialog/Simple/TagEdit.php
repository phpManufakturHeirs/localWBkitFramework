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
use Symfony\Component\Form\FormBuilder;
use phpManufaktur\Contact\Data\Contact\TagType as TagTypeData;

class TagEdit extends Dialog {

    protected $TagTypeData = null;
    protected static $tag_type_id = -1;

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
                'edit' => isset($options['template']['edit']) ? $options['template']['edit'] : 'backend/simple/edit.tag.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/simple/tag/edit'
            )
        ));

        $this->TagTypeData = new TagTypeData($this->app);
    }

    public function setTagID($tag_id)
    {
        self::$tag_type_id = $tag_id;
    }

    /**
     * Build the complete form with the form.factory
     *
     * @param array $contact flatten contact record
     * @return FormBuilder
     */
    protected function getForm($tag_type)
    {
       $form = $this->app['form.factory']->createBuilder('form', $tag_type)
            ->add('tag_type_id', 'hidden')
            ->add('tag_name', 'text', array(
                'required' => true,
                'read_only' => (isset($tag_type['tag_name']) && !empty($tag_type['tag_name'])) ? true : false,
                'label' => 'Tag'
            ))
            ->add('tag_description', 'textarea', array(
                'required' => false,
                'label' => 'Description'
            ));
        return $form->getForm();
    }

    /**
     * Default controller for the tags
     *
     * @param Application $app
     * @param string $tag_id
     * @return string
     */
    public function controller(Application $app, $tag_id=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($tag_id)) {
            $this->setTagID($tag_id);
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
        // check if a TAG ID isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['tag_type_id'])) {
            self::$tag_type_id = $form_request['tag_type_id'];
        }

        // get the tag record
        if (false === ($tag_type = $this->TagTypeData->select(self::$tag_type_id))) {
            $tag_type = $this->TagTypeData->getDefaultRecord();
        }

        // get the form
        $form = $this->getForm($tag_type);

        if ('POST' == $this->app['request']->getMethod()) {
            $delete = $this->app['request']->get('delete', null);
            if (!is_null($delete)) {
                // delete this tag
                $this->TagTypeData->delete(self::$tag_type_id);
                $this->setMessage('The record with the ID %id% was successfull deleted.',
                    array('%id%' => self::$tag_type_id));
                self::$tag_type_id = -1;
                $tag_type = $this->TagTypeData->getDefaultRecord();
                $form = $this->getForm($tag_type);
            }
            else {
                // the form was submitted, bind the request
                $form->bind($this->app['request']);
                if ($form->isValid()) {
                    // get the form data
                    $tag = $form->getData();
                    if (self::$tag_type_id < 1) {
                        // insert a new TAG
                        $matches = array();
                        $tag_name = str_replace(' ', '_', strtoupper($tag['tag_name']));
                        if (preg_match_all('/[^A-Z0-9_$]/', $tag_name, $matches)) {
                            // name check fail
                            $this->setMessage('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                                array('%identifier%' => 'Tag'));
                        }
                        elseif ($this->TagTypeData->existsTag($tag_name)) {
                            // the tag already exists
                            $this->setMessage('The tag type %tag_name% already exists!', array('%tag_name%' => $tag_name));
                        }
                        else {
                            $data = array(
                                'tag_name' => $tag_name,
                                'tag_description' => !is_null($tag['tag_description']) ? $tag['tag_description'] : ''
                            );
                            $this->TagTypeData->insert($data, self::$tag_type_id);
                            $this->setMessage('The record with the ID %id% was successfull inserted.', array('%id%' => self::$tag_type_id));
                        }
                    }
                    else {
                        // update an existing tag
                        $this->TagTypeData->update($tag, self::$tag_type_id);
                        $this->setMessage('The record with the ID %id% was successfull updated.', array('%id%' => self::$tag_type_id));
                    }

                    // get the changed tag record
                    if (false === ($tag_type = $this->TagTypeData->select(self::$tag_type_id))) {
                        $tag_type = $this->TagTypeData->getDefaultRecord();
                    }
                    // get the form
                    $form = $this->getForm($tag_type);
                }
                else {
                    // general error (timeout, CSFR ...)
                    $this->setMessage('The form is not valid, please check your input and try again!');
                }
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
