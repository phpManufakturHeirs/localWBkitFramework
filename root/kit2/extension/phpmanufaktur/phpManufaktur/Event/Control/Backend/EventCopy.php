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

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\EventSearch;
use phpManufaktur\Event\Data\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\CommandCollection\Data\Comments\CommentsPassed;

class EventCopy extends Backend
{
    protected static $columns = null;
    protected static $route = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Event\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        try {
            // search for the config file in the template directory
            $cfg_file = $this->app['utils']->getTemplateFile(
                '@phpManufaktur/Event/Template', 'admin/list.event.json', '', true);
            // get the columns to show in the list
            $cfg = $this->app['utils']->readJSON($cfg_file);
            self::$columns = isset($cfg['columns']) ? $cfg['columns'] : $this->EventData->getColumns();
        } catch (\Exception $e) {
            // the config file does not exists - use all available columns
            self::$columns = $this->EventData->getColumns();
        }
        self::$route =  array(
            'start' => '/admin/event/copy?usage='.self::$usage,
            'select' => '/admin/event/copy/id/{event_id}?usage='.self::$usage,
            'check' => '/admin/event/copy/search/check?usage='.self::$usage,
            'comments' => '/admin/event/copy/comments/check?usage='.self::$usage
        );
    }

    /**
     * Get the search form fields
     *
     * @return form.factory
     */
    protected function getSearchFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('search', 'text' );
    }

    /**
     * Controller to copy a event from another
     *
     * @param Application $app
     * @return string
     */
    public function controllerCopyEvent(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSearchFormFields();
        $form = $fields->getForm();

        $this->setAlert('Please search for the event you want to copy data from.', array(), self::ALERT_TYPE_INFO);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/copy.event.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('event_edit'),
                'alert' => $this->getAlert(),
                'events' => array(),
                'form' => $form->createView(),
                'route' => self::$route
            ));
    }


    /**
     * Check the search term, display result list or again the search dialog
     *
     * @param Application $app
     */
    public function controllerSearchCheck(Application $app)
    {
        $this->initialize($app);

        // get the form
        $fields = $this->getSearchFormFields();
        $form = $fields->getForm();
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $search = $form->getData();

            $EventSearch = new EventSearch($app);
            if (false === ($events = $EventSearch->search($search['search']))) {
                // no hits, return to the search dialog
                $this->setAlert('No hits for the search term <i>%search%</i>!',
                    array('%search%' => $search['search']), self::ALERT_TYPE_WARNING);
                return $this->controllerCopyEvent($app);
            }

            $this->setAlert('%count% hits for the search term </i>%search%</i>.',
                array('%count%' => count($events), '%search%' => $search['search']), self::ALERT_TYPE_SUCCESS);

            $this->setAlert('Please select the event you want to copy into a new one',
                array(), self::ALERT_TYPE_INFO);

            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Event/Template', 'admin/copy.event.twig'),
                array(
                    'usage' => self::$usage,
                    'toolbar' => $this->getToolbar('event_edit'),
                    'alert' => $this->getAlert(),
                    'events' => $events,
                    'columns' => self::$columns,
                    'route' => self::$route,
                    'form' => $form->createView()
                ));
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->controllerCopyEvent($app);
        }
    }

    protected function getHandleCommentsFormFields($event_id, $new_event_id)
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => $event_id
        ))
        ->add('new_event_id', 'hidden', array(
            'data' => $new_event_id
        ))
        ->add('comments', 'choice', array(
            'choices' => array(
                'IGNORE' => $this->app['translator']->trans('Ignore existing comments'),
                'PASS_FROM' => $this->app['translator']->trans('Pass comments from parent')
            ),
            'expanded' => true,
            'required' => true,
            'label' => 'Comments handling',
            'data' => 'IGNORE'
        ));
    }

    protected function HandleComments($event_id, $new_event_id)
    {
        $fields = $this->getHandleCommentsFormFields($event_id, $new_event_id);
        $form = $fields->getForm();

        $this->setAlert('Please determine the handling for the comments.', array(), self::ALERT_TYPE_INFO);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/copy.event.comments.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('event_edit'),
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'route' => self::$route
            ));
    }

    public function controllerCommentsCheck(Application $app)
    {
        $this->initialize($app);

        // get the form
        $fields = $this->getHandleCommentsFormFields(-1, -1);
        $form = $fields->getForm();
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if (isset($data['comments']) && ($data['comments'] == 'PASS_FROM')) {
                // pass the comments from the old EVENT ID to the new one
                $CommentsPassed = new CommentsPassed($app);
                $CommentsPassed->insertPassTo('EVENT', $data['event_id'], $data['new_event_id']);
            }

            // the request method must be GET not POST!
            $subRequest = Request::create("/admin/event/edit/id/".$data['new_event_id'], 'GET', array(
                'usage' => self::$usage,
                'alert' => $app['translator']->trans('This event was copied from the event with the ID %id%. Be aware that you should change the dates before publishing to avoid duplicate events!',
                    array('%id%' => $data['event_id'])),
                'alert_type' => self::ALERT_TYPE_SUCCESS
            ));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->controllerCopyEvent($app);
        }
    }


    /**
     * Controller for the selected Event ID to copy the new event from
     *
     * @param Application $app
     * @param integer $event_id
     * @return string
     */
    public function controllerCopyID(Application $app, $event_id)
    {
        $this->initialize($app);

        $EventData = new Event($app);
        if (false === ($event = $EventData->selectEvent($event_id, false))) {
            // Ooops, ID does not exists!
            $this->setAlert('The record with the ID %id% does not exists!', array('%id%' => $event_id), self::ALERT_TYPE_WARNING);
            return $this->controllerCopyEvent($app);
        }

        // unset not needed fields
        unset($event['contact']);
        unset($event['event_id']);
        unset($event['participants']);
        unset($event['rating']);

        // change the status to LOCKED
        $event['event_status'] = 'LOCKED';

        $new_event_id = -1;
        $EventData->insertEvent($event, $new_event_id, true);


        return $this->HandleComments($event_id, $new_event_id);


    }
}
