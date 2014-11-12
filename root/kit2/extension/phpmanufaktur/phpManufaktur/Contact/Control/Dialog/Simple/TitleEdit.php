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
use phpManufaktur\Contact\Data\Contact\Title;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

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
                'edit' => isset($options['template']['edit']) ? $options['template']['edit'] : 'pattern/admin/simple/edit.title.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/title/edit',
                'list' => isset($options['route']['list']) ? $options['route']['list'] : '/admin/contact/title/list'
            )
        ));
        $this->TitleData = new Title($this->app);
    }

    /**
     * Set the title ID
     *
     * @param number $title_id
     */
    public function setTitleID($title_id)
    {
        self::$title_id = $title_id;
    }

    /**
     * Get the title form
     *
     * @param array $title
     */
    protected function getForm($title)
    {
        return $this->app['form.factory']->createBuilder('form', $title)
            ->add('title_id', 'hidden')
            ->add('title_identifier', 'text', array(
                'label' => 'Name',
                'read_only' => ($title['title_id'] > 0) ? true : false
            ))
            ->add('title_short', 'text', array(
                'label' => 'Short name'
            ))
            ->add('title_long', 'text', array(
                'label' => 'Long name'
            ))
            ->add('delete', 'checkbox', array(
                'required' => false
            ))
            ->getForm();
    }

    /**
     * Get the title
     *
     * @return array
     */
    protected function getTitle()
    {
        if (self::$title_id > 0) {
            if (false === ($title = $this->TitleData->select(self::$title_id))) {
                $this->setAlert('The title with the ID %title_id% does not exists!',
                    array('%title_id%' => self::$title_id), self::ALERT_TYPE_WARNING);
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

    /**
     * Controller to create or edit the title
     *
     * @param Application $app
     * @param integer $title_id
     * @return string
     */
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
                if (isset($title['delete']) && $title['delete']) {
                    // delete the title
                    $this->TitleData->delete($title['title_id']);
                    $this->setAlert('The record with the ID %id% was successfull deleted.',
                        array('%id%' => $title['title_id']), self::ALERT_TYPE_SUCCESS);
                    // subrequest to the title list
                    $subRequest = Request::create(self::$options['route']['list'], 'GET');
                    return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }
                else {
                    // insert or edit a title
                    if ($title['title_id'] > 0) {
                        // update the record
                        if (empty($title['title_short']) || (strlen($title['title_short']) < 2)) {
                            // missing the short title!
                            $this->setAlert('Please define a short name for the title!');
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
                            $this->setAlert('The record with the ID %id% was successfull updated.',
                                array('%id%' => $title['title_id']), self::ALERT_TYPE_SUCCESS);
                            // subrequest to the title list
                            $subRequest = Request::create(self::$options['route']['list'], 'GET');
                            return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                        }
                    }
                    else {
                        // insert a new record
                        $title_identifier = str_replace(' ', '_', strtoupper(trim($title['title_identifier'])));
                        $matches = array();
                        if (preg_match_all('/[^A-Z0-9_$]/', $title_identifier, $matches)) {
                            // name check fail
                            $this->setAlert('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                                array('%identifier%' => 'Title'), self::ALERT_TYPE_WARNING);
                        }
                        elseif (empty($title['title_short']) || (strlen($title['title_short']) < 2)) {
                            // missing the short title!
                            $this->setAlert('Please define a short name for the title!');
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
                            $this->setAlert('The record with the ID %id% was successfull inserted.',
                                array('%id%' => self::$title_id), self::ALERT_TYPE_SUCCESS);
                            // subrequest to the title list
                            $subRequest = Request::create(self::$options['route']['list'], 'GET');
                            return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                        }
                    }
                }
                // get the form with the actual category ID
                $form = $this->getForm($this->getTitle());
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['edit']),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }

}
