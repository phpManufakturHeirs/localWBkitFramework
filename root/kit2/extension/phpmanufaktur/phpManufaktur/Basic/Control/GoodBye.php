<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\Basic\Control;

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
        $parameter_str = !empty($parameters) ? '?'.http_build_query($parameters) : '';
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

        if (!is_null($content = $app['request']->query->get('content'))) {
            $content = urldecode($content);
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/goodbye.twig'), array(
                'basic' => $this->getBasicSettings(),
                'content' => $content
            )
        );
    }
}
