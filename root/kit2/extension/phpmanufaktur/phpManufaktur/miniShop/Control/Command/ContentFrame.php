<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Command;

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentFrame extends Basic
{
    /**
     * Controller to create the iFrame for the Content kitCommands.
     * Execute the route /content/action
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initParameters($app);

        $query = $this->getCMSgetParameters();
        if (isset($query['action']) && ($query['action'] == 'basket')) {
            return $this->createIFrame('/minishop/action');
        }
        // get the preferred template
        $template = $this->getPreferredTemplateStyle();

        if (!file_exists(MANUFAKTUR_PATH.'/miniShop/Template/'.$template.'/command/content.twig')) {
            $template = 'default';
        }

        $subRequest = Request::create('/minishop/action', 'GET', array(
            'pid' => self::getParameterID()));

        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    public function ControllerBasket(Application $app)
    {
        $this->initParameters($app);
        return $this->createIFrame('/minishop/basket');
    }
}
