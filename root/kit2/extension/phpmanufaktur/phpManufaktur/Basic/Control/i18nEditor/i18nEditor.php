<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\i18nEditor;

use Silex\Application;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormFactory;

class i18nEditor extends i18nParser
{
    const unassigned_start_number = 1000000;

    protected static $script_start = null;
    protected static $script_execution_time = null;
    protected static $usage = null;
    protected static $usage_param = null;
    protected static $info = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\localeEditor\localeParser::initialize()
     */
    protected function initialize(Application $app)
    {
        // get the current timestamp
        self::$script_start = microtime(true);

        parent::initialize($app);

        $usage = $this->app['request']->get('usage');
        self::$usage = is_null($usage) ? 'framework' : $usage;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';

        if (self::$usage != 'framework') {
            // set the locale from the CMS locale
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
        }

        // gather some information
        $this->gatherInformation();
    }

    /**
     * Gather information about the locales sources, translations ...
     *
     */
    protected function gatherInformation()
    {
        $translation = array();
        $count = $this->i18nScanFile->selectCount();
        foreach (self::$config['translation']['locale'] as $locale) {
            if ($locale === 'EN') {
                continue;
            }
            $translation[$locale] = array(
                'locale' => $locale,
                'status' => $this->i18nTranslation->selectTranslationStatus($locale)
            );
            if (($count['locale_hits'] > 0) && ($translation[$locale]['status']['total'] == 0)) {
                $this->setAlert('There exists no statistic information about the locale <strong>%locale%</strong>, please execute a <em>search run</em>!',
                    array('%locale%' => $locale), self::ALERT_TYPE_INFO);
            }
        }

        self::$info = array(
            'last_file_modification' => $this->i18nScanFile->getLastModificationDateTime(),
            'count_registered' => $count['count_registered'],
            'count_scanned' => $count['count_scanned'],
            'locale_hits' => $count['locale_hits'],
            'duplicates' => count($this->i18nTranslation->selectDuplicates()),
            'conflicts' => $this->i18nTranslation->countConflicts(),
            'unassigned' => $this->i18nTranslationUnassigned->count(),
            'translation' => $translation
        );
    }

    /**
     * Create the toolbar for the dialogs
     *
     * @param string $active
     * @return array
     */
    protected function getToolbar($active)
    {
        $toolbar = array();
        $tabs = array_merge(array('overview'), self::$config['translation']['locale'], array('sources', 'problems', 'about'));

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the i18nEditor'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/about',
                        'active' => ($active === 'about')
                    );
                    break;
                case 'overview':
                    $toolbar[$tab] = array(
                        'name' => 'overview',
                        'text' => $this->app['translator']->trans('Overview'),
                        'hint' => $this->app['translator']->trans('A brief summary of the translation status'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/overview',
                        'active' => ($active === 'overview')
                    );
                    break;
                case 'sources':
                    $toolbar[$tab] = array(
                        'name' => 'sources',
                        'text' => $this->app['translator']->trans('Sources'),
                        'hint' => $this->app['translator']->trans('List of translation sources'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources',
                        'active' => ($active === 'sources')
                    );
                    break;
                case 'problems':
                    if (self::$config['developer']['enabled']) {
                        // add this navigation only in active developer mode!
                        $toolbar[$tab] = array(
                            'name' => 'problems',
                            'text' => $this->app['translator']->trans('Problems'),
                            'hint' => $this->app['translator']->trans('Problems with the translation data'),
                            'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems',
                            'active' => ($active === 'problems')
                        );
                    }
                    break;
                default:
                    if (in_array($tab, self::$config['translation']['locale'])) {
                        $toolbar[$tab] = array(
                            'name' => strtolower($tab),
                            'text' => $tab, // no translation!
                            'hint' => $this->app['translator']->trans('Edit the locale %locale%', array(
                                '%locale%' => $this->app['translator']->trans($tab))),
                            'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($tab).'/files',
                            'active' => (strtolower($active) === strtolower($tab))
                        );
                    }
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Get the toolbar for the locale dialogs
     *
     * @param string $active
     * @param string $locale
     * @return multitype:multitype:string boolean NULL
     */
    protected function getToolbarLocale($active, $locale)
    {
        $toolbar = array();
        $tabs = array('files', 'custom', 'pending', 'edit');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'pending':
                    $toolbar[$tab] = array(
                        'name' => 'pending',
                        'text' => $this->app['translator']->trans('Waiting'),
                        'hint' => $this->app['translator']->trans('Locales waiting for a translation'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($locale).'/pending'.self::$usage_param,
                        'active' => ($active === 'pending')
                    );
                    break;
                case 'edit':
                    $toolbar[$tab] = array(
                        'name' => 'edit',
                        'text' => $this->app['translator']->trans('Edit'),
                        'hint' => $this->app['translator']->trans('Edit translation'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/translation/edit/id'.self::$usage_param,
                        'active' => ($active === 'edit')
                    );
                    break;
                case 'files':
                    $toolbar[$tab] = array(
                        'name' => 'files',
                        'text' => $this->app['translator']->trans('Files'),
                        'hint' => $this->app['translator']->trans('View the translations grouped by locale files'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($locale).'/files'.self::$usage_param,
                        'active' => ($active === 'files')
                    );
                    break;
                case 'custom':
                    $toolbar[$tab] = array(
                        'name' => 'custom',
                        'text' => $this->app['translator']->trans('Custom'),
                        'hint' => $this->app['translator']->trans('View the custom translations for this installation'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/locale/'.strtolower($locale).'/custom'.self::$usage_param,
                        'active' => ($active === 'custom')
                    );
                    break;
            }
        }
        return $toolbar;
    }

    protected function getToolbarAZ($active)
    {
        $toolbar = array();
        $tabs = array('a-c','d-f','g-i','j-l','m-p','q-s','t','u-z','special');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'a-c':
                    $toolbar[$tab] = array(
                        'name' => 'a-c',
                        'text' => 'ABC',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'a-c')
                    );
                    break;
                case 'd-f':
                    $toolbar[$tab] = array(
                        'name' => 'd-f',
                        'text' => 'DEF',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'd-f')
                    );
                    break;
                case 'g-i':
                    $toolbar[$tab] = array(
                        'name' => 'g-i',
                        'text' => 'GHI',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'g-i')
                    );
                    break;
                case 'j-l':
                    $toolbar[$tab] = array(
                        'name' => 'j-l',
                        'text' => 'JKL',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'j-l')
                    );
                    break;
                case 'm-p':
                    $toolbar[$tab] = array(
                        'name' => 'm-p',
                        'text' => 'MNOP',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'm-p')
                    );
                    break;
               case 'q-s':
                    $toolbar[$tab] = array(
                        'name' => 'q-s',
                        'text' => 'QRS',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'q-s')
                    );
                    break;
               case 't':
                    $toolbar[$tab] = array(
                        'name' => 't',
                        'text' => 'T',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 't')
                    );
                    break;
               case 'u-z':
                    $toolbar[$tab] = array(
                        'name' => 'u-z',
                        'text' => 'UVWXYZ',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'u-z')
                    );
                    break;
               case 'special':
                    $toolbar[$tab] = array(
                        'name' => 'special',
                        'text' => '*',
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/sources/'.$tab.self::$usage_param,
                        'active' => ($active === 'special')
                    );
                    break;

            }
        }
        return $toolbar;
    }

    /**
     * Get the toolbar for the problems dialog
     *
     * @param string $active
     * @return array
     */
    protected function getToolbarProblems($active)
    {
        $toolbar = array();
        $tabs = array('conflicts', 'unassigned', 'duplicates');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'conflicts':
                    $toolbar[$tab] = array(
                        'name' => 'conflicts',
                        'text' => $this->app['translator']->trans('Conflicts'),
                        'hint' => $this->app['translator']->trans('Translations which causes a conflict'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/conflicts'.self::$usage_param,
                        'active' => ($active === 'conflicts')
                    );
                    break;
                case 'unassigned':
                    $toolbar[$tab] = array(
                        'name' => 'unassigned',
                        'text' => $this->app['translator']->trans('Unassigned'),
                        'hint' => $this->app['translator']->trans('Translations which are not assigned to any files'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/unassigned'.self::$usage_param,
                        'active' => ($active === 'unassigned')
                    );
                    break;
                case 'duplicates':
                    $toolbar[$tab] = array(
                        'name' => 'duplicates',
                        'text' => $this->app['translator']->trans('Duplicates'),
                        'hint' => $this->app['translator']->trans('Duplicate translations'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/problems/duplicates'.self::$usage_param,
                        'active' => ($active === 'duplicates')
                    );
                    break;
            }
        }
        return $toolbar;
    }


    /**
     * Show the about dialog for flexContent
     *
     * @return string rendered dialog
     */
    public function ControllerAbout(Application $app)
    {
        $this->initialize($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Basic/extension.i18n.editor.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/about.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('about'),
                'extension' => $extension,
                'config' => self::$config
            ));
    }

    /**
     * Show the overview dialog for the i18nEditor
     *
     * @return string \Twig_Template
     */
    protected function showOverview()
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/overview.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('overview'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info
            ));
    }

    /**
     * Scan the complete kitFramework and update all i18n data tables
     *
     * @param Application $app
     * @return string
     */
    public function ControllerScan(Application $app)
    {
        $this->initialize($app);

        // process PHP files
        $this->findPHPfiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('PHP'))) {
            foreach ($files as $file) {
                $this->parsePHPfile($file['file_path']);
            }
        }

        // process Twig files
        $this->findTwigFiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('TWIG'))) {
            foreach ($files as $file) {
                $this->parseTwigFile($file['file_path']);
            }
        }

        // remove widowed locale sources from the database
        $widowed = $this->i18nSource->selectWidowed();
        if (is_array($widowed)) {
            self::$translation_deleted = array();
            foreach ($widowed as $widow) {
                if (!in_array($widow['locale_source'], self::$config['translation']['system'])) {
                    $this->i18nSource->delete($widow['locale_id']);
                    self::$translation_deleted[] = $widow['locale_id'];
                }
            }
        }

        // update the translation table
        $this->updateTranslationTable();

        // find the locale files
        $this->findLocaleFiles();

        // check if conflicts are solved
        $this->checkConflicts();

        self::$script_execution_time = sprintf('%01.2f', (microtime(true) - self::$script_start));
        $this->setAlert('Executed search run in %seconds% seconds.',
            array('%seconds%' => self::$script_execution_time), self::ALERT_TYPE_SUCCESS);
        $this->gatherInformation();
        return $this->showOverview();
    }

    /**
     * The general controller for the localeEditor
     *
     * @param Application $app
     * @return string
     */
    public function ControllerOverview(Application $app)
    {
        $this->initialize($app);
        return $this->showOverview();
    }

    public function ControllerLocalePending(Application $app, $locale)
    {
        $this->initialize($app);

        if (false === ($pendings = $this->i18nTranslation->selectPendings($locale))) {
            $this->setAlert('There exists no pending translations for the locale %locale%.',
                array('%locale%' => $this->app['translator']->trans(strtoupper($locale))),
                self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.pending.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar(strtolower($locale)),
                'toolbar_locale' => $this->getToolbarLocale('pending', $locale),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'locale_locale' => $locale,
                'pendings' => $pendings
            ));
    }

    /**
     * Return the Sources Overview dialog
     *
     */
    protected function showSources($tab)
    {
        if (false === ($list = $this->i18nSource->selectAll(
            self::$config['editor']['sources']['list']['order_by'],
            self::$config['editor']['sources']['list']['order_direction'],
            $tab))) {
            $this->setAlert('No sources available, please <a href="%url%">start a search run</a>!',
                array('%url%' => FRAMEWORK_URL.'/admin/i18n/editor/scan'.self::$usage_param), self::ALERT_TYPE_WARNING);
        }
        $sources = array();

        if (is_array($list)) {
            foreach ($list as $source) {
                $sources[$source['locale_id']] = $source;
                $sources[$source['locale_id']]['references'] =
                    $this->i18nReference->countReferencesForLocaleID($source['locale_id']);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/sources.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('sources'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'sources' => $sources,
                'toolbar_az' => $this->getToolbarAZ($tab)
            ));
    }

    /**
     * Controller to view the locale sources
     *
     * @param Application $app
     */
    public function ControllerSources(Application $app, $tab)
    {
        $this->initialize($app);
        return $this->showSources($tab);
    }

    /**
     * Get the form for the Locale Reference
     *
     * @param array $data
     * @return FormFactoryBuilder
     */
    protected function getReferenceForm($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('locale_id', 'hidden', array(
                'data' => isset($data['locale_id']) ? $data['locale_id'] : -1
            ))
            ->add('locale_locale', 'hidden', array(
                'data' => isset($data['locale_locale']) ? $data['locale_locale'] : ''
                ))
            ->add('locale_source', 'hidden', array(
                'data' => isset($data['locale_source']) ? $this->app['utils']->sanitizeText($data['locale_source']) : ''
            ))
            ->add('locale_remark', 'textarea', array(
                'data' => isset($data['locale_remark']) ? $data['locale_remark'] : '',
                'required' => false
            ))
            ->getForm();
    }

    /**
     * Controller to inspect the locale source details and add an optional remark
     *
     * @param Application $app
     * @param integer $locale_id
     */
    public function ControllerSourcesDetail(Application $app, $locale_id)
    {
        $this->initialize($app);

        if (false === ($source = $this->i18nSource->select($locale_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $locale_id), self::ALERT_TYPE_DANGER);
        }

        $references = array();
        $translations = array();
        $translation_conflict = false;
        if (is_array($source)) {
            if (false === ($files = $this->i18nReference->selectReferencesForLocaleID($locale_id))) {
                $this->setAlert('There exists no references for the locale source with the id %locale_id%.',
                    array('%locale_id%' => $locale_id), self::ALERT_TYPE_WARNING);
            }
            else {
                foreach ($files as $file) {
                    $references[$file['file_id']] = $file;
                    $references[$file['file_id']]['file_path'] = realpath($file['file_path']);
                    $references[$file['file_id']]['basename'] = basename(realpath($file['file_path']));
                }
            }

            foreach (self::$config['translation']['locale'] as $locale) {
                if (false === ($files = $this->i18nTranslationFile->selectByLocaleID($locale_id, $locale))) {
                    continue;
                }
                foreach ($files as $file) {
                    $item = array();
                    if (false !== ($translation = $this->i18nTranslation->select($file['translation_id']))) {
                        $item = $file;
                        $item['translation_text'] = $translation['translation_text'];
                        $item['translation_remark'] = $translation['translation_remark'];
                        $item['translation_status'] = $translation['translation_status'];
                        if ($translation['translation_status'] === 'CONFLICT') {
                            $translation_conflict = true;
                        }
                        $translations[] = $item;
                    }
                }
            }
            if (empty($translations)) {
                $this->setAlert('There exists no translations for the locale source with the id %locale_id%',
                        array('%locale_id%' => $locale_id), self::ALERT_TYPE_WARNING);
            }
            if ($translation_conflict) {
                $this->setAlert('One or more translation for this source is conflicting!',
                    array(), self::ALERT_TYPE_WARNING);
            }
        }

        $form = $this->getReferenceForm($source);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/sources.detail.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('sources'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'source' => $source,
                'references' => $references,
                'form' => $form->createView(),
                'translations' => $translations
            ));
    }

    /**
     * Controller to check the Detail form and update the data record
     *
     * @param Application $app
     */
    public function ControllerSourcesDetailCheck(Application $app)
    {
        $this->initialize($app);

        $form = $this->getReferenceForm();

        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            $remark = !is_null($data['locale_remark']) ? $data['locale_remark'] : '';
            $this->i18nSource->update($data['locale_id'], array('locale_remark' => $remark));
            $this->setAlert('The record with the ID %id% was successfull updated.',
                array('%id%' => $data['locale_id']), self::ALERT_TYPE_SUCCESS);
            return $this->showSources();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->ControllerSourcesDetail($app, -1);
        }
    }

    /**
     * Controller to show the translation conflicts
     *
     * @param Application $app
     */
    public function ControllerProblemsConflicts(Application $app)
    {
        $this->initialize($app);

        $conflict_translations = $this->i18nTranslation->selectConflicts();
        $conflicts = array();
        if (is_array($conflict_translations)) {
            foreach ($conflict_translations as $conflict) {
                $files = $this->i18nTranslationFile->selectByLocaleID($conflict['locale_id'], $conflict['locale_locale']);
                $conflict['conflict_files'] = $files;
                $conflicts[] = $conflict;
            }
        }

        if (empty($conflicts)) {
            $this->setAlert('There exists no conflicts.', array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.conflicts.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('conflicts'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'conflicts' => $conflicts
            ));
    }

    /**
     * Controller to show the unassigned translations
     *
     * @param Application $app
     */
    public function ControllerProblemsUnassigned(Application $app)
    {
        $this->initialize($app);

        if (false === ($unassigneds = $this->i18nTranslationUnassigned->selectAll())) {
            $this->setAlert('There exists no unassigned translations.', array(), self::ALERT_TYPE_INFO);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.unassigned.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('unassigned'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'unassigneds' => $unassigneds
            ));
    }

    /**
     * Controller to show duplicate translations
     *
     * @param Application $app
     */
    public function ControllerProblemsDuplicates(Application $app)
    {
        $this->initialize($app);

        if (false === ($duplicate_translations = $this->i18nTranslation->selectDuplicates())) {
            $this->setAlert('There exists no duplicate translations.', array(), self::ALERT_TYPE_INFO);
        }
        $duplicates = array();
        if (is_array($duplicate_translations)) {
            foreach ($duplicate_translations as $duplicate) {
                $files = $this->i18nTranslationFile->selectByLocaleID($duplicate['locale_id'], $duplicate['locale_locale']);
                $duplicate['duplicate_files'] = $files;
                $duplicates[] = $duplicate;
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/problems.duplicates.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('problems'),
                'toolbar_problems' => $this->getToolbarProblems('duplicates'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'duplicates' => $duplicates
            ));
    }

    /**
     * Sorting function for getLocaleFilesForChoice()
     *
     * @param string $a
     * @param string $b
     * @return integer
     */
    protected static function sortLocaleDirectories($a, $b)
    {
        $a = strtolower(pathinfo($a, PATHINFO_DIRNAME));
        $b = strtolower(pathinfo($b, PATHINFO_DIRNAME));

        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

    /**
     * Get the possible locale files for selecting or saving - this function
     * check if the developer access is enabled or not
     *
     * @param string $locale
     * @param boolean $must_exists the locale file must exists
     */
    protected function getLocaleFilesForChoice($locale, $must_exists=false, $ignore_admin=false)
    {
        $locale = strtolower($locale);
        $extensions = new Finder();
        $extensions->directories()->in(array(MANUFAKTUR_PATH));
        $extensions->exclude(array('Updater'));
        $extensions->depth('== 0');
        $extensions->sortByName();

        // use stricly DIRECTORY_SEPARATOR to avoid problems with the realpath
        $base = DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR;

        $search = array();
        if ($ignore_admin || self::$config['developer']['enabled']) {
            $sub = 'Metric'.DIRECTORY_SEPARATOR.$locale.'.php';
            if (!$must_exists || ($must_exists && $this->app['filesystem']->exists(realpath(MANUFAKTUR_PATH).DIRECTORY_SEPARATOR.'Basic'.$base.$sub))) {
                $search = array(realpath(MANUFAKTUR_PATH).DIRECTORY_SEPARATOR.'Basic'.$base.$sub => DIRECTORY_SEPARATOR.'Basic'.$base.$sub);
            }
        }

        foreach ($extensions as $extension) {
            $path = $extension->getRealpath();
            $extension_name = substr($path, strrpos($path, DIRECTORY_SEPARATOR)+1);
            if (//in_array($extension_name, self::$config['finder']['php']['exclude']['directory']) ||
                in_array($extension_name, self::$config['editor']['translation']['exclude']['extension'])) {
                continue;
            }
            if ($ignore_admin || self::$config['developer']['enabled']) {
                $subdirectory = DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR.$locale.'.php';
                if (!$must_exists || ($must_exists && $this->app['filesystem']->exists($path.$subdirectory))) {
                    $search[$path.$subdirectory] = DIRECTORY_SEPARATOR.$extension_name.$subdirectory;
                }
            }
            $subdirectory = DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR.$locale.'.php';
            if (!$must_exists || ($must_exists && $this->app['filesystem']->exists($path.$subdirectory))) {
                $search[$path.$subdirectory] = DIRECTORY_SEPARATOR.$extension_name.$subdirectory;
            }
        }

        // sort the result array by the locale directories
        uasort($search, array('self', 'sortLocaleDirectories'));

        return $search;
    }

    /**
     * Get the form for translating locales
     *
     * @param array $data
     */
    protected function getTranslationForm($translation=array(), $translation_file=array())
    {
        $files = array();
        $locale_files = $this->getLocaleFilesForChoice(isset($translation['locale_locale']) ? $translation['locale_locale'] : 'EN');
        $locale_file_id = null;
        $i = 0;
        $is_custom_file = false;

        foreach ($locale_files as $key => $value) {
            // use integer values instead of path's as key for the choices
            $files[$i] = $value;
            if (isset($translation_file['file_path']) && ($translation_file['file_path'] == $key)) {
                $locale_file_id = $i;
                $is_custom_file = (false !== strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR));
            }
            $i++;
        }
        $target_files = array();
        foreach ($files as $key => $value) {
            if ($key === $locale_file_id) {
                continue;
            }
            if ($is_custom_file && (false !== strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR))) {
                $target_files[$key] = $value;
            }
            elseif (!$is_custom_file && self::$config['developer']['enabled'] &&
                (false === strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR))) {
                $target_files[$key] = $value;
            }
        }
        $custom_files = array();
        if (!$is_custom_file && self::$config['developer']['enabled']) {
            foreach ($files as $key => $value) {
                if (false !== strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                    $custom_files[$key] = $value;
                }
            }
        }

        $form = $this->app['form.factory']->createBuilder('form')
            ->add('translation_id', 'hidden', array(
                'data' => isset($translation['translation_id']) ? $translation['translation_id'] : -1
            ))
            ->add('locale_id', 'hidden', array(
                'data' => isset($translation['locale_id']) ? $translation['locale_id'] : -1
            ))
            ->add('locale_locale', 'hidden', array(
                'data' => isset($translation['locale_locale']) ? $translation['locale_locale'] : 'EN'
                ))
            ->add('translation_status', 'hidden', array(
                'data' => isset($translation['translation_status']) ? $translation['translation_status'] : 'PENDING'
            ))
            ->add('extension', 'hidden', array(
                'data' => isset($translation_file['extension']) ? $translation_file['extension'] : 'UNKNOWN'
            ))
            ->add('locale_source', 'hidden', array(
                'data' => isset($translation['locale_source']) ? $this->app['utils']->sanitizeText($translation['locale_source']) : ''
            ))
            ->add('translation_text', 'textarea', array(
                'data' => isset($translation['translation_text']) ? $translation['translation_text'] : '',
                'read_only' => (isset($translation['translation_status']) && ($translation['translation_status'] === 'CONFLICT'))
            ))
            ->add('translation_remark', 'textarea', array(
                'data' => isset($translation['translation_remark']) ? $translation['translation_remark'] : '',
                'required' => false
            ))
            ;

        $choice_path = 'file_path';
        if (!is_null($locale_file_id)) {
            $choice_path = 'file_path_choice';
            // we need a hidden field with the file_path to avoid problems with CSRF and the form validation
            $form->add('file_path', 'hidden', array(
                'data' => $locale_file_id
            ));
        }

        $form->add($choice_path, 'choice', array(
            'choices' => $files,
            'empty_value' => '- please select -',
            'data' => $locale_file_id,
            'disabled' => !is_null($locale_file_id)
        ));

        if (!is_null($locale_file_id) &&
            ((isset($translation_file['locale_type']) && ($translation_file['locale_type'] === 'CUSTOM')) ||
            self::$config['developer']['enabled'])) {
            if (!empty($target_files)) {
                // add choice to move the translation to another file
                $form->add('translation_move_to', 'choice', array(
                    'choices' => $target_files,
                    'empty_value' => '- please select -',
                    'required' => false
                ));
            }
            else {
                $form->add('translation_move_to', 'hidden');
            }
            if (!$is_custom_file && !empty($custom_files)) {
                // add choice to create custom files (needed by developers!)
                $form->add('translation_custom_file', 'choice', array(
                    'choices' => $custom_files,
                    'empty_value' => '- please select -',
                    'required' => false
                ));
            }
            else {
                $form->add('translation_custom_file', 'hidden');
            }
            // add checkbox to delete this translation
            $form->add('translation_delete_checkbox', 'checkbox', array(
                'required' => false
            ));
        }
        else {
            // add hidden fields to avoid problems with CSRF and the form validation
            $form->add('translation_delete_checkbox', 'hidden');
            $form->add('translation_move_to', 'hidden');
            $form->add('translation_custom_file', 'hidden');
        }

        return $form->getForm();
    }

    /**
     * Controller for the Translation Edit Dialog
     *
     * @param Application $app
     * @param integer $translation_id
     */
    public function ControllerTranslationEdit(Application $app, $translation_id)
    {
        $this->initialize($app);

        if ($translation_id > self::unassigned_start_number) {
            // this is a unassigned translation!
            $unassigned_id = $translation_id - self::unassigned_start_number;
            if (false === ($unassigned = $this->i18nTranslationUnassigned->select($unassigned_id))) {
                $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $translation_id), self::ALERT_TYPE_DANGER);
                return $this->promptAlertFramework();
            }
            // map the unassigned record as translation record
            $translation = $unassigned;
            $translation['translation_id'] = $translation_id;
            $translation['locale_md5'] = md5($unassigned['locale_source']);
            $translation['translation_md5'] = md5($unassigned['translation_text']);
            $translation['translation_remark'] = 'UNASSIGNED';
            $translation['translation_status'] = 'UNASSIGNED';
        }
        elseif (false === ($translation = $this->i18nTranslation->select($translation_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $translation_id), self::ALERT_TYPE_DANGER);
            return $this->promptAlertFramework();
        }

        if ($translation['translation_status'] === 'CONFLICT') {
            if (!self::$config['developer']['enabled']) {
                $this->setAlert('The status of this translation is set to <strong>CONFLICT</strong>. This problem must be solved by a developer.',
                    array(), self::ALERT_TYPE_DANGER);
            }
            else {
                $this->setAlert('You must solve the <strong>CONFLICT</strong> before you can change this translation record.',
                    array(), self::ALERT_TYPE_DANGER);
            }
        }

        if ($translation_id > self::unassigned_start_number) {
            // map the unassigned record as translation file
            $translation_files = array(
                array(
                    'file_id' => -1,
                    'translation_id' => $translation_id,
                    'locale_id' => -1,
                    'locale_locale' => $translation['locale_locale'],
                    'locale_type' => $translation['locale_type'],
                    'extension' => $translation['extension'],
                    'file_path' => $translation['file_path'],
                    'file_md5' => $translation['file_md5'],
                    'timestamp' => $translation['timestamp']
                )
            );
        }
        else {
            $translation_files = $this->i18nTranslationFile->selectByTranslationID($translation_id, $translation['locale_locale']);
        }

        $source = false;
        if (($translation_id < self::unassigned_start_number) && (false === ($source = $this->i18nSource->select($translation['locale_id'])))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $translation['locale_id']), self::ALERT_TYPE_DANGER);
        }

        $references = array();
        if (is_array($source)) {
            if (false === ($files = $this->i18nReference->selectReferencesForLocaleID($translation['locale_id']))) {
                $this->setAlert('There exists no references for the locale source with the id %locale_id%.',
                    array('%locale_id%' => $translation['locale_id']), self::ALERT_TYPE_WARNING);
            }
            else {
                foreach ($files as $file) {
                    $references[$file['file_id']] = $file;
                    $references[$file['file_id']]['file_path'] = realpath($file['file_path']);
                    $references[$file['file_id']]['basename'] = basename(realpath($file['file_path']));
                }
            }
        }


        $form = $this->getTranslationForm($translation, $translation_files[0]);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/translation.edit.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar($translation['locale_locale']),
                'toolbar_locales' => $this->getToolbarLocale('edit', $translation['locale_locale']),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'info' => self::$info,
                'form' => $form->createView(),
                'references' => $references
            ));
    }

    /**
     * Sort the locale translations before writing to file
     *
     * @param string $a
     * @param string $b
     * @return number
     */
    protected static function sortLocaleTranslations($a, $b)
    {
        $a = strtolower(strip_tags($a));
        $b = strtolower(strip_tags($b));

        return strcasecmp($a, $b);
    }

    /**
     * Controller to check Translations and write/backup locale files
     *
     * @param Application $app
     */
    public function ControllerTranslationEditCheck(Application $app)
    {
        $this->initialize($app);

        // get the form
        $form = $this->getTranslationForm();
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if (!$data['translation_delete_checkbox'] &&
                !is_numeric($data['translation_move_to']) &&
                is_numeric($data['translation_custom_file']) &&
                ($data['translation_custom_file'] >= 0)) {
                // enable a developer to create a custom file - we simply map the ID's ...
                $data['file_path'] = intval($data['translation_custom_file']);
            }

            $files = $this->getLocaleFilesForChoice($data['locale_locale']);
            $i = 0;
            foreach ($files as $key => $value) {
                if ($i == $data['file_path']) {
                    // locale path
                    $locale_path = $key;
                    // extension name
                    $extension = ltrim($value, DIRECTORY_SEPARATOR);
                    $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));
                    $target_is_custom = (false !== (strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)));
                    break;
                }
                $i++;
            }

            $write_locale_file = false;

            if ($data['translation_delete_checkbox']) {
                // delete this translation
                if ($data['translation_id'] > self::unassigned_start_number) {
                    // this is a unassigned translation!
                    $unassigned_id = $data['translation_id'] - self::unassigned_start_number;
                    $this->i18nTranslationUnassigned->delete($unassigned_id);
                }
                else {
                    // regular translation
                    $this->i18nTranslation->delete($data['translation_id']);
                    $this->i18nTranslationFile->deleteByTranslationID($data['translation_id']);
                }
                $this->setAlert('Deleted the translation with the ID %id%.',
                    array('%id%' => $data['translation_id']), self::ALERT_TYPE_SUCCESS);
                // get the content of the current locale file
                $file_array = $this->getLocaleFileArray($locale_path);
                // remove this translation from the array
                unset($file_array[$this->app['utils']->unsanitizeText($data['locale_source'])]);
                // sort the array
                uksort($file_array, array('self', 'sortLocaleTranslations'));
                $locale_array = array();
                foreach ($file_array as $key => $value) {
                    $locale_array["'".str_replace(array("\'","'"), array("'", "\'"), $key)."'"] = "'".str_replace(array("\'", "'"), array("'", "\'"), $value)."'";
                }
                $this->putLocaleFile($locale_path, $locale_array, $extension);
                // return to the translation file page
                return $this->ControllerLocaleFiles($app, $data['locale_locale'], -2);
            }

            if (is_numeric($data['translation_move_to']) && ($data['translation_move_to'] >= 0)) {
                // move this translation to another translation file
                if ($data['translation_id'] < self::unassigned_start_number) {
                    // regular translation file
                    $this->i18nTranslationFile->deleteByTranslationID($data['translation_id']);
                }

                // get the content of the current locale file
                $file_array = $this->getLocaleFileArray($locale_path);
                // remove this translation from the array
                unset($file_array[$this->app['utils']->unsanitizeText($data['locale_source'])]);
                // sort the array
                uksort($file_array, array('self', 'sortLocaleTranslations'));
                $locale_array = array();
                foreach ($file_array as $key => $value) {
                    $locale_array["'".str_replace(array("\'","'"), array("'", "\'"), $key)."'"] = "'".str_replace(array("\'", "'"), array("'", "\'"), $value)."'";
                }
                $this->putLocaleFile($locale_path, $locale_array, $extension, true);

                if (strpos($locale_path, DIRECTORY_SEPARATOR.'Metric'.DIRECTORY_SEPARATOR)) {
                    $locale_type = 'METRIC';
                }
                elseif (strpos($locale_path, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                    $locale_type = 'CUSTOM';
                }
                else {
                    $locale_type = 'DEFAULT';
                }

                $i = 0;

                foreach ($files as $key => $value) {
                    if ($i == $data['translation_move_to']) {
                        // locale path
                        $new_locale_path = $key;
                        // extension name
                        $extension = ltrim($value, DIRECTORY_SEPARATOR);
                        $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));
                        break;
                    }
                    $i++;
                }

                if ($data['translation_id'] > self::unassigned_start_number) {
                    $update = array(
                        'locale_locale' => $data['locale_locale'],
                        'locale_type' => $locale_type,
                        'extension' => $extension,
                        'file_path' => $new_locale_path,
                        'file_md5' => md5($new_locale_path)
                    );
                    $this->i18nTranslationUnassigned->update($data['translation_id'] - self::unassigned_start_number, $update);
                }
                else {
                    // this is a regular translation file
                    $file_data = array(
                        'translation_id' => $data['translation_id'],
                        'locale_id' => $data['locale_id'],
                        'locale_locale' => $data['locale_locale'],
                        'locale_type' => $locale_type,
                        'extension' => $extension,
                        'file_path' => $new_locale_path,
                        'file_md5' => md5($new_locale_path)
                    );
                    $this->i18nTranslationFile->insert($file_data);
                }

                // get the content of the current locale file
                $file_array = $this->getLocaleFileArray($new_locale_path);

                // add or update the current translation
                $file_array[$data['locale_source']] = $data['translation_text'];

                // sort the array
                uksort($file_array, array('self', 'sortLocaleTranslations'));

                $locale_array = array();
                foreach ($file_array as $key => $value) {
                    $locale_array["'".str_replace(array("\'","'"), array("'", "\'"), $key)."'"] = "'".str_replace(array("\'", "'"), array("'", "\'"), $value)."'";
                }
                $this->putLocaleFile($new_locale_path, $locale_array, $extension, true);

                $this->setAlert('Successful moved the translation to the extension %extension%.',
                    array('%extension%' => $extension), self::ALERT_TYPE_SUCCESS);

                return $this->ControllerTranslationEdit($app, $data['translation_id']);
            }

            if ($data['translation_id'] > self::unassigned_start_number) { echo "yepp!";
                // this is a unassigned translation !!!
                $unassigned_id = $data['translation_id'] - self::unassigned_start_number;
                $current = $this->i18nTranslationUnassigned->select($unassigned_id);
                if ($current['translation_md5'] !== md5($data['translation_text'])) {
                    // the translation has changed
                    if ($target_is_custom && ($locale_path !== $current['file_path'])) { echo "NEQW!!";
                        // create a new custom file
                        if (strpos($locale_path, DIRECTORY_SEPARATOR.'Metric'.DIRECTORY_SEPARATOR)) {
                            $locale_type = 'METRIC';
                        }
                        elseif (strpos($locale_path, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                            $locale_type = 'CUSTOM';
                        }
                        else {
                            $locale_type = 'DEFAULT';
                        }
                        $insert = array(
                            'file_path' => $locale_path,
                            'file_md5' => md5($locale_path),
                            'extension' => $extension,
                            'locale_locale' => $data['locale_locale'],
                            'locale_source' => $current['locale_source'],
                            'locale_md5' => $current['locale_md5'],
                            'locale_type' => $locale_type,
                            'translation_text' => $data['translation_text'],
                            'translation_md5' => md5($data['translation_text'])
                        );
                        $unassigned_id = $this->i18nTranslationUnassigned->insert($insert);
                        $data['translation_id'] = self::unassigned_start_number + $unassigned_id;
                    }
                    else {
                        $update = array(
                            'translation_text' => $data['translation_text'],
                            'translation_md5' => md5($data['translation_text'])
                        );
                        $this->i18nTranslationUnassigned->update($unassigned_id, $update);
                    }
                    $write_locale_file = true;
                }
            }
            else {
                // this is a regular translation
                if (false === ($files = $this->i18nTranslationFile->selectByTranslationID($data['translation_id'], $data['locale_locale']))) {
                    // create a new translation file record
                    if (strpos($locale_path, DIRECTORY_SEPARATOR.'Metric'.DIRECTORY_SEPARATOR)) {
                        $locale_type = 'METRIC';
                    }
                    elseif (strpos($locale_path, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                        $locale_type = 'CUSTOM';
                    }
                    else {
                        $locale_type = 'DEFAULT';
                    }
                    $file_data = array(
                        'translation_id' => $data['translation_id'],
                        'locale_id' => $data['locale_id'],
                        'locale_locale' => $data['locale_locale'],
                        'locale_type' => $locale_type,
                        'extension' => $extension,
                        'file_path' => $locale_path,
                        'file_md5' => md5($locale_path)
                    );
                    $this->i18nTranslationFile->insert($file_data);

                    $translation = array(
                        'translation_text' => $data['translation_text'],
                        'translation_md5' => md5($data['translation_text']),
                        'translation_remark' => !empty($data['translation_remark']) ? $data['translation_remark'] : '',
                        'translation_status' => 'TRANSLATED'
                    );
                    $this->i18nTranslation->update($data['translation_id'], $translation);

                    $write_locale_file = true;
                }
                elseif (isset($files[0]['file_path']) && ($files[0]['file_path'] === $locale_path)) {
                    // update the translation record ?
                    $current = $this->i18nTranslation->select($data['translation_id']);
                    if ($current['translation_md5'] !== md5($data['translation_text'])) {
                        // the translation has changed, save the record
                        $translation = array(
                            'translation_text' => $data['translation_text'],
                            'translation_md5' => md5($data['translation_text']),
                            'translation_remark' => !empty($data['translation_remark']) ? $data['translation_remark'] : '',
                            'translation_status' => 'TRANSLATED'
                        );
                        $this->i18nTranslation->update($data['translation_id'], $translation);
                        $write_locale_file = true;
                    }
                }
                else {
                    // new translation file?
                    $check_files = $this->i18nTranslationFile->selectByLocaleID($data['locale_id'], $data['locale_locale']);
                    if (is_array($check_files)) {
                        $checked = false;
                        foreach ($check_files as $check_file) {
                            if ($check_file['file_md5'] === md5($locale_path)) {
                                $checked = true;
                                $translation = $this->i18nTranslation->select($check_file['translation_id']);
                                if ($translation['translation_md5'] !== md5($data['translation_text'])) {
                                    // change existing translation
                                    $update = array(
                                        'translation_text' => $data['translation_text'],
                                        'translation_md5' => md5($data['translation_text']),
                                        'translation_remark' => !empty($data['translation_remark']) ? $data['translation_remark'] : '',
                                        'translation_status' => 'TRANSLATED'
                                    );
                                    $this->i18nTranslation->update($check_file['translation_id'], $update);
                                    $write_locale_file = true;
                                }
                                $data['translation_id'] = $check_file['translation_id'];
                            }
                        }
                        if (!$checked) {
                            // create a new translation file
                            $current = $this->i18nTranslation->select($data['translation_id']);
                            $new = array(
                                'locale_id' => $current['locale_id'],
                                'locale_locale' => $current['locale_locale'],
                                'locale_source' => $current['locale_source'],
                                'locale_source_plain' => $current['locale_source'],
                                'locale_md5' => $current['locale_md5'],
                                'translation_text' => $data['translation_text'],
                                'translation_md5' => md5($data['translation_text']),
                                'translation_remark' => !empty($data['translation_remark']) ? $data['translation_remark'] : '',
                                'translation_status' => 'TRANSLATED'
                            );
                            $translation_id = $this->i18nTranslation->insert($new);
                            $data['translation_id'] = $translation_id;

                            if (strpos($locale_path, DIRECTORY_SEPARATOR.'Metric'.DIRECTORY_SEPARATOR)) {
                                $locale_type = 'METRIC';
                            }
                            elseif (strpos($locale_path, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                                $locale_type = 'CUSTOM';
                            }
                            else {
                                $locale_type = 'DEFAULT';
                            }
                            $file_data = array(
                                'translation_id' => $translation_id,
                                'locale_id' => $data['locale_id'],
                                'locale_locale' => $data['locale_locale'],
                                'locale_type' => $locale_type,
                                'extension' => $extension,
                                'file_path' => $locale_path,
                                'file_md5' => md5($locale_path)
                            );
                            $this->i18nTranslationFile->insert($file_data);
                            $write_locale_file = true;


                        }
                    }
                }
            }

            if ($write_locale_file && self::$config['translation']['file']['save']) {
                // get the content of the current locale file
                $file_array = $this->getLocaleFileArray($locale_path);

                // add or update the current translation
                $file_array[$data['locale_source']] = $data['translation_text'];

                // sort the array
                uksort($file_array, array('self', 'sortLocaleTranslations'));

                $locale_array = array();
                foreach ($file_array as $key => $value) {
                    $locale_array["'".str_replace(array("\'","'"), array("'", "\'"), $key)."'"] = "'".str_replace(array("\'", "'"), array("'", "\'"), $value)."'";
                }
                $this->putLocaleFile($locale_path, $locale_array, $extension);
            }
            else {
                $this->setAlert('The translation has not changed.', array(), self::ALERT_TYPE_INFO);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->ControllerTranslationEdit($app, isset($data['translation_id']) ? $data['translation_id'] : -1);
    }

    /**
     * Create form to select a locale file
     *
     * @param string $locale
     * @param integer $file_id
     */
    protected function getLocaleFileForm($locale, $file_id=-1)
    {
        $locale_files = $this->getLocaleFilesForChoice($locale, true, true);
        $files = array();
        $files[-1] = $this->app['translator']->trans('- all files -');
        $i = 0;
        foreach ($locale_files as $key => $value) {
            $files[$i] = $value;
            $i++;
        }

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('locale_locale', 'hidden', array(
            'data' => $locale
        ))
        ->add('locale_file', 'choice', array(
             'choices' => $files,
             'data' => $file_id
        ));
        return $form->getForm();
    }

    /**
     * Show select dropdown for locale files an a list of translations for the
     * current choosen locale file
     *
     * @param string $locale
     * @param integer $file_id
     */
    protected function showLocaleFile($locale, $file_id)
    {
        $locale_path = null; // select all files
        if ($file_id >= 0) {
            $locale_files = $this->getLocaleFilesForChoice($locale, true, true);
            $i = 0;
            foreach ($locale_files as $key => $value) {
                if ($i === $file_id) {
                    $locale_path = $key;
                    break;
                }
                $i++;
            }
        }

        $form = $this->getLocaleFileForm($locale, $file_id);

        if (is_null($locale_path)) {
            // select all files and all non custom translations
            $locales = $this->i18nTranslation->selectTranslated($locale);
            $unassigneds = $this->i18nTranslationUnassigned->selectAllNonCustom();
            if (is_array($unassigneds)) {
                if (!is_array($locales)) {
                    $locales = array();
                }
                foreach ($unassigneds as $unassigned) {
                    $unassigned['translation_id'] = $unassigned['unassigned_id'] + self::unassigned_start_number;
                    $unassigned['locale_id'] = -1;
                    $unassigned['translation_remark'] = 'UNASSIGNED';
                    $unassigned['translation_status'] = 'UNASSIGNED';
                    $locales[] = $unassigned;
                }
            }
            if (!is_array($locales)) {
                $this->setAlert('There a no translated sources available');
            }
        }
        else {
            // select non custom translations for a specific file
            $locales = $this->i18nTranslation->selectByFileMD5(md5($locale_path));
            $unassigneds = $this->i18nTranslationUnassigned->selectByFileMD5(md5($locale_path));
            if (is_array($unassigneds)) {
                if (!is_array($locales)) {
                    $locales = array();
                }
                foreach ($unassigneds as $unassigned) {
                    $unassigned['translation_id'] = $unassigned['unassigned_id'] + self::unassigned_start_number;
                    $unassigned['locale_id'] = -1;
                    $unassigned['translation_remark'] = 'UNASSIGNED';
                    $unassigned['translation_status'] = 'UNASSIGNED';
                    $locales[] = $unassigned;
                }
            }
            if (!is_array($locales)) {
                $this->setAlert('This locale file does not contain any translations!');
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.file.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar($locale),
                'toolbar_locale' => $this->getToolbarLocale('files', $locale),
                'alert' => $this->getAlert(),
                'locale_locale' => $locale,
                'config' => self::$config,
                'info' => self::$info,
                'form' => $form->createView(),
                'locales' => $locales
            ));
    }

    /**
     * General controller for locale files
     *
     * @param Application $app
     * @param string $locale
     * @param integer $file_id
     */
    public function ControllerLocaleFiles(Application $app, $locale, $file_id)
    {
        $this->initialize($app);
        $id = ($file_id > -2) ? $file_id : 0;
        return $this->showLocaleFile($locale, $id);
    }

    /**
     * Check the select form and show the locale list for the choosen file
     *
     * @param Application $app
     * @param string $locale
     */
    public function ControllerLocaleFileSelect(Application $app, $locale)
    {
        $this->initialize($app);

        // get the form
        $form = $this->getLocaleFileForm($locale);
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            return $this->showLocaleFile($locale, $data['locale_file']);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }
        return $this->showLocaleFile($locale, -1);
    }

    /**
     * Show all available custom translations for this installation
     *
     * @param Application $app
     * @param string $locale
     */
    public function ControllerLocaleCustom(Application $app, $locale)
    {
        $this->initialize($app);

        $locales = $this->i18nTranslation->selectTranslatedCustom($locale);
        $unassigneds = $this->i18nTranslationUnassigned->selectAllCustom();
        if (is_array($unassigneds)) {
            if (!is_array($locales)) {
                $locales = array();
            }
            foreach ($unassigneds as $unassigned) {
                $unassigned['translation_id'] = $unassigned['unassigned_id'] + self::unassigned_start_number;
                $unassigned['locale_id'] = -1;
                $unassigned['translation_remark'] = 'UNASSIGNED';
                $unassigned['translation_status'] = 'UNASSIGNED';
                $locales[] = $unassigned;
            }
        }

        if (!is_array($locales)) {
            $this->setAlert('There exists no custom translations for this installation!');
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.custom.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar($locale),
                'toolbar_locale' => $this->getToolbarLocale('custom', $locale),
                'alert' => $this->getAlert(),
                'locale_locale' => $locale,
                'config' => self::$config,
                'info' => self::$info,
                'locales' => $locales
            ));
    }

    /**
     * Get the form to create a custom unassigned translation
     *
     * @param string $locale
     */
    protected function getLocaleCustomForm($locale)
    {
        $locale_files = $this->getLocaleFilesForChoice($locale, false, true);

        $files = array();
        $i = 0;
        foreach ($locale_files as $key => $value) {
            if (false !== strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                $files[$i] = $value;
            }
            $i++;
        }

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('locale_locale', 'hidden', array(
            'data' => $locale
        ))
        ->add('locale_source', 'textarea')
        ->add('translation_text', 'textarea')
        ->add('file_path', 'choice', array(
            'choices' => $files,
            'empty_value' => '- please select -'
        ));
        return $form->getForm();
    }

    /**
     * Show the rendered dialog to create a custom unassigned translation
     *
     * @param string $locale
     * @param FormFactory $form
     */
    protected function showLocaleCustomNew($locale, $form)
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.custom.create.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar($locale),
                'toolbar_locale' => $this->getToolbarLocale('custom', $locale),
                'alert' => $this->getAlert(),
                'locale_locale' => $locale,
                'config' => self::$config,
                'info' => self::$info,
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to create a custom unassigned translation
     *
     * @param Application $app
     * @param string $locale
     */
    public function ControllerLocaleCustomNew(Application $app, $locale)
    {
        $this->initialize($app);
        $form = $this->getLocaleCustomForm($locale);
        return $this->showLocaleCustomNew($locale, $form);
    }

    /**
     * Controller to check the dialog with a custom unassigned translation
     *
     * @param Application $app
     */
    public function ControllerLocaleCustomNewCheck(Application $app)
    {
        $this->initialize($app);

        $locale = (null !== ($form = $this->app['request']->request->get('form'))) ? $form['locale_locale'] : 'en';

        // get the form
        $form = $this->getLocaleCustomForm($locale);
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if (false !== ($source_id = $this->i18nSource->existsMD5(md5($data['locale_source'])))) {
                // this source is already in use
                $this->setAlert('The source <a href="%url%">%source%</a> is already in use, can not insert it as unassigned translation!',
                    array('%url%' => FRAMEWORK_URL.'/admin/i18n/editor/sources/detail/'.$source_id.self::$usage_param,
                        '%source%' => $data['locale_source']), self::ALERT_TYPE_WARNING);
                return $this->showLocaleCustomNew($locale, $form);
            }

            if (false !== ($unassigned_id = $this->i18nTranslationUnassigned->existsMD5(md5($data['locale_source'])))) {
                // this source exists already as unassigned record!
                $this->setAlert('The source <strong>%source%</strong> exists already as unassigned translation record, can not insert it!',
                    array('%source%' => $data['locale_source']), self::ALERT_TYPE_WARNING);
                return $this->showLocaleCustomNew($locale, $form);
            }

            $files = $this->getLocaleFilesForChoice($data['locale_locale']);
            $i = 0;
            foreach ($files as $key => $value) {
                if ($i == $data['file_path']) {
                    // locale path
                    $locale_path = $key;
                    // extension name
                    $extension = ltrim($value, DIRECTORY_SEPARATOR);
                    $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));
                    $target_is_custom = (false !== (strpos($value, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)));
                    break;
                }
                $i++;
            }

            if (strpos($locale_path, DIRECTORY_SEPARATOR.'Metric'.DIRECTORY_SEPARATOR)) {
                $locale_type = 'METRIC';
            }
            elseif (strpos($locale_path, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) {
                $locale_type = 'CUSTOM';
            }
            else {
                $locale_type = 'DEFAULT';
            }

            $insert = array(
                'file_path' => $locale_path,
                'file_md5' => md5($locale_path),
                'extension' => $extension,
                'locale_locale' => $data['locale_locale'],
                'locale_source' => $data['locale_source'],
                'locale_md5' => md5($data['locale_source']),
                'locale_type' => $locale_type,
                'translation_text' => $data['translation_text'],
                'translation_md5' => md5($data['translation_text'])
            );
            // insert the unassigned record
            $this->i18nTranslationUnassigned->insert($insert);

            // get the content of the current locale file
            $file_array = $this->getLocaleFileArray($locale_path);
            // add or update the current translation
            $file_array[$data['locale_source']] = $data['translation_text'];
            // sort the array
            uksort($file_array, array('self', 'sortLocaleTranslations'));
            $locale_array = array();
            foreach ($file_array as $key => $value) {
                $locale_array["'".str_replace(array("\'","'"), array("'", "\'"), $key)."'"] = "'".str_replace(array("\'", "'"), array("'", "\'"), $value)."'";
            }
            $this->putLocaleFile($locale_path, $locale_array, $extension);

            $this->setAlert('Successful inserted a new unassigned translation.',
                array(), self::ALERT_TYPE_SUCCESS);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->ControllerLocaleCustom($app, $locale);
    }
}
