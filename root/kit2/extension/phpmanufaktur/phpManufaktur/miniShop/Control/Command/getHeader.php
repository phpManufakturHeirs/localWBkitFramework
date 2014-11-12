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
use phpManufaktur\miniShop\Data\Shop\Article;

class getHeader
{
    /**
     * Controller return the title, description and keywords for the page settings
     *
     * @param Application $app
     * @param integer $article_id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function Controller(Application $app, $article_id)
    {
        $dataArticle = new Article($app);
        if (false === ($article = $dataArticle->select($article_id))) {
            return $app->json(array('status' => "The miniShop article ID $article_id does not exist!"), 404);
        }

        return $app->json(array(
            'title' => $article['seo_title'],
            'description' => $article['seo_description'],
            'keywords' => $article['seo_keywords']));
    }
}
