<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Title;

class TitleList extends Dialog {

    protected $TitleData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app, $options);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Alert::initialize()
     */
    protected function initialize(Application $app, $options=null)
    {
        parent::initialize($app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'list' => isset($options['template']['list']) ? $options['template']['list'] : 'pattern/admin/simple/list.title.twig'
            ),
            'route' => array(
                'create' => isset($options['route']['create']) ? $options['route']['create'] : '/admin/contact/title/edit',
                'edit' => isset($options['route']['edit']) ? $options['route']['edit'] : '/admin/contact/title/edit/id/{title_id}'
            )
        ));
        $this->TitleData = new Title($this->app);
    }

    /**
     * Controller for the title list
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
     * Return the title list
     *
     * @return string category list
     */
    public function exec($extra=null)
    {
        $titles = $this->TitleData->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['list']),
            array(
                'alert' => $this->getAlert(),
                'route' => self::$options['route'],
                'titles' => $titles,
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
