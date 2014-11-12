<?php

/**
 * kfHelloWorld
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/HelloWorld
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace thirdParty\HelloWorld\Control;

use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommandBasic;
use Silex\Application;

class HelloSiteModified extends kitCommandBasic
{
    public function exec(Application $app)
    {
        $this->app = $app;
        $this->initParameters();

        if (!in_array(CMS_TYPE, array('WebsiteBaker', 'LEPTON'))) {
            // SiteModified support only WebsiteBaker or LEPTON
            return $this->app['translator']->trans('~~ <b>Error</b>: SiteModified support only WebsiteBaker or LEPTON CMS. ~~');
        }

        try {
            // select the last modified date from the CMS `pages` table
            $SQL = "SELECT MAX(`modified_when`) AS `site_modified` FROM `".CMS_TABLE_PREFIX."pages`";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }

        if (!isset($result['site_modified'])) {
            // something went wrong, got no date!
            return $this->app['translator']->trans('~~ <b>Error</b>: SiteModified got no valid modification date! ~~');
        }

        // got the translated date
        $datetime = date($this->app['translator']->trans('d/m/Y \a\t H:i'), $result['site_modified']);

        // return the complete response
        return $this->app['translator']->trans('This site was last modified on %datetime%.',
            array('%datetime%' => $datetime));
    }
}
