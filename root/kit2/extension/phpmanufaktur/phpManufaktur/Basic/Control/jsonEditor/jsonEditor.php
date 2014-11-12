<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\jsonEditor;

use Silex\Application;
use Symfony\Component\Finder\Finder;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;

class jsonEditor extends Alert
{
    protected static $config = null;
    protected $Configuration = null;
    protected static $json_path = null;
    protected static $usage = null;
    protected static $locale = null;

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        self::$usage = $app['request']->get('usage', 'framework');

        $this->Configuration = new Configuration($app);
        self::$config = $this->Configuration->getConfiguration();

        if (self::$usage != 'framework') {
            // set the locale from the CMS locale
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
        }
    }

    /**
     * Scan for the kitFramework configuration files
     *
     */
    protected function scanJSONfiles()
    {
        $jsonFiles = new Finder();
        $jsonFiles
            ->files()
            ->name('*.json')
            ->in(FRAMEWORK_PATH.'/config')
            ->in(MANUFAKTUR_PATH)
            ->in(CMS_MEDIA_PATH)
            ->in(FRAMEWORK_MEDIA_PATH)
            ->sortByName();

        // exclude all specified *.json files
        foreach (self::$config['exclude']['file'] as $file) {
            $jsonFiles->notName($file);
        }
        // exclude the specified directories
        $jsonFiles->exclude(self::$config['exclude']['directory']);

        $json_array = array();
        foreach ($jsonFiles as $file) {
            $realpath = $file->getRealpath();
            if (strpos($realpath, realpath(FRAMEWORK_PATH.'/config')) === 0) {
                $json_array[$realpath] = $file->getBasename();
            }
            elseif (strpos($realpath, realpath(CMS_MEDIA_PATH)) === 0) {
                $json_array[$realpath] = substr($realpath, strlen(realpath(CMS_PATH)));
            }
            elseif (strpos($realpath, realpath(FRAMEWORK_MEDIA_PATH)) === 0) {
                $json_array[$realpath] = substr($realpath, strlen(realpath(FRAMEWORK_PATH)));
            }
            else {
                $json_array[$realpath] = substr($realpath, strlen(realpath(MANUFAKTUR_PATH)));
            }
        }

        self::$config['last_scan'] = date('Y-m-d H:i:s');
        self::$config['configuration_files'] = $json_array;

        $this->Configuration->setConfiguration(self::$config);
        $this->Configuration->saveConfiguration();

        $this->setAlert('Successful scanned the kitFramework for *.json configuration files', array(), self::ALERT_TYPE_SUCCESS, true);
    }

    /**
     * Get the form to select a configuration file
     *
     * @return FormBuilder
     */
    protected function getFormEditor()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('json_path', 'hidden', array(
            'data' => self::$json_path
        ))
        ->add('configuration_file', 'choice', array(
            'choices' => self::$config['configuration_files'],
            'empty_value' => '- please select -',
            'data' => self::$json_path
        ))
        ->getForm();
    }

    /**
     * Try to find a help information for the current loaded configuration file
     *
     * @return string|NULL
     */
    protected function getHelp()
    {
        if (!is_null(self::$json_path)) {
            $json_file = basename(self::$json_path);
            $replace = array(
                '%CMS_URL%' => CMS_URL,
                '%FRAMEWORK_URL%' => FRAMEWORK_URL
            );
            if (isset(self::$config['help'][$json_file])) {
                $help = $this->app['translator']->trans(self::$config['help'][$json_file], $replace);
            }
            else {
                $help = $this->app['translator']->trans('Sorry, there is currently no information available about <strong>%file%</strong>, please suggest a hint and help to improve the Configuration Editor!',
                    array('%file%' => $json_file));
            }
        }
        else {
            // null indicate that no configuration file is processed!
            $help = null;
        }
        return $help;
    }

    /**
     * Show the complete configuration editor dialog
     *
     * @return string
     */
    protected function showEditor()
    {
        $form = $this->getFormEditor();

        $json_content = null;
        if (!is_null(self::$json_path)) {
            $json_content = file_get_contents(self::$json_path);
        }
        elseif (!$this->isAlert()) {
            $this->setAlert('Please select the configuration file you want to edit.', array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/json.editor.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'json_content' => $json_content,
                'json_path' => self::$json_path,
                'json_file' => basename(self::$json_path),
                'help' => $this->getHelp()
            ));
    }

    /**
     * Controller to save the current configuration file
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ControllerSaveFile(Application $app)
    {
        $this->initialize($app);

        if (null == ($json_path = $app['request']->request->get('json_path'))) {
            throw new \Exception('Missing the GET parameter `json_path`!');
        }
        if (null == ($json_content = $app['request']->request->get('json_content'))) {
            throw new \Exception('Missing the GET parameter `json_content`!');
        }
        $json_path = urldecode($json_path);

        file_put_contents($json_path, $app['utils']->JSONFormat(json_decode($json_content, true)));

        $this->setAlert('The configuration file <strong>%file%</strong> was successful saved.',
            array('%file%' => basename($json_path)), self::ALERT_TYPE_SUCCESS);
        return $app->json(array('alert' => $this->getAlert()));
    }

    public function ControllerOpenFile(Application $app, $filename)
    {
        $this->initialize($app);

        // search for the given configuration file
        $hits = array();
        foreach (self::$config['configuration_files'] as $path => $item) {
            if (strtolower(basename($path)) === strtolower($filename)) {
                $hits[] = $path;
            }
        }
        if (count($hits) == 1) {
            // exactly one hit
            self::$json_path = $hits[0];
            $this->setAlert('Load the configuration file <strong>%file%</strong> into the editor.',
                array('%file%' => $filename), self::ALERT_TYPE_SUCCESS);
        }
        elseif (count($hits) > 1) {
            self::$json_path = $hits[0];
            $this->setAlert('The controller has detected <strong>%count%</strong> configuration files with the name <strong>%filename%</strong> and loaded the first hit into the editor.',
                array('%count%' => count($hits), '%filename%' => $filename), self::ALERT_TYPE_SUCCESS);
        }
        else {
            // file not found
            $this->setAlert('Sorry, but the configuration file <strong>%filename%</strong> was not found. Please be aware that this controller may fail if you try to open a configuration file of a just installed extension, perhaps the extension must be executed first and you should also do a <key>rescan</key> for the configuration files.',
                array('%filename%' => $filename), self::ALERT_TYPE_WARNING);
        }

        // show the Editor dialog
        return $this->showEditor();
    }

    /**
     * Controller to load the selected configuration file
     *
     * @param Application $app
     * @return string
     */
    public function ControllerLoadFile(Application $app, $file='')
    {
        $this->initialize($app);

        // get the form
        $form = $this->getFormEditor();
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            self::$json_path = $data['configuration_file'];
            $this->setAlert('Load the configuration file <strong>%file%</strong> into the editor.',
                array('%file%' => basename(self::$json_path)), self::ALERT_TYPE_SUCCESS);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->showEditor();
    }

    /**
     * Controller to force a scan of the kitFramework for *.json configuration files
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerScanFramework(Application $app)
    {
        $this->initialize($app);

        $this->scanJSONfiles();

        $subRequest = Request::create('/admin/json/editor', 'GET', array(
            'usage' => self::$usage
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * General controller for the jsonEditor
     *
     * @param Application $app
     * @return string
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        if (is_null(self::$config['last_scan'])) {
            // there exists no scan!
            $this->scanJSONfiles();
        }
        else {
            $last_scan = Carbon::createFromFormat('Y-m-d H:i:s', self::$config['last_scan']);
            $last_scan->addHours(self::$config['wait_hours']);
            if ($last_scan->lt(Carbon::now())) {
                // last scan is older than specified in the configuration
                $this->scanJSONfiles();
            }
        }

        return $this->showEditor();
    }
}
