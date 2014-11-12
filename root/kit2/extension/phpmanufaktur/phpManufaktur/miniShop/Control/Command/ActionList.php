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

use Silex\Application;

class ActionList extends CommandBasic
{
    protected $Basket = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $this->Basket = new Basket($app);
    }

    /**
     * Return the list with the desired articles
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function showList()
    {
        $result = null;

        $base = null;
        $groups = null;

        if (!is_null(self::$parameter['groups'])) {
            // show the list for a article group
            $checks = strpos(self::$parameter['groups'], ',') ? explode(',', self::$parameter['groups']) : array(self::$parameter['groups']);
            $groups = array();
            foreach ($checks as $check) {
                $check = trim($check);
                if (!$this->dataGroup->existsName($check)) {
                    $this->setAlert('The article group <strong>%group%</strong> does not exist, please check the kitCommand!',
                        array('%group%' => $check), self::ALERT_TYPE_DANGER);
                    continue;
                }
                else {
                    $groups[] = $check;
                }
            }
        }

        if (!is_null(self::$parameter['base'])) {
            if (!$this->dataBase->existsName(self::$parameter)) {
                $this->setAlert('The base configuration <strong>%base%</strong> does not exist, please check the kitCommand!',
                    array('%base%', self::$parameter['base']));
            }
            else {
                $base = self::$parameter['base'];
            }
        }

        if (is_null($groups) && is_null($base)) {
            if ($this->dataBase->countActive() > 1) {
                $this->setAlert('There exists more than one base configurations, so you must set a base or a group as parameter!',
                    array(), self::ALERT_TYPE_DANGER);
            }
            elseif (false !== ($base_config = $this->dataBase->selectAllActive())) {
                $base = $base_config[0]['name'];
                if (false !== ($active_groups = $this->dataGroup->selectAllActiveByBase($base))) {
                    $groups = array();
                    foreach ($active_groups as $group) {
                        $groups[] = $group['name'];
                    }
                }
            }
        }

        $articles = null;

        if (!is_null($groups)) {
            $articles = $this->dataArticle->selectByGroup($groups, self::$parameter['limit'],
                self::$parameter['order_by'], self::$parameter['order_direction']);
        }

        if (isset($articles[0]['base_id'])) {
            $base = $this->dataBase->select($articles[0]['base_id']);
        }

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/list.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'config' => self::$config,
                'parameter' => self::$parameter,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'articles' => $articles,
                'basket' => $this->Basket->getBasket(),
                'base' => $base
            ));

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    /**
     * Controller to show an article list
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initParameters($app);

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'minishop')
            && isset($GET['action']) && ($GET['action'] == 'list')) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if ($key == 'command') {
                    continue;
                }
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        if (isset(self::$parameter['alert'])) {
            $this->setAlertUnformatted(base64_decode(self::$parameter['alert']));
        }

        if (isset(self::$parameter['status']) && !empty(self::$parameter['status'])) {
            if (strpos(self::$parameter['status'], ',')) {
                $status = array();
                $stats = explode(',', self::$parameter['status']);
                foreach ($stats as $stat) {
                    $stat = trim($stat);
                    if (!empty($stat)) {
                        $status[] = $stat;
                    }
                }
            }
            else {
                $status = array(trim(self::$parameter['status']));
            }
            self::$parameter['status'] = $status;
        }

        return $this->showList();
    }
}
