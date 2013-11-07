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
use phpManufaktur\Contact\Data\Contact\CategoryType;

class CategoryList extends Dialog {

    protected $CategoryTypeData = null;

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
                'list' => isset($options['template']['list']) ? $options['template']['list'] : 'backend/simple/list.category.twig'
            ),
            'route' => array(
                'edit' => isset($options['route']['edit']) ? $options['route']['edit'] : '/admin/contact/simple/category/edit/id/{category_id}'
            )
        ));
        $this->CategoryTypeData = new CategoryType($this->app);
    }

    /**
     * Default controller for the category list
     *
     * @param Application $app
     * @return string
     */
    public function controller(Application $app)
    {
        $this->app = $app;
        $this->initialize();
        return $this->exec();
    }

    /**
     * Return the Category list
     *
     * @return string category list
     */
    public function exec($extra=null)
    {
        $categories = $this->CategoryTypeData->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['list']),
            array(
                'message' => $this->getMessage(),
                'route' => self::$options['route'],
                'categories' => $categories,
                'extra' => $extra
            ));
    }
}
