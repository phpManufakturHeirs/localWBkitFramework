<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;

class ContactView extends Basic
{
    protected static $parameter = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$parameter = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'contact') &&
            isset($GET['action']) && ($GET['action'] == 'view')) {
            foreach ($GET as $key => $value) {
                if ($key == 'command') continue;
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        self::$parameter['map'] = isset(self::$parameter['map']) ?
            (empty(self::$parameter['map']) || (self::$parameter['map'] == 1) ||
                (strtolower(self::$parameter['map'] == 'true'))) ? true : false : false;

        self::$parameter['edit'] = isset(self::$parameter['edit']) ?
            (empty(self::$parameter['edit']) || (self::$parameter['edit'] == 1) ||
                (strtolower(self::$parameter['edit'] == 'true'))) ? true : false : false;
    }

    /**
     * Controller for the class ContactView
     *
     * @param Application $app
     * @return mixed
     */
    public function ControllerView(Application $app)
    {
        $this->initParameters($app);

        if (!isset(self::$parameter['contact_id'])) {
            $this->setAlert('Missing the parameter <b>%parameter%</b>, please check the kitCommand expression!',
                array('%parameter%' => 'contact_id'), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        if (!filter_var(self::$parameter['contact_id'], FILTER_VALIDATE_INT,
            array('options' => array('min_range' => 1)))) {
            $this->setAlert('The value of the parameter contact_id must be an integer value and greater than 0',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        if (!$app['contact']->isActive(self::$parameter['contact_id']) ||
            !$app['contact']->isPublic(self::$parameter['contact_id'])) {
            $this->setAlert('You are not allowed to access this resource!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $contact = $app['contact']->select(self::$parameter['contact_id']);
        if ($contact['contact']['contact_id'] != self::$parameter['contact_id']) {
            $this->setAlert("The contact with the ID %contact_id% does not exists!",
                array('%contact_id%' => self::$parameter['contact_id']), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $cms_parameter = $this->getCMSgetParameters();
        $origin = isset($cms_parameter['origin']) ? urldecode($cms_parameter['origin']) : null;
        $search = isset($cms_parameter['search']) ? urldecode($cms_parameter['search']) : null;

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/view.contact.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact' => $contact,
                'parameter' => self::$parameter,
                'origin' => $origin,
                'search' => $search
            ));
    }
}
