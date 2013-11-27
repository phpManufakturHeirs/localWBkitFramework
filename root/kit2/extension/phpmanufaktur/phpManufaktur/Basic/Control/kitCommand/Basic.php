<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;
use phpManufaktur\Basic\Data\kitCommandParameter;

/**
 * The elementary basic class for all kitCommands
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Basic
{
    protected $app = null;
    protected static $message = '';
    private static $cms_info = null;
    private static $parameter = null;
    private static $GET = null;
    private static $POST = null;
    private static $preferred_template = null;
    private static $frame = null;
    private static $page = null;
    private static $parameter_id = null;

    /**
     * Constructor
     *
     * @param Application $app
     * @param integer $parameter_id
     */
    public function __construct(Application $app=null, $parameter_id=-1)
    {
        if (!is_null($app)) {
            $this->initParameters($app, $parameter_id);
        }
    }

    /**
     * Initialize the class Basic and set all parameters needed for the kitCommands
     *
     * @param Application $app
     * @param integer $parameter_id
     * @throws \Exception
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        $this->app = $app;
        // set the given parameter ID
        self::$parameter_id = $parameter_id;

        $get_parameters = array();

        if (self::$parameter_id == -1) {
            $pids = array('pid', 'parameter_id');
            $GET = $this->app['request']->request->get('GET');
            foreach ($pids as $pid_name) {
                if (!is_null($this->app['request']->request->get($pid_name))) {
                    // read the parameter ID from POST
                    self::$parameter_id = $this->app['request']->request->get($pid_name);
                }
                elseif (!is_null($this->app['request']->query->get($pid_name))) {
                    // get the parameter ID from GET
                    self::$parameter_id = $this->app['request']->query->get($pid_name);
                }
                elseif ((null !== ($form = $this->app['request']->request->get('form'))) && isset($form[$pid_name])) {
                    // get the parameter ID from a form.factory POST
                    self::$parameter_id = $form[$pid_name];
                }
                elseif (isset($GET[$pid_name])) {
                    // get the parameter ID from the CMS
                    self::$parameter_id = $GET[$pid_name];
                    foreach ($GET as $key => $value) {
                        if ($key == $pid_name) continue;
                        $get_parameters[$key] = $value;
                    }
                }
            }
        }

        // init the parameter table
        $cmdParameter = new kitCommandParameter($this->app);

        if (is_null($this->app['request']->request->get('cms'))) {
            if (self::$parameter_id == '-1') {
                // create a default parameter array for the FRAMEWORK
                //throw new \Exception('Need at least CMS POST parameters or a parameter ID!');
                $params = array(
                    'cms' => array(
                        'type' => 'framework',
                        'version' => null,
                        'locale' => $this->app['request']->getPreferredLanguage(),
                        'page_id' => -1,
                        'page_url' => null,
                        'user' => array(
                            'id' => -1,
                            'name' => '',
                            'email' => ''
                        ),
                        'special' => array(
                            'post_id' => null,
                            'topic_id' => null
                        )
                    ),
                    'GET' => array(),
                    'POST' => array(),
                    'parameter' => array(
                        'frame_redirect' => 'false'
                    ),
                    'iFrame' => null
                );
            }
            elseif (false === ($params = $cmdParameter->selectParameter(self::$parameter_id))) {
                throw new \Exception('Can not get the data for parameter ID '.self::$parameter_id);
            }
            $this->app['request']->request->set('cms', $params['cms']);
            $this->app['request']->request->set('parameter', $params['parameter']);
            $this->app['request']->request->set('GET', $params['GET']);
            $this->app['request']->request->set('POST', $params['POST']);
            $this->app['request']->request->set('iFrame', $params['iFrame']);
        }

        // get the CMS information
        Basic::$cms_info = $this->app['request']->request->get('cms', array(), true);
        // get the parameters for the kitCommand
        Basic::$parameter = $this->app['request']->request->get('parameter', array(), true);
        if (!empty($get_parameters)) {
            // inject parameters form the CMS URL
            foreach ($get_parameters as $key => $value) {
                Basic::$parameter[$key] = $value;
            }
            $this->app['request']->request->set('parameter', Basic::$parameter);
        }
        // get the CMS $_GET parameters
        Basic::$GET = $this->app['request']->request->get('GET', array(), true);
        // get the CMS $_POST parameters
        Basic::$POST = $this->app['request']->request->get('POST', array(), true);
        // get the preferred template
        Basic::$preferred_template = (isset(Basic::$parameter['template'])) ? Basic::$parameter['template'] : FRAMEWORK_TEMPLATE_PREFERRED;
        // set the values for the frame

        if (null === (Basic::$frame = $this->app['request']->request->get('iFrame', null, true))) {
            // gather the iFrame parameters
            Basic::$frame = array(
                'id' => (isset(Basic::$parameter['frame_id'])) ? Basic::$parameter['frame_id'] : 'iframe-'.$this->app['utils']->createGUID(), //'kitframework_iframe',
                'name' => (isset(Basic::$parameter['frame_name'])) ? Basic::$parameter['frame_name'] : 'kitframework_iframe',
                'add' => (isset(Basic::$parameter['frame_add'])) ? Basic::$parameter['frame_add'] : 50,
                'width' => (isset(Basic::$parameter['frame_width'])) ? Basic::$parameter['frame_width'] : '100%',
                'height' => (isset(Basic::$parameter['frame_height'])) ? Basic::$parameter['frame_height'] : '400px',
                'auto' => (isset(Basic::$parameter['frame_auto']) && ((Basic::$parameter['frame_auto'] == 'false') || (Basic::$parameter['frame_auto'] == '0'))) ? false : true,
                'source' => (isset(Basic::$parameter['frame_source'])) ? Basic::$parameter['frame_source'] : '',
                'class' => (isset(Basic::$parameter['frame_class'])) ? Basic::$parameter['frame_class'] : 'kitcommand',
                'redirect' => array(
                    'active' => (isset(Basic::$parameter['frame_redirect']) && ((strtolower(Basic::$parameter['frame_redirect']) == 'false') || (Basic::$parameter['frame_redirect'] == '0'))) ? false : true,
                    'route' => (isset(Basic::$GET['redirect'])) ? Basic::$GET['redirect'] : ''
                    ),
                'tracking' => (isset(Basic::$parameter['frame_tracking']) && ((strtolower(Basic::$parameter['frame_tracking']) == 'false') || (Basic::$parameter['frame_tracking'] == '0'))) ? false : true,
                'scroll_to_id' => (isset(Basic::$parameter['frame_scroll_to_id'])) ? trim(Basic::$parameter['frame_scroll_to_id']) : ''
            );
        }

        $tracking = '';
        if (Basic::$frame['tracking'] && file_exists(FRAMEWORK_PATH.'/config/tracking.htt')) {
            // enable the tracking for the iframe
            $tracking = file_get_contents(FRAMEWORK_PATH.'/config/tracking.htt');
        }
        // set the values for the page
        Basic::$page = array(
            'title' => (isset(Basic::$parameter['frame_title'])) ? Basic::$parameter['frame_title'] : '',
            'description' => (isset(Basic::$parameter['frame_description'])) ? Basic::$parameter['frame_description'] : '',
            'keywords' => (isset(Basic::$parameter['frame_keywords'])) ? Basic::$parameter['frame_keywords'] : '',
            'robots' => (isset(Basic::$parameter['frame_robots'])) ? Basic::$parameter['frame_robots'] : 'index,follow',
            'charset' => (isset(Basic::$parameter['frame_charset'])) ? Basic::$parameter['frame_charset'] : 'UTF-8',
            'tracking' => $tracking,
            'cache' => (isset(Basic::$parameter['frame_cache']) && ((Basic::$parameter['frame_cache'] == 1) || (strtolower(Basic::$parameter['frame_cache'] == 'true')))) ? true : false
        );

        if (Basic::$parameter_id == -1) {
            $this->createParameterID();
        }

        // set the locale from the CMS locale
        $this->app['translator']->setLocale($this->getCMSlocale());
    }

    protected function createParameterID($parameter_array=null)
    {
        if (!is_array($parameter_array)) {
            $parameter_array = array(
                'cms' => Basic::$cms_info,
                'parameter' => Basic::$parameter,
                'GET' => Basic::$GET,
                'POST' => Basic::$POST,
                'iFrame' => Basic::$frame
            );
        }

        $parameter_str = json_encode($parameter_array);
        $link = md5($parameter_str);

        $cmdParameter = new kitCommandParameter($this->app);

        if (false === ($para = $cmdParameter->selectParameter($link))) {
            // create a new parameter record
            $data = array(
                'link' => $link,
                'parameter' => $parameter_str
            );
            $cmdParameter->insert($data);
        }
        Basic::$parameter_id = $link;
        $this->app['request']->request->set('parameter_id', $link);
        return Basic::$parameter_id;
    }

    /**
     * Get the parameter ID  (PID)
     *
     * @return integer
     */
    public function getParameterID()
    {
        return self::$parameter_id;
    }

    /**
     * Get the parameters submitted with the kitCommand
     *
     * @return array
     */
    public function getCommandParameters()
    {
        return (isset(Basic::$parameter)) ? Basic::$parameter : array();
    }

    /**
     * Set the parameters for the kitCommand
     *
     * @param array $parameters
     */
    public function setCommandParameters($parameters)
    {
        Basic::$parameter = $parameters;
    }

    /**
     * Get the GET parameters submitted to the CMS
     *
     * @return array
     */
    public function getCMSgetParameters()
    {
        return (isset(Basic::$GET)) ? Basic::$GET : array();
    }

    /**
     * Get the POST parameters submitted to the CMS
     *
     * @return array
     */
    public function getCMSpostParameters()
    {
        return (isset(Basic::$POST)) ? Basic::$POST : array();
    }

    /**
     * Get the information array of the CMS
     *
     * @return array
     */
    public function getCMSinfoArray()
    {
        return Basic::$cms_info;
    }

    /**
     * Get the locale set for the CMS
     *
     * @return string
     */
    public function getCMSlocale()
    {
        return isset(Basic::$cms_info['locale']) ? Basic::$cms_info['locale'] : $this->app['request']->getPreferredLanguage();
    }

    /**
     * Get the PAGE ID set for the actual page in the CMS
     *
     * @return number
     */
    public function getCMSpageID()
    {
        return isset(Basic::$cms_info['page_id']) ? Basic::$cms_info['page_id'] : -1;
    }

    /**
     * Get the URL for the actual page in the CMS
     *
     * @return string
     */
    public function getCMSpageURL()
    {
        return isset(Basic::$cms_info['page_url']) ? Basic::$cms_info['page_url'] : CMS_URL;
    }

    public function setCMSpageURL($url)
    {
        Basic::$cms_info['page_url'] = $url;
    }

    /**
     * Get the USER ID of the authenticated CMS user
     *
     * @return number USER ID or -1 if no user is authenticated
     */
    public function getCMSuserID()
    {
        return isset(Basic::$cms_info['user']['id']) ? Basic::$cms_info['user']['id'] : -1;
    }

    /**
     * Get the USER NAME of the authenticated CMS user or an empty string
     *
     * @return string
     */
    public function getCMSuserName()
    {
        return (isset(Basic::$cms_info['user']['name'])) ? Basic::$cms_info['user']['name'] : '';
    }

    /**
     * Get the USER EMAIL address of the authenticated CMS user or an empty string
     *
     * @return string
     */
    public function getCMSuserEMail()
    {
        return (isset(Basic::$cms_info['user']['email'])) ? Basic::$cms_info['user']['email'] : '';
    }

    /**
     * Get the preferred template style to use otherwise return 'default'
     *
     * @return string
     */
    public function getPreferredTemplateStyle()
    {
        return (isset(Basic::$preferred_template)) ? Basic::$preferred_template : FRAMEWORK_TEMPLATE_PREFERRED;
    }

    /**
     * Get the collected BASIC settings for the template
     *
     * @return array basic settings
     */
    public function getBasicSettings()
    {
        return array(
            'message' => $this->getMessage(),
            'cms' => Basic::$cms_info,
            'frame' => Basic::$frame,
            'page' => Basic::$page,
            'parameter_id' => Basic::$parameter_id,
            'pid' => Basic::$parameter_id
        );
    }

    /**
     * @return the $message
     */
    public function getMessage()
    {
        return Basic::$message;
    }

    /**
     * Set a message. Messages are chained and will be translated with the given
     * parameters. If $log_message = true, the message will also logged to the
     * kitFramework logfile.
     *
     * @param string $message
     * @param array $params
     * @param boolean $log_message
     */
    public function setMessage($message, $params=array(), $log_message=false)
    {
        Basic::$message .= $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'kitcommand/iframe.message.twig',
            self::$preferred_template),
            array(
                'message' => $this->app['translator']->trans($message, $params)
            ));
        if ($log_message) {
            // log this message
            $this->app['monolog']->addDebug(strip_tags($this->app['translator']->trans($message, $params, 'messages', 'en')));
        }
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public function isMessage()
    {
        return !empty(Basic::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public function clearMessage()
    {
        Basic::$message = '';
    }

    /**
     * Switch the redirection on/off if the iframe content is executed external
     *
     * @param bool $active
     */
    public function setRedirectActive($active)
    {
        Basic::$frame['redirect']['active'] = $active;
    }

    /**
     * Get the redirection setting
     *
     * @return bool
     */
    public function getRedirectActive()
    {
        return Basic::$frame['redirect']['active'];
    }

    /**
     * Set the redirect route if the content of the iframe is executed external.
     * The routing must be handled by the application, checking $_GET['redirect']
     *
     * @param string $route
     */
    public function setRedirectRoute($route)
    {
        Basic::$frame['redirect']['route'] = $route;
    }

    /**
     * Get the active redirect route
     *
     * @return string route
     */
    public function getRedirectRoute()
    {
        return Basic::$frame['redirect']['route'];
    }

    /**
     * Set the page title for the iframe content. Will be ignored if the title
     * isset by the kitCommand frame_title.
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        if (empty(Basic::$page['title'])) {
            Basic::$page['title'] = $title;
        }
    }

    /**
     * Get the page title of the iframe content
     *
     * @return string title
     */
    public function getPageTitle()
    {
        return Basic::$page['title'];
    }

    /**
     * Set the page description for the iframe content. Will be ignored if the
     * description isset by the kitCommand frame_description.
     *
     * @param string $description
     */
    public function setPageDescription($description)
    {
        if (empty(Basic::$page['description'])) {
            Basic::$page['description'] = $description;
        }
    }

    /**
     * Get the page description of the iframe content
     *
     * @return string description
     */
    public function getPageDescription()
    {
        return Basic::$page['description'];
    }

    /**
     * Set the page keywords for the iframe content. Will be ignored if the
     * keywords are set by the kitCommand frame_keywords.
     *
     * @param string $keywords
     */
    public function setPageKeywords($keywords)
    {
        if (empty(Basic::$page['keywords'])) {
            Basic::$page['keywords'] = $keywords;
        }
    }

    /**
     * Get the page keywords of the iframe content
     *
     * @return string keywords
     */
    public function getPageKeywords()
    {
        return Basic::$page['keywords'];
    }

    /**
     * Set the ID for the iframe
     *
     * @param string $id
     */
    public function setFrameID($id)
    {
        Basic::$frame['id'] = $id;
    }

    /**
     * Get the ID of the iframe
     *
     * @return string
     */
    public function getFrameID()
    {
        return Basic::$frame['id'];
    }

    /**
     * Set the name of the iframe
     *
     * @param string $name
     */
    public function setFrameName($name)
    {
        Basic::$frame['name'] = $name;
    }

    /**
     * Get the name of the iframe
     *
     * @return string
     */
    public function getFrameName()
    {
        return Basic::$frame['name'];
    }

    /**
     * Set additional heigth for the iframe
     *
     * @param integer $add
     */
    public function setFrameAdd($add)
    {
        Basic::$frame['add'] = $add;
    }

    /**
     * Get the additional height for the iframe
     *
     * @return integer
     */
    public function getFrameAdd()
    {
        return Basic::$frame['add'];
    }

    /**
     * Set the iframe width
     *
     * @param <mixed> $width string with percent or pixel value
     */
    public function setFrameWidth($width)
    {
        Basic::$frame['width'] = $width;
    }

    /**
     * Get the iframe width
     *
     * @return string
     */
    public function getFrameWidth()
    {
        return Basic::$frame['width'];
    }

    /**
     * Set the iframe height as pixel value
     *
     * @param string $height
     */
    public function setFrameHeight($height)
    {
        Basic::$frame['height'] = $height;
    }

    /**
     * Get the iframe height value
     *
     * @return string
     */
    public function getFrameHeight()
    {
        return Basic::$frame['height'];
    }

    /**
     * Switch the iframe automatic height control on or off
     *
     * @param bool $auto
     */
    public function setFrameAuto($auto)
    {
        Basic::$frame['auto'] = $auto;
    }

    /**
     * Get the iframe automatic height control value
     *
     * @return bool
     */
    public function getFrameAuto()
    {
        return Basic::$frame['auto'];
    }

    /**
     * Set the class for the iframe itself
     *
     * @param string $class
     */
    public function setFrameClass($class)
    {
        Basic::$frame['class'] = $class;
    }

    /**
     * Get the class for the iframe
     *
     * @return string
     */
    public function getFrameClass()
    {
        return Basic::$frame['class'];
    }

    public function setFrameScrollToID($class_id)
    {
        Basic::$frame['scroll_to_id'] = $class_id;
    }

    public function getFrameScrollToID()
    {
        return Basic::$frame['scroll_to_id'];
    }

    /**
     * Get the path to the kitCommand info file command.xxx.json
     *
     * @param string $command
     * @return string|boolean path on success otherwise false
     */
    public function getInfoPath($command)
    {
        $prefix = 'command';
        if ((strpos($command, 'filter:') !== false) && (strpos($command, 'filter:') == 0)) {
            $prefix = 'filter';
            $command = substr($command, strlen('filter:'));
        }
        $patterns = $this->app['routes']->getIterator();
        foreach ($patterns as $pattern) {
            $match = $pattern->getPattern();
            if ((strpos($match, "/$prefix/$command") !== false) && (strpos($match, "/$prefix/$command") == 0))  {
                if ((null !== ($info_path = $pattern->getOption('info'))) && file_exists($info_path)) {
                    return $info_path;
                }
            }
        }
        return false;
    }

    /**
     * Create a iFrame for embedding a kitCommand within a Content Management System
     *
     * @param string $start_route the start route for the iframe content
     * @param boolean $redirect if true the function check for possible redirects
     */
    public function createIFrame($start_route, $redirect=true)
    {
        $route = ($redirect && !empty(Basic::$frame['redirect']['route'])) ? Basic::$frame['redirect']['route'] : $start_route;

        Basic::$frame['source'] = FRAMEWORK_URL.$route.'?pid='.$this->getParameterID();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'kitcommand/iframe.twig',
            self::$preferred_template),
            array(
                'frame' => Basic::$frame
            ));
    }

}
