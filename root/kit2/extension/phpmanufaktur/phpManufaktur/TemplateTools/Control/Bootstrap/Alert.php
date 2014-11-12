<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\Bootstrap;

use Silex\Application;

class Alert
{
    protected $app = null;
    
    protected static $options = array(
        'type' => self::INFO,
        'parameter' => array(),
        'translate' => true,
        'dismissable' => true,
        'template_directory' => '@pattern/bootstrap/function/alert/'
    );
    
    const SUCCESS = 'alert-success';
    const INFO = 'alert-info';
    const WARNING = 'alert-warning';
    const DANGER = 'alert-danger';
    
    protected static $alert_types = array(
        self::SUCCESS,
        self::INFO,
        self::WARNING,
        self::DANGER    	
    );
    
    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Check the $options and set self::$options
     *
     * @param array $options
     */
    protected function checkOptions($options)
    {
        if (isset($options['type']) && in_array(strtolower($options['type']), self::$alert_types)) {
            self::$options['type'] = strtolower($options['type']);
        }
        if (isset($options['parameter']) && is_array($options['parameter'])) {
            self::$options['parameter'] = $options['parameter'];
        }
        if (isset($options['translate']) && is_bool($options['translate'])) {
            self::$options['translate'] = $options['translate'];
        }
        if (isset($options['dismissable']) && is_bool($options['dismissable'])) {
            self::$options['dismissable'] = $options['dismissable'];
        }
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
    }
    
    /**
     * Use the Bootstrap Alert Component to alert a message
     * 
     * @param string $message
     * @param array $options
     * @param boolean $prompt
     * @return string rendered alert
     */
    public function alert($message='', $options=array(), $prompt=true)
    {
        // first check the options
        $this->checkOptions($options);
        
        $message = trim($message);
        if (empty($message)) {
            self::$options['type'] = self::DANGER;
            $message = $this->app['translator']->trans('Missing the message to alert!');
        }
        
        if (self::$options['translate'])  {
            $message = $this->app['translator']->trans($message, self::$options['parameter']);
        }
        
        $alert = $this->app['twig']->render(
            self::$options['template_directory'].'alert.twig', array(
                'message' => $message,
                'options' => self::$options));
        
        if ($prompt) {
            echo $alert;
        }
        return $alert;
    }
}