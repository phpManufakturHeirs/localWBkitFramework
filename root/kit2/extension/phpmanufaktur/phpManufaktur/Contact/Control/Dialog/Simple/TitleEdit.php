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
use phpManufaktur\Contact\Data\Contact\Title;

class TitleEdit extends Dialog {

    protected $TitleData = null;
    protected static $title_id = -1;

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
                'edit' => isset($options['template']['edit']) ? $options['template']['edit'] : 'backend/simple/edit.title.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/simple/title/edit'
            )
        ));
        $this->TitleData = new Title($this->app);
    }

      /**
     * @param number $title_id
     */
    public function setTitleID($title_id)
    {
        self::$title_id = $title_id;
    }

    protected function getForm($title)
    {
        return $this->app['form.factory']->createBuilder('form', $title)
            ->add('title_id', 'hidden')
            ->add('title_identifier', 'text', array(
                'label' => 'Identifier',
                'read_only' => ($title['title_id'] > 0) ? true : false
            ))
            ->add('title_short', 'text', array(
                'label' => 'Short name'
            ))
            ->add('title_long', 'text', array(
                'label' => 'Long name'
            ))
            ->getForm();
    }

    protected function getTitle()
    {
        if (self::$title_id > 0) {
            if (false === ($title = $this->TitleData->select(self::$title_id))) {
                $this->setMessage('The title with the ID %title_id% does not exists!',
                    array('%title_id%' => self::$title_id));
                self::$title_id = -1;
            }
        }

        if (self::$title_id < 1) {
            // set default values
            $title = array(
                'title_id' => -1,
                'title_identifier' => '',
                'title_short' => '',
                'title_long' => ''
            );
        }
        return $title;
    }

    public function controller(Application $app, $title_id=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($title_id)) {
            $this->setTitleID($title_id);
        }
        return $this->exec();
    }

    /**
     * Return the TITLE edit dialog
     *
     * @return string title dialog
     */
    public function exec($extra=null)
    {
        // check if a title ID isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['title_id'])) {
            self::$title_id = $form_request['title_id'];
        }

        // get the form with the actual title ID
        $form = $this->getForm($this->getTitle());

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $title = $form->getData();
                if (!is_null($this->app['request']->request->get('delete', null))) {
                    // delete the title
                    $this->TitleData->delete($title['title_id']);
                    $this->setMessage('The title %title_identifier% was successfull deleted.',
                        array('%title_identifier%' => $title['title_identifier']));
                    self::$title_id = -1;
                }
                else {
                    // insert or edit a title
                    if ($title['title_id'] > 0) {
                        // update the record
                        if (empty($title['title_short']) || (strlen($title['title_short']) < 2)) {
                            // missing the short title!
                            $this->setMessage('Please define a short name for the title!');
                        }
                        else {
                            if (empty($title['title_long'])) {
                                $title['title_long'] = $title['title_short'];
                            }
                            $data = array(
                                'title_short' => $title['title_short'],
                                'title_long' => $title['title_long']
                            );
                            $this->TitleData->update($data, $title['title_id']);
                            $this->setMessage('The record with the ID %id% was successfull updated.',
                                array('%id%' => $title['title_id']));
                        }
                    }
                    else {
                        // insert a new record
                        $title_identifier = str_replace(' ', '_', strtoupper(trim($title['title_identifier'])));
                        $matches = array();
                        if (preg_match_all('/[^A-Z0-9_$]/', $title_identifier, $matches)) {
                            // name check fail
                            $this->setMessage('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                                array('%identifier%' => 'Title'));
                        }
                        elseif (empty($title['title_short']) || (strlen($title['title_short']) < 2)) {
                            // missing the short title!
                            $this->setMessage('Please define a short name for the title!');
                        }
                        else {
                            // insert the record
                            if (empty($title['title_long'])) {
                                $title['title_long'] = $title['title_short'];
                            }
                            $data = array(
                                'title_identifier' => $title_identifier,
                                'title_short' => $title['title_short'],
                                'title_long' => $title['title_long']
                            );
                            $this->TitleData->insert($data, self::$title_id);
                            $this->setMessage('The title %title_identifier% was successfull inserted.',
                                array('%title_identifier%' => $title_identifier));
                        }
                    }
                }
                // get the form with the actual category ID
                $form = $this->getForm($this->getTitle());
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
