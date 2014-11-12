<?php

/**
 * kfCKEditor
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\CKEditor\Control;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\flexContent\Control\Command\Tools;
use phpManufaktur\flexContent\Data\Content\TagType;

class hashtagLink
{

    public function ControllerDialog(Application $app)
    {
        $language = $app['session']->get('FLEXCONTENT_EDIT_CONTENT_LANGUAGE', null);

        $Hashtags = new TagType($app);
        $linklist = $Hashtags->selectHashtagLinkList($language);

        $Tools = new Tools($app);

        $xml = '<data><pageslist>';

        if (is_array($linklist)) {
            foreach ($linklist as $link) {
                $permalink_base = $Tools->getPermalinkBaseURL($link['language']);
                $url = $permalink_base.'/buzzword/'.$link['tag_permalink'];
                $xml .= '<item id="'.$url.'" value="#'.$link['tag_name'].'" />';
            }
        }
        $xml .= '</pageslist></data>';
        return new Response($xml, 200, array('Content-Type' => 'application/xml'));
    }
}
