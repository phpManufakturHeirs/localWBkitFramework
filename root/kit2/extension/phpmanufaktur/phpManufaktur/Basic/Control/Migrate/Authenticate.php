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
use Symfony\Component\Form\FormFactory;

class Authenticate extends Alert
{
    private static $isAuthenticated = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $migrate_guid = $app['session']->get('MIGRATE_GUID');
        if (is_null($migrate_guid)) {
            self::$isAuthenticated = false;
        }
        else {
            // check the GUID
            if (is_null(FRAMEWORK_UID)) {
                throw new \Exception('Invalid FRAMEWORK_UID in framework.json - please check the configuration!');
            }
            self::$isAuthenticated = (FRAMEWORK_UID === $migrate_guid);
        }
    }

    /**
     * Remove the current sessions
     *
     */
    public function removeSession()
    {
        $this->app['session']->remove('MIGRATE_GUID');
        $this->app['session']->remove('AUTHENTICATE_ERRORS');
    }

    /**
     * @return the $isAuthenticated
     */
    public static function IsAuthenticated ()
    {
        return Authenticate::$isAuthenticated;
    }

    /**
     * Get the Authentication form
     *
     * @return FormFactory
     */
    protected function getAuthenticationForm()
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('framework_uid', 'text')
            ->getForm();
    }

    /**
     * Controller to show a dialog to input the FRAMEWORK_UID
     *
     * @param Application $app
     */
    public function ControllerAuthenticate(Application $app)
    {
        $this->initialize($app);

        $form = $this->getAuthenticationForm();

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/authenticate.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'authentication_locked' => ($app['session']->get('AUTHENTICATE_ERRORS') > 3)
        ));
    }

    /**
     * Controller to check the Authentication
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerAuthenticateCheck(Application $app)
    {
        $this->initialize($app);

        $form = $this->getAuthenticationForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if ($data['framework_uid'] === FRAMEWORK_UID) {
                $app['session']->set('MIGRATE_GUID', $data['framework_uid']);
                $app['session']->remove('AUTHENTICATE_ERRORS');

                $subRequest = Request::create('/start/', 'GET');
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }

            $count = $app['session']->get('AUTHENTICATE_ERRORS', 0);
            $app['session']->set('AUTHENTICATE_ERRORS', $count+1);

            $this->setAlert('The <var>FRAMEWORK_UID</var> you typed in was invalid.', array(), self::ALERT_TYPE_WARNING);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create('/authenticate/', 'GET');
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
