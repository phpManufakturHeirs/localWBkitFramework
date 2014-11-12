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
use phpManufaktur\TemplateTools\Data\Locale\Data\Locale;

class Setup
{
    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app = null)
    {
        if (!is_null($app)) {
            $this->app = $app;
        }
    }

    /**
     * Register the given Template in the CMS
     *
     * @param string $template_directory_name
     * @return boolean
     */
    protected function register_template($template_directory_name)
    {
        $info_path = CMS_PATH.'/templates/'.$template_directory_name.'/info.php';
        if (!$this->app['filesystem']->exists($info_path)) {
            $this->app['monolog']->addError('File does not exists: '.$info_path,
                array(__METHOD__, __LINE__));
            return false;
        }

        // initialize the info.php variables
        $template_directory = null;
        $template_name = null;
        $template_description = null;
        $template_function = null;
        $template_version = null;
        $template_platform = null;
        $template_author = null;
        $template_license = null;
        $template_guid = null;

        // require the info.php
        require $info_path;

        // check if this template is already registered
        $SQL = "SELECT `addon_id` FROM `".CMS_TABLE_PREFIX."addons` WHERE `directory`='$template_directory_name' ".
            "AND `type`='template'";
        $addon_id = $this->app['db']->fetchColumn($SQL);

        if ($addon_id > 0) {
            // the template is already registered, remove it.
            $this->app['db']->delete(CMS_TABLE_PREFIX.'addons', array('addon_id' => $addon_id));
        }

        $data = array(
            'directory' => $template_directory,
            'name' => $template_name,
            'description' => addslashes($template_description),
            'type' => 'template',
            'function' => $template_function,
            'version' => $template_version,
            'platform' => $template_platform,
            'author' => addslashes($template_author),
            'license' => addslashes($template_license)
        );

        if (CMS_TYPE != 'WebsiteBaker') {
            $data['guid'] = $template_guid;
        }

        $this->app['db']->insert(CMS_TABLE_PREFIX.'addons', $data);

        return true;
    }

    /**
     * Install all example templates within the CMS and register them
     *
     */
    public function install_templates()
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
                // the template already exists - remove it
                $this->app['filesystem']->remove($target_directory);
            }
            // create the template directory
            $this->app['filesystem']->mkdir($target_directory);

            // get all files and directories from source
            $source = new Finder();
            $source->in($directory->getRealpath());

            foreach ($source as $item) {
                if ($item->isDir()) {
                    // create the directory in the target
                    $this->app['filesystem']->mkdir($target_directory.'/'.$item->getFilename());
                }
                else {
                    // copy file to target
                    $this->app['filesystem']->copy($item->getRealPath(), $target_directory.'/'.$item->getRelativePathname());
                }
            }

            // ok - all files are copied, now update the CMS database
            if ($this->register_template($template_name)) {
                $this->app['monolog']->addDebug('Successfull installed and registered the template '.$template_name);
            }
        }
    }

    /**
     * Process different CMS Bugfixes
     *
     */
    public function cms_bugfix()
    {
        if ($this->app['filesystem']->exists(CMS_PATH.'/modules/news/view.php')) {
            $content = file_get_contents(CMS_PATH.'/modules/news/view.php');
            if (false === (strpos($content, 'global $MOD_NEWS;'))) {
                // proceed bugfix, set the global variable $MOD_NEWS
                $this->app['filesystem']->remove(CMS_PATH.'/modules/news/view.php.bak');
                file_put_contents(CMS_PATH.'/modules/news/view.php.bak', $content);

                $new_content = substr($content, stripos($content, '<?php')+strlen('<?php'));
                $insert = '<?php'."\n\n".'// Bugfix: missing the global variable $MOD_NEWS (proceeded by the TemplateTools)'.
                    "\n".'global $MOD_NEWS;'."\n";
                file_put_contents(CMS_PATH.'/modules/news/view.php', $insert.$new_content);
                $this->app['monolog']->addDebug('Insert Bugfix for the CMS NEWS Addon.');
            }
        }
    }

    /**
     * Create the Locale Table
     *
     */
    public function createLocaleTable()
    {
        $Locale = new Locale($this->app);
        $Locale->createTable();
        $Locale->initLocaleList();
    }

    /**
     * Execute the setup routine for the TemplateTools
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        // CMS Bugfixes
        $this->cms_bugfix();

        // create the Country Table
        $this->createLocaleTable();

        // install the templates
        $this->install_templates();

        return $app['translator']->trans('Successfull installed the extension %extension%.',
                array('%extension%' => 'TemplateTools'));
    }
}
