<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Data\Setup;

use Silex\Application;
use Symfony\Component\Finder\Finder;

class Uninstall
{
    protected $app = null;

    /**
     * Uninstall the template examples
     * 
     */
    protected function uninstall_templates()
    {
        // initialize the Finder
        $finder = new Finder();
        // we need only the top level
        $finder->depth('== 0');
        // get all directories in the /Examples
        $finder->directories()->in(MANUFAKTUR_PATH.'/TemplateTools/Examples');
        
        foreach ($finder as $directory) {
            $template_name = $directory->getFilename();
            $target_directory = CMS_PATH.'/templates/'.$template_name;
        
            if ($this->app['filesystem']->exists($target_directory)) {
                // the template exists - remove it
                $this->app['filesystem']->remove($target_directory);
            }
            
            $this->app['db']->delete(CMS_TABLE_PREFIX.'addons', 
                array('type' => 'template', 'directory' => $template_name));
        }
    }
    
    /**
     * Uninstall the TemplateTools
     * 
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;
        
        // uninstall the templates
        $this->uninstall_templates();
        $this->app['monolog']->addDebug('Successfull removed the TemplateTools Example Templates.');
        
        return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
            array('%extension%' => 'TemplateTools'));
    }
}
