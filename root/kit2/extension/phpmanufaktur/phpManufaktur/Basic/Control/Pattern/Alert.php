<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Pattern;

use Silex\Application;

class Alert
{
    protected $app = null;

    private static $alert = '';
    private static $alert_type = self::ALERT_TYPE_INFO;

    private static $alert_namespace = null;
    private static $alert_template =  null;

    const ORIGIN_ALERT_NAMESPACE = '@phpManufaktur/Basic/Template';
    const ORIGIN_ALERT_TEMPLATE = 'pattern/bootstrap/alert.twig';

    const ALERT_TYPE_INFO = 'alert-info';
    const ALERT_TYPE_SUCCESS = 'alert-success';
    const ALERT_TYPE_WARNING = 'alert-warning';
    const ALERT_TYPE_DANGER = 'alert-danger';

    protected static $alert_type_array = array(
        self::ALERT_TYPE_SUCCESS,
        self::ALERT_TYPE_INFO,
        self::ALERT_TYPE_WARNING,
        self::ALERT_TYPE_DANGER
    );

    /**
     * Constructor
     */
    public function __construct(Application $app=null)
    {
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;

        $this->setAlertNamespace(self::ORIGIN_ALERT_NAMESPACE);
        $this->setAlertTemplate(self::ORIGIN_ALERT_TEMPLATE);
    }

    /**
     * Set an alert.
     *
     * Alerts of type alert-warning will be logged as DEBUG,
     * alerts of type alert-danger will be logged as ERROR.
     * If $debug all other alerts will be logged as DEBUG.
     *
     * @param string $alert the alert to display
     * @param array $params parameters for translation
     * @param string $type alert-success, alert-info, alert-warning or alert-danger
     * @param string $debug if true the alert will be also logged
     * @param array $debug_info additional debugging information
     */
    public function setAlert($alert, $params=array(), $type=self::ALERT_TYPE_INFO, $debug=false, $debug_info=array())
    {
        $this->setAlertType($type);

        if ($type == self::ALERT_TYPE_DANGER) {
            $this->app['monolog']->addError(strip_tags($this->app['translator']->trans($alert, $params, 'messages', 'en')), $debug_info);
        }
        elseif ($debug || ($type == self::ALERT_TYPE_WARNING)) {
            $this->app['monolog']->addDebug(strip_tags($this->app['translator']->trans($alert, $params, 'messages', 'en')), $debug_info);
        }

        try {
            self::$alert .= $this->app['twig']->render($this->app['utils']->getTemplateFile(
                self::$alert_namespace,
                self::$alert_template),
                array(
                    'content' => $this->app['translator']->trans($alert, $params),
                    'type' => $this->getAlertType()
                ));
        }
        catch (\Exception $e) {
            try {
                // fall back to the orgin namespace & templage and try again ...
                self::$alert .= $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    self::ORIGIN_ALERT_NAMESPACE, self::ORIGIN_ALERT_TEMPLATE),
                    array(
                        'content' => $this->app['translator']->trans($alert, $params),
                        'type' => $this->getAlertType()
                    ));
                $this->app['monolog']->addDebug('setAlert() fall back to origin namespace and template because namespace: '.
                    self::$alert_namespace.' and template: '. self::$alert_template.
                    ' caused an exception.', array(__METHOD__, __LINE__));
            }
            catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
    }

    /**
     * Take the given alert without any change ...
     *
     * @param string $alert
     */
    public function setAlertUnformatted($alert)
    {
        self::$alert .= $alert;
    }

    /**
     * Return all formatted alerts
     *
     * @return string
     */
    public function getAlert()
    {
        return self::$alert;
    }

    /**
     * Check if an alert isset
     *
     * @return boolean
     */
    public function isAlert()
    {
        return (!empty(self::$alert));
    }

    /**
     * Reset all alerts
     */
    public function clearAlert()
    {
        self::$alert = '';
    }

    /**
     * Set the alert type
     *
     * @param string $type
     * @throws \Exception
     */
    public function setAlertType($type)
    {
        if (!in_array(strtolower($type), self::$alert_type_array)) {
            $this->app['monolog']->addError("Unexpected alert type: '$type', allowed are only the types alert-success, alert-info, alert-warning or alert-danger.");
            $type = self::ALERT_TYPE_INFO;
        }
        self::$alert_type = strtolower($type);
    }

    /**
     * Return the active alert type
     *
     * @return string alert type
     */
    public function getAlertType()
    {
        return self::$alert_type;
    }

    /**
     * Set the namespace for the alert template
     *
     * @param string $namespace
     */
    public function setAlertNamespace($namespace)
    {
        self::$alert_namespace = $namespace;
    }

    /**
     * Get the namespace of the alert template
     *
     * @return string
     */
    public function getAlertNamespace()
    {
        return self::$alert_namespace;
    }

    /**
     * Set the template for the alert
     *
     * @param string $template
     */
    public function setAlertTemplate($template)
    {
        self::$alert_template = $template;
    }

    /**
     * Get the template name for the alert
     *
     * @return string
     */
    public function getAlertTemplate()
    {
        return self::$alert_template;
    }

    /**
     * Prompt the active Alert(s) using the BASIC Bootstrap template alert.twig
     *
     * @return rendered alert template
     * @see promptAlertFramework()
     */
    public function promptAlert()
    {
        if (!$this->isAlert()) {
            $this->setAlert('Oooops, missing the alert which should be prompted here ... ', array(), self::ALERT_TYPE_WARNING);
        }
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/alert.twig'),
            array(
                'basic' => array(
                    'alert' => $this->getAlert(),
                    'cms' => array(
                        'locale' => $this->app['translator']->getLocale()
                    )
                )
            ));
    }


    /**
     * Prompt the active Alert(s) using the kitFramework BACKEND/ADMIN template alert.twig
     *
     * @return rendered alert template
     * @see promptAlert()
     */
    public function promptAlertFramework()
    {
        if (!$this->isAlert()) {
            $this->setAlert('Oooops, missing the alert which should be prompted here ... ', array(), self::ALERT_TYPE_WARNING);
        }
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => 'framework'
            ));
    }

}
