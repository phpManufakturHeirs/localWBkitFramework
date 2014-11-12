<?php

/**
 * kitFramework::Migrate
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Migrate;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Migrate extends Alert
{
    protected $Authenticate = null;
    protected static $CMS_PATH = null;
    protected static $CMS_URL = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->Authenticate = new Authenticate($app);

        self::$CMS_PATH = substr(FRAMEWORK_PATH, 0, strpos(FRAMEWORK_PATH, '/kit2'));
        self::$CMS_URL = substr(FRAMEWORK_URL, 0, strpos(FRAMEWORK_URL, '/kit2'));

        $app['translator']->setLocale($app['session']->get('LOCALE', 'en'));
    }

    /**
     * Get form to check the CMS and kitFramework path and URL
     *
     * @param array $data
     */
    protected function formUrlCheck($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('existing_cms_url', 'hidden', array(
            'data' => isset($data['existing_cms_url']) ? $data['existing_cms_url'] : ''
        ))
        ->add('cms_url', 'text', array(
            'data' => self::$CMS_URL
        ))
        ->getForm();
    }

    /**
     * Form to check the MySQL settings
     *
     * @param array $data
     */
    protected function formMySqlCheck($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('cms_url_changed', 'hidden', array(
            'data' => isset($data['cms_url_changed']) ? $data['cms_url_changed'] : null
        ))
        ->add('existing_cms_url', 'hidden', array(
            'data' => isset($data['existing_cms_url']) ? $data['existing_cms_url'] : null
        ))
        ->add('cms_url', 'hidden', array(
            'data' => isset($data['cms_url']) ? $data['cms_url'] : null
        ))
        ->add('existing_db_host', 'hidden', array(
            'data' => isset($data['existing_db_host']) ? $data['existing_db_host'] : null
        ))
        ->add('db_host', 'text', array(
            'data' => isset($data['db_host']) ? $data['db_host'] : null
        ))
        ->add('existing_db_port', 'hidden', array(
            'data' => isset($data['existing_db_port']) ? $data['existing_db_port'] : null
        ))
        ->add('db_port', 'text', array(
            'data' => isset($data['db_port']) ? $data['db_port'] : null
        ))
        ->add('existing_db_name', 'hidden', array(
            'data' => isset($data['existing_db_name']) ? $data['existing_db_name'] : null
        ))
        ->add('db_name', 'text', array(
            'data' => isset($data['db_name']) ? $data['db_name'] : null
        ))
        ->add('existing_db_username', 'hidden', array(
            'data' => isset($data['existing_db_username']) ? $data['existing_db_username'] : null
        ))
        ->add('db_username', 'text', array(
            'data' => isset($data['db_username']) ? $data['db_username'] : null
        ))
        ->add('existing_db_password', 'hidden', array(
            'data' => isset($data['existing_db_password']) ? $data['existing_db_password'] : null
        ))
        ->add('db_password', 'password', array(
            'required' => false,
            'always_empty' => false,
            'attr' => array(
                'value' => isset($data['db_password']) ? $data['db_password'] : null
            )
        ))
        ->add('existing_table_prefix', 'hidden', array(
            'data' => isset($data['existing_table_prefix']) ? $data['existing_table_prefix'] : null
        ))
        ->add('table_prefix', 'text', array(
            'data' => isset($data['table_prefix']) ? $data['table_prefix'] : null
        ))

        ->getForm();
    }

    /**
     * Form to check the E-Mail settings
     *
     * @param array $data
     */
    protected function formEMailCheck($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('cms_url_changed', 'hidden', array(
            'data' => isset($data['cms_url_changed']) ? $data['cms_url_changed'] : null
        ))
        ->add('existing_cms_url', 'hidden', array(
            'data' => isset($data['existing_cms_url']) ? $data['existing_cms_url'] : null
        ))
        ->add('cms_url', 'hidden', array(
            'data' => isset($data['cms_url']) ? $data['cms_url'] : null
        ))
        ->add('db_host', 'hidden', array(
            'data' => isset($data['db_host']) ? $data['db_host'] : null
        ))
        ->add('db_port', 'hidden', array(
            'data' => isset($data['db_port']) ? $data['db_port'] : null
        ))
        ->add('db_name', 'hidden', array(
            'data' => isset($data['db_name']) ? $data['db_name'] : null
        ))
        ->add('db_username', 'hidden', array(
            'data' => isset($data['db_username']) ? $data['db_username'] : null
        ))
        ->add('db_password', 'hidden', array(
            'data' => isset($data['db_password']) ? $data['db_password'] : null
        ))
        ->add('table_prefix', 'hidden', array(
            'data' => isset($data['table_prefix']) ? $data['table_prefix'] : null
        ))
        ->add('mysql_changed', 'hidden', array(
            'data' => isset($data['mysql_changed']) ? $data['mysql_changed'] : null
        ))
        ->add('server_email', 'text', array(
            'data' => isset($data['server_email']) ? $data['server_email'] : null
        ))
        ->add('server_name', 'text', array(
            'data' => isset($data['server_name']) ? $data['server_name'] : null
        ))
        ->add('smtp_host', 'text', array(
            'data' => isset($data['smtp_host']) ? $data['smtp_host'] : null
        ))
        ->add('smtp_port', 'text', array(
            'data' => isset($data['smtp_port']) ? $data['smtp_port'] : null
        ))
        ->add('smtp_username', 'text', array(
            'data' => isset($data['smtp_username']) ? $data['smtp_username'] : null,
            'required' => false
        ))
        ->add('smtp_password', 'password', array(
            'required' => false,
            'always_empty' => false,
            'attr' => array(
                'value' => isset($data['smtp_password']) ? $data['smtp_password'] : null
            )
        ))
        ->add('smtp_encryption', 'text', array(
            'data' => isset($data['smtp_encryption']) ? $data['smtp_encryption'] : null,
            'required' => false
        ))
        ->add('smtp_auth_mode', 'text', array(
            'data' => isset($data['smtp_auth_mode']) ? $data['smtp_auth_mode'] : null,
            'required' => false
        ))
        ->getForm();
    }

    /**
     * Read the constants from the CMS config.php
     *
     * @param array reference $config
     * @return boolean
     */
    protected function readCMSconfig(&$config=array())
    {
        // check if token is a constant value
        function is_constant($token)
        {
            return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
            $token == T_LNUMBER || $token == T_DNUMBER;
        }

        // strip quotation marks form token value
        function strip($value)
        {
            return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
        }

        if (false === ($code = @file_get_contents(self::$CMS_PATH.'/config.php'))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => '/config.php'), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => CMS_PATH.'/config.php', 'method' => __METHOD__));
            return false;
        }

        $defines = array();
        $state = 0;
        $key = '';
        $value = '';

        // get all TOKENS from the code
        $tokens = token_get_all($code);
        $token = reset($tokens);
        while ($token) {
            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    // do nothing
                }
                elseif ($token[0] === T_STRING && strtolower($token[1]) === 'define') {
                    $state = 1;
                }
                elseif ($state === 2 && is_constant($token[0])) {
                    $key = $token[1];
                    $state = 3;
                }
                elseif ($state === 4 && is_constant($token[0])) {
                    $value = $token[1];
                    $state = 5;
                }
            }
            else {
                $symbol = trim($token);
                if ($symbol === '(' && $state === 1) {
                    $state = 2;
                }
                elseif ($symbol === ',' && $state === 3) {
                    $state = 4;
                }
                elseif ($symbol === ')' && $state === 5) {
                    $defines[strip($key)] = strip($value);
                    $state = 0;
                }
            }
            $token = next($tokens);
        }

        $config = $defines;
        return true;
    }

    /**
     * This controller start the migration process
     *
     * @param Application $app
     */
    public function ControllerStart(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $next_step = true;

        $config = array();
        if (!$this->readCMSconfig($config)) {
            $next_step = false;
        }

        $cms_url = null;
        if (isset($config['WB_URL'])) {
            $cms_url = $config['WB_URL'];
        }
        elseif (isset($config['CAT_URL'])) {
            $cms_url = $config['CAT_URL'];
        }
        else {
            $cms_url = null;
        }

        $data = array(
            'existing_cms_url' => $cms_url,
        );
        $form = $this->formUrlCheck($data);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/url.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'next_step' => $next_step
        ));
    }

    /**
     * Controller to remove the current session data and start with a new one
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerSessionRemove(Application $app)
    {
        $this->initialize($app);

        $this->Authenticate->removeSession();

        $subRequest = Request::create('/start/', 'GET');
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to check the CMS URL
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerUrlCheck(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $form = $this->formUrlCheck();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            $checked = true;
            if (!filter_var($data['cms_url'])) {
                $this->setAlert('The URL <strong>%url%</strong> is not valid, please check your input!',
                    array('%url%' => $data['cms_url']), self::ALERT_TYPE_DANGER);
                $checked = false;
            }

            if ($checked) {
                $changes = array(
                    'cms_url_changed' => ($data['existing_cms_url'] !== $data['cms_url']),
                    'existing_cms_url' => $data['existing_cms_url'],
                    'cms_url' => $data['cms_url']
                );

                $subRequest = Request::create('/mysql/', 'POST', $changes);
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create('/start/', 'GET');
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to check the MySQL settings
     *
     * @param Application $app
     * @throws \Exception
     */
    public function ControllerMySql(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        if ((null === ($cms_url_changed = $app['request']->get('cms_url_changed'))) ||
            (null === ($existing_cms_url = $app['request']->get('existing_cms_url'))) ||
            (null === ($cms_url = $app['request']->get('cms_url')))) {
            // invalid submission
            throw new \Exception('Missing one or more POST data!');
        }

        $next_step = true;

        $config = array();
        if (!$this->readCMSconfig($config)) {
            $next_step = false;
        }

        $data = array();
        if ($next_step) {
            $data = array(
                'cms_url_changed' => $cms_url_changed,
                'existing_cms_url' => $existing_cms_url,
                'cms_url' => $cms_url,
                'existing_db_host' => isset($config['CAT_DB_HOST']) ? $config['CAT_DB_HOST'] : $config['DB_HOST'],
                'db_host' => isset($config['CAT_DB_HOST']) ? $config['CAT_DB_HOST'] : $config['DB_HOST'],
                'existing_db_port' => isset($config['CAT_DB_PORT']) ? $config['CAT_DB_PORT'] : $config['DB_PORT'],
                'db_port' => isset($config['CAT_DB_PORT']) ? $config['CAT_DB_PORT'] : $config['DB_PORT'],
                'existing_db_name' => isset($config['CAT_DB_NAME']) ? $config['CAT_DB_NAME'] : $config['DB_NAME'],
                'db_name' => isset($config['CAT_DB_NAME']) ? $config['CAT_DB_NAME'] : $config['DB_NAME'],
                'existing_db_username' => isset($config['CAT_DB_USERNAME']) ? $config['CAT_DB_USERNAME'] : $config['DB_USERNAME'],
                'db_username' => isset($config['CAT_DB_USERNAME']) ? $config['CAT_DB_USERNAME'] : $config['DB_USERNAME'],
                'existing_db_password' => isset($config['CAT_DB_PASSWORD']) ? $config['CAT_DB_PASSWORD'] : $config['DB_PASSWORD'],
                'db_password' => isset($config['CAT_DB_PASSWORD']) ? $config['CAT_DB_PASSWORD'] : $config['DB_PASSWORD'],
                'existing_table_prefix' => isset($config['CAT_TABLE_PREFIX']) ? $config['CAT_TABLE_PREFIX'] : $config['TABLE_PREFIX'],
                'table_prefix' => isset($config['CAT_TABLE_PREFIX']) ? $config['CAT_TABLE_PREFIX'] : $config['TABLE_PREFIX']
            );
        }

        $form = $this->formMySqlCheck($data);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/mysql.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'next_step' => $next_step
        ));
    }

    /**
     * Controller to check the submitted MySQL settings
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerMySqlCheck(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $form = $this->formMySqlCheck();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if (($data['existing_db_host'] !== $data['db_host']) ||
                ($data['existing_db_name'] !== $data['db_name']) ||
                ($data['existing_db_password'] !== $data['db_password']) ||
                ($data['existing_db_port'] !== $data['db_port']) ||
                ($data['existing_db_username'] !== $data['db_username']) ||
                ($data['existing_table_prefix'] !== $data['table_prefix'])) {
                $data['mysql_changed'] = true;
            }
            else {
                $data['mysql_changed'] = false;
            }

            $subRequest = Request::create('/email/', 'POST', $data);
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create('/mysql/', 'POST', $app['request']->get('form'));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to check the email settings
     *
     * @param Application $app
     * @throws \Exception
     */
    public function ControllerEMail(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $data = $app['request']->request->all();

        if (!isset($data['cms_url']) || !isset($data['db_host'])) {
            // invalid submission
            throw new \Exception('Missing one or more POST data!');
        }

        $swiftmailer = $app['utils']->readJSON(FRAMEWORK_PATH.'/config/swift.cms.json');

        foreach ($swiftmailer as $key => $value) {
            $data[strtolower($key)] = $value;
        }

        $form = $this->formEMailCheck($data);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/email.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView()
        ));
    }

    /**
     * Controller to check the submitted email settings
     *
     * @param Application $app
     * @return string
     */
    public function ControllerEMailCheck(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $form = $this->formEMailCheck();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            $swiftmailer = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/swift.cms.json');

            $data['email_changed'] = false;
            foreach ($swiftmailer as $key => $value) {
                // attention: different data types, don't use strict comparison!
                if ($data[strtolower($key)] != $value) {
                    $data['email_changed'] = true;
                    break;
                }
            }

            if (!$data['cms_url_changed'] && !$data['mysql_changed'] && !$data['email_changed']) {
                $this->setAlert('There a no settings changed, nothing to do ...', array(), self::ALERT_TYPE_INFO);
            }
            else {
                // process the changed settings for the CMS
                $this->processCmsChanges($data);

                // process the changed settings for the kitFramework
                $this->processFrameworkChanges($data);
            }

            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/migrate/result.twig'),
                array(
                    'alert' => $this->getAlert()
                ));
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create('/email/', 'POST', $app['request']->get('form'));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Process all changes which affect the CMS
     *
     * @param array $data
     */
    protected function processCmsChanges($data)
    {
        // read the CMS /config.php into an array
        $config_lines = file(self::$CMS_PATH.'/config.php');

        if ($data['cms_url_changed']) {
            // the CMS URL has changed
            $new_lines = array();
            foreach ($config_lines as $line) {
                if (((strpos($line, 'CAT_URL') !== false) || (strpos($line, 'WB_URL') !== false)) &&
                    (strpos($line, $data['existing_cms_url']) !== false)) {
                    $new_lines[] = str_replace($data['existing_cms_url'], $data['cms_url'], $line);
                    $this->setAlert('Changed the CMS URL from %old_url% to %new_url%.',
                        array('%old_url%' => $data['existing_cms_url'], '%new_url%' => $data['cms_url']), self::ALERT_TYPE_SUCCESS, true);
                }
                else {
                    $new_lines[] = $line;
                }
            }
            $config_lines = $new_lines;
        }

        if ($data['mysql_changed']) {
            // the MySQL settings has changed
            $new_lines = array();
            $old_mysql = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/doctrine.cms.json');
            $modified = false;
            foreach ($config_lines as $line) {
                if ((strpos($line, 'DB_HOST') !== false) && (strpos($line, $old_mysql['DB_HOST']))) {
                    $new_lines[] = str_replace($old_mysql['DB_HOST'], $data['db_host'], $line);
                    $modified = true;
                }
                elseif ((strpos($line, 'DB_PORT') !== false) && (strpos($line, $old_mysql['DB_PORT']))) {
                    $new_lines[] = str_replace($old_mysql['DB_PORT'], $data['db_port'], $line);
                    $modified = true;
                }
                elseif ((strpos($line, 'DB_NAME') !== false) && (strpos($line, $old_mysql['DB_NAME']))) {
                    $new_lines[] = str_replace($old_mysql['DB_NAME'], $data['db_name'], $line);
                    $modified = true;
                }
                elseif ((strpos($line, 'DB_USERNAME') !== false) && (strpos($line, $old_mysql['DB_USERNAME']))) {
                    $new_lines[] = str_replace($old_mysql['DB_USERNAME'], $data['db_username'], $line);
                    $modified = true;
                }
                elseif (strpos($line, 'DB_PASSWORD') !== false) {
                    if (empty($old_mysql['DB_PASSWORD']) && empty($data['db_password']))  {
                        // both passwords are empty - nothing to do ...
                        $new_lines[] = $line;
                    }
                    elseif (empty($old_mysql['DB_PASSWORD']) && !empty($data['db_password'])) {
                        // that's a bit tricky, we must replace the whole line
                        if (strpos($line, 'CAT_DB_PASSWORD')) {
                            // BlackCat
                            $new_lines[] = "define('CAT_DB_PASSWORD', '{$data['db_password']}');\n";
                        }
                        else {
                            // WebsiteBaker or LEPTON
                            $new_lines[] = "define('DB_PASSWORD', '{$data['db_password']}');\n";
                        }
                        $modified = true;
                    }
                    else {
                        $new_lines[] = str_replace($old_mysql['DB_PASSWORD'], $data['db_password'], $line);
                        $modified = true;
                    }
                }
                elseif ((strpos($line, 'TABLE_PREFIX') !== false) && (strpos($line, $old_mysql['TABLE_PREFIX']))) {
                    $new_lines[] = str_replace($old_mysql['TABLE_PREFIX'], $data['table_prefix'], $line);
                    $modified = true;
                }
                else {
                    $new_lines[] = $line;
                }
            }
            if ($modified) {
                $config_lines = $new_lines;
                $this->setAlert('Updated the database settings for the CMS.', array(), self::ALERT_TYPE_SUCCESS, true);
            }
        }

        if ($data['cms_url_changed'] || $data['mysql_changed']) {
            $this->app['filesystem']->copy(self::$CMS_PATH.'/config.php', self::$CMS_PATH.'/config.bak', true);
            $config = implode('', $config_lines);
            file_put_contents(self::$CMS_PATH.'/config.php', $config);
            $this->setAlert('Create CMS /config.bak and write new /config.php', array(), self::ALERT_TYPE_SUCCESS, true);
        }
    }

    /**
     * Process all changes which affect the kitFramework
     *
     * @param array $data
     */
    protected function processFrameworkChanges($data)
    {
        if ($data['cms_url_changed']) {
            // update the framework.json
            $framework_json = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/framework.json');
            $framework_json['FRAMEWORK_URL'] = $data['cms_url'].'/kit2';
            file_put_contents(FRAMEWORK_PATH.'/config/framework.json', $this->app['utils']->JSONFormat($framework_json));
            $this->setAlert('Updated the kitFramework URL to %url%.',
                array('%url%' => $framework_json['FRAMEWORK_URL']), self::ALERT_TYPE_SUCCESS, true);

            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/Event/config.event.json')) {
                // update the Event configuration
                $event = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Event/config.event.json');

                if (!empty($event['event']['subscription']['terms']['url'])) {
                    $event['event']['subscription']['terms']['url'] = str_replace($data['existing_cms_url'], $data['cms_url'], $event['event']['subscription']['terms']['url']);
                    $this->setAlert('Updated the terms & conditions URL for Event', array(), self::ALERT_TYPE_SUCCESS, true);
                }

                if (!empty($event['permalink']['cms']['url'])) {
                    $event['permalink']['cms']['url'] = str_replace($data['existing_cms_url'], $data['cms_url'], $event['permalink']['cms']['url']);
                    $this->setAlert('Updated the permalink URL for Event', array(), self::ALERT_TYPE_SUCCESS, true);
                }
            }

            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/flexContent/config.flexcontent.json')) {
                // update flexContent configuration
                $flexContent = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/flexContent/config.flexcontent.json');

                if (isset($flexContent['remote']['client'])) {
                    $this->setAlert('You have configured flexContent as Remote Client. Please check the specified remote URLs in <var>config.flexcontent.json</var>.',
                        array(), self::ALERT_TYPE_INFO, true);
                }

                // update the flexContent bootstrap.include.inc for the permanent links
                $Setup = new \phpManufaktur\flexContent\Data\Setup\Setup();
                $subdirectory = parse_url(self::$CMS_URL, PHP_URL_PATH);
                $Setup->createPermalinkRoutes($this->app, $flexContent, $subdirectory);
                $this->setAlert('Updated the flexContent bootstrap.include.inc for the permanent links.',
                    array(), self::ALERT_TYPE_SUCCESS, true);

                // create the physical directories needed by the permanent links
                $Setup->createPermalinkDirectories($this->app, $flexContent, $subdirectory, self::$CMS_PATH);
                $this->setAlert('Create the physical directories needed by the flexContent permanent links.',
                    array(), self::ALERT_TYPE_SUCCESS, true);
            }

            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/miniShop/config.minishop.json')) {
                // update the miniShop configuration
                $miniShop = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/miniShop/config.minishop.json');

                // update the miniShop bootstrap.include.inc for the permanent links
                $Setup = new \phpManufaktur\miniShop\Data\Setup\Setup();
                $subdirectory = parse_url(self::$CMS_URL, PHP_URL_PATH);
                $Setup->createPermalinkRoutes($this->app, $miniShop, $subdirectory);
                $this->setAlert('Updated the miniShop bootstrap.include.inc for the permanent links.',
                    array(), self::ALERT_TYPE_SUCCESS, true);

                // create the physical directories needed by the permanent links
                $Setup->createPermalinkDirectories($this->app, $miniShop, $subdirectory, self::$CMS_PATH);
                $this->setAlert('Create the physical directories needed by the miniShop permanent links.',
                    array(), self::ALERT_TYPE_SUCCESS, true);
            }

        }

        if ($data['mysql_changed']) {
            // update the doctrine.cms.json
            $doctrine = array(
                'DB_TYPE' => 'mysqli',
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_NAME' => $data['db_name'],
                'DB_USERNAME' => $data['db_username'],
                'DB_PASSWORD' => $data['db_password'],
                'TABLE_PREFIX' =>$data['table_prefix']
            );
            file_put_contents(FRAMEWORK_PATH.'/config/doctrine.cms.json', $this->app['utils']->JSONFormat($doctrine));
            $this->setAlert('Updated the kitFramework database settings.', array(), self::ALERT_TYPE_SUCCESS, true);
        }

        if ($data['email_changed']) {
            $email = array(
                'SERVER_EMAIL' => $data['server_email'],
                'SERVER_NAME' => $data['server_name'],
                'SMTP_HOST' => $data['smtp_host'],
                'SMTP_PORT' => intval($data['smtp_port']),
                'SMTP_USERNAME' => $data['smtp_username'],
                'SMTP_PASSWORD' => $data['smtp_password'],
                'SMTP_ENCRYPTION' => $data['smtp_encryption'],
                'SMTP_AUTH_MODE' => $data['smtp_auth_mode']
            );
            file_put_contents(FRAMEWORK_PATH.'/config/swift.cms.json', $this->app['utils']->JSONFormat($email));
            $this->setAlert('Updated the kitFramework email settings.', array(), self::ALERT_TYPE_SUCCESS, true);
        }

        // check the /config/cms.json in any case!
        $cms_json = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/cms.json');
        $cms_json['CMS_URL'] = $data['cms_url'];
        $cms_json['CMS_MEDIA_PATH'] = self::$CMS_PATH.'/media';
        $cms_json['CMS_MEDIA_URL'] = $data['cms_url'].'/media';
        file_put_contents(FRAMEWORK_PATH.'/config/cms.json', $this->app['utils']->JSONFormat($cms_json));
        $this->setAlert('Updated the kitFramework CMS settings.', array(), self::ALERT_TYPE_SUCCESS, true);

        // create the kitFramework .htaccess file in any case!
        $htaccess = file_get_contents(self::$CMS_PATH.'/modules/kit_framework/Setup/htaccess.htt');
        $subdirectory = parse_url(self::$CMS_URL, PHP_URL_PATH);
        if (!empty($subdirectory)) {
            $subdirectory = '/'.$subdirectory;
        }
        $replace = $subdirectory.'/kit2';
        $htaccess = str_replace('{RELATIVE_PATH}', $replace, $htaccess);
        file_put_contents(FRAMEWORK_PATH.'/.htaccess', $htaccess);
        $this->setAlert('Create a new .htaccess file for the kitFramework root directory.',
            array(), self::ALERT_TYPE_SUCCESS, true);

        // cleanup the /kit2/temp/cache directory
        $this->app['filesystem']->remove(FRAMEWORK_TEMP_PATH.'/cache');
        $this->setAlert('Cleanup the kitFramework cache directory.', array(), self::ALERT_TYPE_SUCCESS, true);
    }
}
