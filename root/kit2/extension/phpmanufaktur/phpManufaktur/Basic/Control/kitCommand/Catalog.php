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

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;
use Carbon\Carbon;
use phpManufaktur\Basic\Control\ExtensionCatalog;
use phpManufaktur\Basic\Data\ExtensionCatalog as CatalogData;

class Catalog extends Basic
{
    protected static $config = null;
    protected static $catalog = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new CatalogConfiguration($app);
        self::$config = $Configuration->getConfiguration();

        $update_catalog = false;
        if ((self::$config['catalog']['refresh']['minutes'] < 1) ||
            (self::$config['catalog']['refresh']['last_check'] == '0000-00-00 00:00:00')) {
            // do always refresh the catalog
            $update_catalog = true;
        }
        else {
            $check = Carbon::createFromFormat('Y-m-d H:i:s', self::$config['catalog']['refresh']['last_check']);
            $check->addMinutes(self::$config['catalog']['refresh']['minutes']);
            $now = Carbon::create();
            if ($check->lt($now)) {
                // update the catalog information
                $update_catalog = true;
            }
        }

        if ($update_catalog) {
            // update the local catalog
            $ExtensionCatalog = new ExtensionCatalog($app);
            $ExtensionCatalog->getOnlineCatalog();
            // update the catalog configuration
            self::$config['catalog']['refresh']['last_check'] = date('Y-m-d H:i:s');
            $Configuration->setConfiguration(self::$config);
            $Configuration->saveConfiguration();
        }

        // read the catalog
        $CatalogData = new CatalogData($app);
        $extensions = $CatalogData->selectAll();

        $locale = $this->getCMSlocale();

        self::$catalog = array();
        foreach ($extensions as $extension) {
            $info = json_decode(base64_decode($extension['info']), true);
            $extension['info'] = $info;
            if (isset($info['description'][$locale])) {
                // description for the actual locale is available
                $description = array(
                    'title' => isset($info['description'][$locale]['title']) ? $info['description'][$locale]['title'] : '',
                    'short' => isset($info['description'][$locale]['short']) ? $info['description'][$locale]['short'] : '',
                    'long' => isset($info['description'][$locale]['long']) ? $info['description'][$locale]['long'] : '',
                    'url' => isset($info['description'][$locale]['url']) ? $info['description'][$locale]['url'] : ''
                );
            }
            else {
                // use the english default
                $description = array(
                    'title' => isset($info['description']['en']['title']) ? $info['description']['en']['title'] : '',
                    'short' => isset($info['description']['en']['short']) ? $info['description']['en']['short'] : '',
                    'long' => isset($info['description']['en']['long']) ? $info['description']['en']['long'] : '',
                    'url' => isset($info['description']['en']['url']) ? $info['description']['en']['url'] : ''
                );
            }
            $extension['description'] = $description;
            self::$catalog[] = $extension;
        }

    }

    /**
     * Controller return the complete Extension Catalog
     *
     * @param Application $app
     */
    public function controllerCatalog(Application $app)
    {
        $this->initParameters($app);
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'kitcommand/catalog.twig'),
            array(
                'catalog' => self::$catalog,
                'basic' => $this->getBasicSettings()
        ));
    }

    /**
     * Controller to create the iFrame for the Catalog
     *
     * @param Application $app
     */
    public function controllerCreateIFrame(Application $app)
    {
        $this->initParameters($app);
        return $this->createIFrame('/basic/catalog');
    }

}
