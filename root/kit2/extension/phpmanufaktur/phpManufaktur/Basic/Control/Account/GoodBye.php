<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\Basic\Control\Account;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;

class GoodBye extends Basic
{
    /**
     * Controller receive GET parameter for route /logout and redirect with looped
     * through parameters to the regular /admin/logout
     *
     * @param Application $app
     */
    public function controllerLogout(Application $app)
    {
        $parameters = $app['request']->query->all();
        $parameter_str = !empty($parameters) ? '?'.http_build_query($parameters, '', '&') : '';
        return $app->redirect(FRAMEWORK_URL."/admin/logout$parameter_str");
    }

    /**
     * Show a Goodbye dialog after logout
     *
     * @param Application $app
     */
    public function controllerGoodBye(Application $app)
    {
        $this->initParameters($app);

        if (null !== ($redirect = $app['request']->query->get('redirect'))) {
            // redirect to the given route or URL
            $parameters = $app['request']->query->all();
            unset($parameters['redirect']);
            $parameter_str = !empty($parameters) ? '?'.http_build_query($parameters, '', '&') : '';
            return $app->redirect(FRAMEWORK_URL.$redirect.$parameter_str);
        }
        if (null != ($msg = $app['request']->query->get('message'))) {
            $this->setAlert($msg, array(), self::ALERT_TYPE_INFO);
        }
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/goodbye.twig'),
            array(
                'basic' => $this->getBasicSettings(),
                'alert' => $this->getAlert(),
                'usage' => $app['request']->get('usage', 'framework')
            ));
    }
}
