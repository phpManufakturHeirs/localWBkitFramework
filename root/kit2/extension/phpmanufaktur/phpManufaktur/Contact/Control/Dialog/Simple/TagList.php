<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\TagType;

class TagList extends Dialog {

    protected $TagTypeData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($options);
        }
    }

    protected function initialize($options=null)
    {
        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'message' => isset($options['template']['message']) ? $options['template']['message'] : 'backend/message.twig',
                'list' => isset($options['template']['list']) ? $options['template']['list'] : 'backend/simple/list.tag.twig'
            ),
            'route' => array(
                'create' => isset($options['route']['create']) ? $options['route']['create'] : '/admin/contact/simple/tag/edit',
                'edit' => isset($options['route']['edit']) ? $options['route']['edit'] : '/admin/contact/simple/tag/edit/id/{tag_id}'
            )
        ));
        $this->TagTypeData = new TagType($this->app);
    }

    /**
     * Default controller for the tag list
     *
     * @param Application $app
     */
    public function controller(Application $app)
    {
        $this->app = $app;
        $this->initialize();
        return $this->exec();
    }

    /**
     * Return the tag list
     *
     * @return string tag list
     */
    public function exec($extra=null)
    {
        $tags = $this->TagTypeData->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['list']),
            array(
                'message' => $this->getMessage(),
                'route' => self::$options['route'],
                'tags' => $tags,
                'extra' => $extra
            ));
    }
}
