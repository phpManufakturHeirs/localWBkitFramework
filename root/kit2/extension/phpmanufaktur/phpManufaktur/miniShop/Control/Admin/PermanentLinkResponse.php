<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;
use phpManufaktur\miniShop\Data\Shop\Article;

class PermanentLinkResponse
{

    public function ControllerPermanentLink(Application $app)
    {
        try {
            if (null == ($link = $app['request']->get('link'))) {
                throw new \Exception('Missing the GET parameter `link`!');
            }

            // create the permalink
            $perma = $app['utils']->sanitizeLink($link);

            $dataArticle = new Article($app);
            if ($dataArticle->existsPermanentLink($perma)) {
                // this permalink is already in use!
                $count = $dataArticle->countPermanentLinksLikeThis($perma);
                $count++;
                // add a counter to the new permanet link
                $perma = sprintf('%s-%d', $perma, $count);
            }

            // return JSON response
            return $app->json($perma, 201);
        }
        catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
