<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\Classic;

use Silex\Application;

class SocialSharingButtons
{
    protected $app = null;
    protected static $options = array(
        'template_directory' => '@pattern/classic/function/socialsharingbuttons/'
    );

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Check the $options and set self::$options
     *
     * @param array $options
     */
    protected function checkOptions($options)
    {
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
    }

    /**
     * Create responsive social sharing buttons
     *
     * @param array $buttons
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function social_sharing_buttons($buttons=array(), $options=array(), $prompt=true)
    {
        // check the options
        $this->checkOptions($options);

        // render the buttons
        $sharing = $this->app['twig']->render(
            self::$options['template_directory'].'social.sharing.buttons.twig',
            array('buttons' => $buttons)
        );

        if ($prompt) {
            echo $sharing;
        }
        return $sharing;
    }
}
