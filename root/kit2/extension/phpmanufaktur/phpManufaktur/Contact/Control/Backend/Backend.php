<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

class Backend extends Alert {

    protected static $usage = null;

    /**
     * Initialize the class with the needed parameters
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return multitype:multitype:string boolean
     */
    public function getToolbar($active) {
        $toolbar_array = array(
            'contact_list' => array(
                'name' => 'contact_list',
                'text' => $this->app['translator']->trans('Contact list'),
                'hint' => $this->app['translator']->trans('List of all available contacts'),
                'link' => FRAMEWORK_URL.'/admin/contact/list',
                'active' => ($active == 'contact_list')
            ),
            'contact_edit' => array(
                'name' => 'contact_edit',
                'text' => ($active === 'contact_edit') ? $this->app['translator']->trans('Edit contact') : $this->app['translator']->trans('Create contact'),
                'hint' => $this->app['translator']->trans('Create or edit a contact record'),
                'link' => FRAMEWORK_URL.'/admin/contact/select',
                'active' => ($active == 'contact_edit')
            ),
            'categories' => array(
                'name' => 'categories',
                'text' => $this->app['translator']->trans('Categories'),
                'hint' => $this->app['translator']->trans('List of available categories'),
                'link' => FRAMEWORK_URL.'/admin/contact/category/list',
                'active' => ($active == 'categories')
            ),
            'tags' => array(
                'name' => 'tags',
                'text' => $this->app['translator']->trans('Tags'),
                'hint' => $this->app['translator']->trans('List of available tags'),
                'link' => FRAMEWORK_URL.'/admin/contact/tag/list',
                'active' => ($active == 'tags')
            ),
            'extra_fields' => array(
                'name' => 'extra_fields',
                'text' => $this->app['translator']->trans('Extra fields'),
                'hint' => $this->app['translator']->trans('List of available extra fields'),
                'link' => FRAMEWORK_URL.'/admin/contact/extra/list',
                'active' => ($active == 'extra_fields')
            ),
            'about' => array(
                'name' => 'about',
                'text' => $this->app['translator']->trans('About'),
                'hint' => $this->app['translator']->trans('Information about the Contact extension'),
                'link' => FRAMEWORK_URL.'/admin/contact/about',
                'active' => ($active == 'about')
                ),
        );
        return $toolbar_array;
    }

}
