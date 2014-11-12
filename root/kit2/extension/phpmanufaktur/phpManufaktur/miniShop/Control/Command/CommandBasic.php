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
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\miniShop\Data\Shop\Group as DataGroup;

class CommandBasic extends Basic
{
    protected static $config = null;
    protected static $parameter = null;

    protected $dataArticle = null;
    protected $dataBase = null;
    protected $dataGroup = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->dataArticle = new DataArticle($app);
        $this->dataBase = new DataBase($app);
        $this->dataGroup = new DataGroup($app);

        self::$parameter = $this->getCommandParameters();

        // check wether to use the minishop.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        self::$parameter['id'] = (isset(self::$parameter['id']) && is_numeric(self::$parameter['id'])) ? intval(self::$parameter['id']) : null;

        self::$parameter['groups'] = (isset(self::$parameter['groups']) && !empty(self::$parameter['groups'])) ? self::$parameter['groups'] : null;
        self::$parameter['base'] = (isset(self::$parameter['base']) && !empty(self::$parameter['base'])) ? self::$parameter['base'] : null;

        self::$parameter['image_max_width'] = (isset(self::$parameter['image_max_width']) && is_numeric(self::$parameter['image_max_width'])) ? intval(self::$parameter['image_max_width']) : 200;
        self::$parameter['image_max_height'] = (isset(self::$parameter['image_max_height']) && is_numeric(self::$parameter['image_max_height'])) ? intval(self::$parameter['image_max_height']) : 200;

        self::$parameter['rating'] = (isset(self::$parameter['rating']) && ((self::$parameter['rating'] == 0) || (strtolower(self::$parameter['rating']) == 'false'))) ? false : true;
        self::$parameter['comments'] = (isset(self::$parameter['comments']) && ((self::$parameter['comments'] == 1) || (strtolower(self::$parameter['comments']) == 'true'))) ? true : false;

        self::$parameter['limit'] = (isset(self::$parameter['limit']) && is_numeric(self::$parameter['limit'])) ? intval(self::$parameter['limit']) : -1;
        self::$parameter['order_by'] = (isset(self::$parameter['order_by'])) ? strtolower(self::$parameter['order_by']) : 'publish_date';
        self::$parameter['order_direction'] = isset(self::$parameter['order_direction']) ? strtoupper(self::$parameter['order_direction']) : 'DESC';

        self::$parameter['thumbnail_max_width'] = (isset(self::$parameter['thumbnail_max_width']) && is_numeric(self::$parameter['thumbnail_max_width'])) ? intval(self::$parameter['thumbnail_max_width']) : 150;
        self::$parameter['thumbnail_max_height'] = (isset(self::$parameter['thumbnail_max_height']) && is_numeric(self::$parameter['thumbnail_max_height'])) ? intval(self::$parameter['thumbnail_max_height']) : 150;

    }

    /**
     * Build the default parameter array for the JSON repsonse to enable autoload
     * of jQuery and CSS. Responding functions can extend the returned array
     * with settings i.e. for robots or canonical links
     *
     * @return array
     */
    protected function getResponseParameter()
    {
        // set the parameters for jQuery and CSS
        $params = array();
        $params['library'] = null;
        if (self::$parameter['check_jquery']) {
            if (self::$config['libraries']['enabled'] &&
                !empty(self::$config['libraries']['jquery'])) {
                // load all predefined jQuery files for the miniShop
                foreach (self::$config['libraries']['jquery'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    $params['library'] .= $library;
                }
            }
        }
        if (self::$parameter['load_css']) {
            if (self::$config['libraries']['enabled'] &&
            !empty(self::$config['libraries']['css'])) {
                // load all predefined CSS files for the miniShop
                foreach (self::$config['libraries']['css'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    // attach to 'library' not to 'css' !!!
                    $params['library'] .= $library;
                }
            }

            // set the CSS parameter
            $params['css'] = 'miniShop,css/minishop.min.css,'.$this->getPreferredTemplateStyle();
        }

        return $params;
    }
}
