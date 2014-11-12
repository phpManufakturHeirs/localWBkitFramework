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
use Symfony\Component\Finder\Finder;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Basic\Data\i18n\i18nScanFile;
use phpManufaktur\Basic\Data\i18n\i18nReference;
use phpManufaktur\Basic\Data\i18n\i18nSource;
use phpManufaktur\Basic\Data\i18n\i18nTranslation;
use phpManufaktur\Basic\Data\i18n\i18nTranslationFile;
use phpManufaktur\Basic\Data\i18n\i18nTranslationUnassigned;

class i18nParser extends Alert
{
    protected static $config = null;
    protected $i18nScanFile = null;
    protected $i18nSource = null;
    protected $i18nReference = null;
    protected $i18nTranslation = null;
    protected $i18nTranslationFile = null;
    protected $i18nTranslationUnassigned = null;

    protected static $translation_updated = null;
    protected static $translation_conflicting = null;
    protected static $translation_deleted = null;
    protected static $translation_unassigned = null;


    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->i18nReference = new i18nReference($app);
        $this->i18nSource = new i18nSource($app);
        $this->i18nTranslation = new i18nTranslation($app);
        $this->i18nTranslationFile = new i18nTranslationFile($app);
        $this->i18nTranslationUnassigned = new i18nTranslationUnassigned($app);
        $this->i18nScanFile = new i18nScanFile($app);

    }

    /**
     * Controller to create the tables for the i18nEditor
     *
     * @param Application $app
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerCreateTable(Application $app)
    {
        $this->initialize($app);

        $this->i18nScanFile->createTable();
        $this->i18nSource->createTable();
        $this->i18nReference->createTable();
        $this->i18nTranslation->createTable();
        $this->i18nTranslationFile->createTable();
        $this->i18nTranslationUnassigned->createTable();

        $this->setAlert('Successful created the tables for the i18nEditor.',
            array(), self::ALERT_TYPE_SUCCESS);
        return $this->promptAlertFramework();
    }

    /**
     * Controller to drop the tables for the i18nEditor
     *
     * @param Application $app
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerDropTable(Application $app)
    {
        $this->initialize($app);

        $this->i18nScanFile->dropTable();
        $this->i18nSource->dropTable();
        $this->i18nReference->dropTable();
        $this->i18nTranslation->dropTable();
        $this->i18nTranslationFile->dropTable();
        $this->i18nTranslationUnassigned->dropTable();

        $this->setAlert('Dropped the tables for the i18nEditor.',
            array(), self::ALERT_TYPE_SUCCESS);
        return $this->promptAlertFramework();
    }

    /**
     * Controller to truncate the tables for the i18n Editor
     *
     * @param Application $app
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerTruncateTable(Application $app)
    {
        $this->initialize($app);

        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_reference');
        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_scan_file');
        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_source');
        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation');
        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file');
        $this->app['db.utils']->truncateTable(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_unassigned');

        $this->setAlert('Truncated the tables for the i18nEditor.',
            array(), self::ALERT_TYPE_SUCCESS);
        return $this->promptAlertFramework();
    }

    /**
     * Parse the given PHP file for translation functions and add the detected
     * locale sources to the database
     *
     * @param string $path
     * @throws \Exception
     * @return boolean
     */
    protected function parsePHPfile($path)
    {
        if (!file_exists($path)) {
            $this->setAlert('The file <strong>%file%</strong> does not exists!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (!is_readable($path)) {
            $this->setAlert('The file <strong>%file%</strong> is not readable!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($code = @file_get_contents($path))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => $path), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => $path, 'method' => __METHOD__));
            return false;
        }

        // get all TOKENS from the code
        $tokens = token_get_all($code);

        $expect_locale = false;
        $counter = 0;
        $code_start = 0;
        $start_word = '';
        $current_source = null;

        if (false === ($file_id = $this->i18nScanFile->existsMD5(md5(realpath($path))))) {
            throw new \Exception("Fatal: It exists no data record for the file $path!");
        }
        // first step: delete the existing references!
        $this->i18nReference->deleteFileReferences($file_id);
        $locale_hits = 0;

        foreach ($tokens as $token) {

            $start_scan = false;
            if (is_array($token) && ((($token[0] === T_STRING) && in_array($token[1], self::$config['parse']['php']['start_word']))) ||
                (($token[0] === T_CONSTANT_ENCAPSED_STRING) && in_array($token[1], self::$config['parse']['php']['property_word']))) {
                if ($expect_locale) {
                    $this->app['monolog']->addDebug("[i18nEditor] Can't evaluate the code, started parsing at line $code_start and stopped at {$token[2]}.",
                        array('path' => $path, 'method' => __METHOD__));
                }
                $expect_locale = true;
                $counter = 0;
                $code_start = $token[2];
                $start_word = $token[1];
                $start_scan = true;
            }

            if (!$start_scan && $expect_locale && is_array($token) && ($token[0] === T_CONSTANT_ENCAPSED_STRING)) {
                $check = trim($token[1], "\x22\x27");
                if (empty($check)) {
                    // don't handle empty strings!
                    $expect_locale = false;
                    continue;
                }

                $last_source = $current_source;
                if (in_array($token[1], self::$config['parse']['php']['stop_word'])) {
                    // skip entry and alert to check the program code
                    $this->app['monolog']->addDebug("[i18nEditor] STOP parsing, detect stop word {$token[1]} at line {$token[2]}",
                        array('start_word' => $start_word, 'path' => $path, 'method' => __METHOD__));
                }
                else {
                    if ($start_word === 'add') {
                        // remove leading and trailing " or ' and humanize the string
                        $current_source = $this->app['utils']->humanize(trim($token[1], "\x22\x27"));
                    }
                    else {
                        $current_source = trim($token[1], "\x22\x27");
                    }

                    if (in_array($current_source, self::$config['translation']['ignore']) ||
                        (($current_source[0] === '%') && ($current_source[strlen($current_source)-1] === '%'))) {
                        // ignore this source term
                        $expect_locale = false;
                        continue;
                    }

                    $source_md5 = md5($current_source);
                    if (false === ($locale_id = $this->i18nSource->existsMD5($source_md5))) {
                        // create a new locale source entry
                        $data = array(
                            'locale_source' => $current_source,
                            'locale_locale' => 'EN',
                            'locale_md5' => $source_md5,
                            'locale_remark' => ''
                        );
                        $locale_id = $this->i18nSource->insert($data);
                    }

                    if (!$this->i18nReference->existsReference($locale_id, $file_id, $token[2])) {
                        // create a new reference
                        switch ($start_word) {
                            case 'add':
                                $usage = 'FORM_FIELD'; break;
                            case "'label'":
                                $usage = 'FORM_LABEL'; break;
                            case 'setAlert':
                                $usage = 'ALERT'; break;
                            case 'trans':
                                $usage = 'TRANSLATOR'; break;
                            default:
                                $usage = 'UNKNOWN'; break;
                        }

                        $data = array(
                            'locale_id' => $locale_id,
                            'file_id' => $file_id,
                            'line_number' => $token[2],
                            'locale_usage' => $usage
                        );
                        $this->i18nReference->insert($data);
                        $locale_hits++;
                    }
                }

                if ($current_source === $last_source) {
                    $this->app['monolog']->addDebug("[i18nEditor] Possibly duplicate definition of '$current_source' at line {$token[2]}!",
                        array('path' => $path, 'method' => __METHOD__));
                }

                $expect_locale = false;
            }

            if ($expect_locale && ($counter > 5)) {
                $expect_locale = false;
                if (isset($token[2])) {
                    $this->app['monolog']->addDebug("[i18nEditor] Can't evaluate the code, started parsing at line $code_start and stopped at {$token[2]}.",
                        array('path' => $path, 'method' => __METHOD__));
                }
                else {
                    $this->app['monolog']->addDebug("[i18nEditor] Can't evaluate the code, started parsing at line $code_start and stopped at counter > $counter.",
                        array('path' => $path, 'method' => __METHOD__));
                }
            }
            $counter++;
        }

        // update the file information
        $data = array(
            'file_status' => 'SCANNED',
            'locale_hits' => $locale_hits
        );
        $this->i18nScanFile->update($file_id, $data);

        return true;
    }

    /**
     * Scan the kitFramework for *.php files and them to the database
     *
     */
    protected function findPHPfiles()
    {
        $phpFiles = new Finder();
        $phpFiles
            ->files()
            ->name('*.php')
            ->in(MANUFAKTUR_PATH);

        // exclude all specified *.php files
        foreach (self::$config['finder']['php']['exclude']['file'] as $file) {
            $phpFiles->notName($file);
        }

        // exclude the specified directories
        $phpFiles->exclude(self::$config['finder']['php']['exclude']['directory']);

        $path_array = array();
        foreach ($phpFiles as $file) {
            $realpath = $file->getRealpath();
            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            if (false === ($file_id = $this->i18nScanFile->existsMD5(md5($realpath)))) {
                // insert a new record
                $data = array(
                    'file_type' => 'PHP',
                    'file_path' => $realpath,
                    'file_md5' => md5($realpath),
                    'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                    'file_status' => 'REGISTERED',
                    'extension' => $extension,
                    'template' => 'NONE',
                    'locale_hits' => 0
                );
                $this->i18nScanFile->insert($data);
            }
            else {
                $data = $this->i18nScanFile->select($file_id);
                if ($data['file_mtime'] !== date('Y-m-d H:i:s', $file->getMTime())) {
                    // the file was changed
                    $update = array(
                        'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'file_status' => 'REGISTERED',
                        'locale_hits' => 0
                    );
                    $this->i18nScanFile->update($file_id, $update);
                }
            }
            $path_array[] = $realpath;
        }

        // check for deleted files
        $all_files = $this->i18nScanFile->selectType('PHP');
        foreach ($all_files as $file) {
            if (!in_array(realpath($file['file_path']), $path_array)) {
                $this->i18nScanFile->delete($file['file_id']);
                $this->app['monolog']->addDebug("[i18nEditor] The file ".basename($file['file_path'])." does no longer exists, removed all entries for this file.",
                    array('path' => $file['file_path'], 'file_id' => $file['file_id'], 'method' => __METHOD__));
            }
        }
    }

    /**
     * Find TWIG files in the kitFramework installation and add them to the database
     */
    protected function findTwigFiles()
    {
        $twigFiles = new Finder();
        $twigFiles
            ->files()
            ->name('*.twig')
            ->in(MANUFAKTUR_PATH);

        // exclude all specified *.twig files
        foreach (self::$config['finder']['twig']['exclude']['file'] as $file) {
            $twigFiles->notName($file);
        }

        // exclude the specified directories
        $twigFiles->exclude(self::$config['finder']['twig']['exclude']['directory']);

        $path_array = array();
        foreach ($twigFiles as $file) {
            $realpath = $file->getRealpath();

            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            // extract the template name from the path
            if (!in_array($extension, self::$config['finder']['twig']['template']['name']['exclude'])) {
                if (!in_array($extension, self::$config['finder']['twig']['template']['use_subdirectory'])) {
                    $template = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR)+
                        strlen(DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR));
                    $template = substr($template, 0, strpos($template, DIRECTORY_SEPARATOR));
                }
                else {
                    // extension is using additional subdirectories, i.e. CommandCollection/Template/Comments/default
                    $template = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR)+
                        strlen(DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR));
                    $template = substr($template, strpos($template, DIRECTORY_SEPARATOR+1));
                    $template = substr($template, strpos($template, DIRECTORY_SEPARATOR)+1);
                    $template = substr($template, 0, strpos($template, DIRECTORY_SEPARATOR));
                }
            }
            else {
                // no template name available
                $template = 'NONE';
            }

            if (false === ($file_id = $this->i18nScanFile->existsMD5(md5($file->getRealpath())))) {
                // insert a new record
                $data = array(
                    'file_type' => 'TWIG',
                    'file_path' => $realpath,
                    'file_md5' => md5($realpath),
                    'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                    'file_status' => 'REGISTERED',
                    'extension' => $extension,
                    'template' => $template,
                    'locale_hits' => 0
                );
                $this->i18nScanFile->insert($data);
            }
            else {
                $data = $this->i18nScanFile->select($file_id);
                if ($data['file_mtime'] !== date('Y-m-d H:i:s', $file->getMTime())) {
                    // the file was changed
                    $update = array(
                        'file_mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'file_status' => 'REGISTERED',
                        'locale_hits' => 0
                    );
                    $this->i18nScanFile->update($file_id, $update);
                }
            }
            $path_array[] = $realpath;
        }

        // check for deleted files
        $all_files = $this->i18nScanFile->selectType('TWIG');
        foreach ($all_files as $file) {
            if (!in_array(realpath($file['file_path']), $path_array)) {
                $this->i18nScanFile->delete($file['file_id']);
                $this->app['monolog']->addDebug("[i18nEditor] The file ".basename($file['file_path'])." does no longer exists, removed all entries for this file.",
                    array('path' => $file['file_path'], 'file_id' => $file['file_id'], 'method' => __METHOD__));
            }
        }
    }

    /**
     * Parse the given TWIG file for translations, extract the locale sources and
     * add them to the database
     *
     * @param unknown $path
     * @throws \Exception
     * @return boolean
     */
    protected function parseTwigFile($path)
    {
        if (!file_exists($path)) {
            $this->setAlert('The file <strong>%file%</strong> does not exists!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (!is_readable($path)) {
            $this->setAlert('The file <strong>%file%</strong> is not readable!',
                array('%file%' => basename($path)), self::ALERT_TYPE_DANGER, true,
                array('path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($content = @file($path))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => $path), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => $path, 'method' => __METHOD__));
            return false;
        }

        if (false === ($file_id = $this->i18nScanFile->existsMD5(md5(realpath($path))))) {
            throw new \Exception("Fatal: It exists no data record for the file $path!");
        }
        // first step: delete the existing references!
        $this->i18nReference->deleteFileReferences($file_id);
        $locale_hits = 0;

        foreach ($content as $line_number => $line_content) {
            $matches_array = array();
            foreach (self::$config['parse']['twig']['regex'] as $regex) {
                preg_match_all($regex, $line_content, $matches, PREG_SET_ORDER);
                $matches_array = array_merge($matches_array, $matches);
            }
            foreach ($matches_array as $match) {
                $locale = trim($match[1]);
                if (in_array($locale[0], array('"', "'"))) {
                    $locale = trim($locale, "\x22\x27");

                    // check the filters, perhaps we have to perform the locale string!
                    $check = substr($match[0], strpos($match[0], '|')+1);
                    $check = rtrim($check, ' }');
                    if (strpos($check, '|')) {
                        $params = explode('|', $check);
                        switch (strtolower($params[0])) {
                            case 'humanize':
                                $locale = $this->app['utils']->humanize($locale);
                                break;
                            case 'uppercase':
                                $locale = strtoupper($locale);
                                break;
                            case 'lowercase':
                                $locale = strtolower($locale);
                                break;
                            case 'capitalize':
                                $locale = ucfirst($locale);
                                break;
                        }
                    }

                    if (strpos($locale, "'|humanize")) {
                        $locale = substr($locale, 0, strpos($locale, "'|humanize"));
                    }

                    $locale_md5 = md5($locale);
                    if (false === ($locale_id = $this->i18nSource->existsMD5($locale_md5))) {
                        // create a new locale source entry
                        $data = array(
                            'locale_source' => $locale,
                            'locale_locale' => 'EN',
                            'locale_md5' => $locale_md5,
                            'locale_remark' => ''
                        );
                        $locale_id = $this->i18nSource->insert($data);
                    }

                    if (!$this->i18nReference->existsReference($locale_id, $file_id, $line_number)) {
                        // create a new reference
                        $data = array(
                            'locale_id' => $locale_id,
                            'file_id' => $file_id,
                            'line_number' => $line_number,
                            'locale_usage' => 'TWIG'
                        );
                        $this->i18nReference->insert($data);
                        $locale_hits++;
                    }
                }
            }
        }

        // update the file information
        $data = array(
            'file_status' => 'SCANNED',
            'locale_hits' => $locale_hits
        );
        $this->i18nScanFile->update($file_id, $data);
    }

    /**
     * Update the translation table
     *
     */
    protected function updateTranslationTable()
    {
        // build the translation tables for all needed locales
        if (false !== ($sources = $this->i18nSource->selectAll())) {
            foreach ($sources as $source) {
                foreach (self::$config['translation']['locale'] as $locale) {
                    if (!$this->i18nTranslation->existsLocaleID($source['locale_id'], $locale)) {
                        $data = array(
                            'locale_id' => $source['locale_id'],
                            'locale_source' => $source['locale_source'],
                            'locale_md5' => $source['locale_md5'],
                            'locale_locale' => $locale,
                            'translation_text' => '',
                            'translation_md5' => '',
                            'translation_remark' => '',
                            'translation_status' => 'PENDING'
                        );
                        $this->i18nTranslation->insert($data);
                    }
                }
            }
        }

        // check for widowed locale translations
        $widowed = $this->i18nTranslation->selectWidowed();
        if (is_array($widowed)) {
            foreach ($widowed as $widow) {
                // remove widow translation
                $this->i18nTranslation->deleteLocaleID($widow['locale_id']);
                $this->setAlert('Deleted widowed locale translation with the ID %id%.',
                    array('%id%' => $widow['locale_id']));
            }
        }
    }

    /**
     * Find locale translation files in the kitFramework installation
     *
     */
    protected function findLocaleFiles()
    {
        $localeFiles = new Finder();
        $localeFiles
            ->files()
            ->in(MANUFAKTUR_PATH);

        // add all specified locale files
        foreach (self::$config['translation']['locale'] as $locale) {
            $localeFiles->name(strtolower($locale).'.php');
        }

        // exclude all specified *.php files
        foreach (self::$config['finder']['locale']['exclude']['file'] as $file) {
            $localeFiles->notName($file);
        }

        // exclude the specified directories
        $localeFiles->exclude(self::$config['finder']['locale']['exclude']['directory']);
        $file_count = 0;
        $translation_count = 0;

        self::$translation_conflicting = array();
        self::$translation_unassigned = array();

        // truncate the table for unassigned translation before starting
        $this->i18nTranslationUnassigned->truncateTable();

        foreach ($localeFiles as $file) {
            $realpath = $file->getRealpath();

            // extract the extension directory from the path
            $extension = substr($realpath, strlen(realpath(MANUFAKTUR_PATH))+1);
            $extension = substr($extension, 0, strpos($extension, DIRECTORY_SEPARATOR));

            $locale_path = substr($realpath, strpos($realpath, DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR)+
                strlen(DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR));

            // get the locale type DEFAULT, CUSTOM or METRIC
            $locale_type = 'DEFAULT';
            if (strpos($locale_path, DIRECTORY_SEPARATOR)) {
                $locale_type = strtoupper(substr($locale_path, 0, strpos($locale_path, DIRECTORY_SEPARATOR)));
            }
            // get the LOCALE
            $locale = strtoupper(pathinfo($realpath, PATHINFO_FILENAME));

            // get all TOKENS from the code
            $code = file_get_contents($realpath);
            $tokens = token_get_all($code);

            $walking = false;
            $key = null;

            $key_array = array();

            foreach ($tokens as $token) {
                if ($token[0] ===  T_ARRAY) {
                    $walking = true;
                }
                if (!$walking) continue;

                if (is_null($key) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
                    // this is the first part: the KEY is the locale_source
                    $key = $token[1];
                    continue;
                }
                if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                    // this is the second part: the VALUE is the translation_text
                    $locale_source = trim($key, "\x22\x27");
                    $locale_source = str_replace(array("\'"), array("'"), $locale_source);

                    // important: set the KEY to NULL and try to get the next pair
                    $key = null;

                    if (in_array($locale_source, $key_array)) {
                        // Ooops - we have an duplicate location key in the translanslation file !!!
                        $this->setAlert('There exist an duplicate locale key for <strong>%key%</strong> in the file <em>%file%</em>!',
                            array('%key%' => $locale_source, '%file%' => $realpath), self::ALERT_TYPE_DANGER, true,
                            array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'method' => __METHOD__));
                        // continue loop!
                        continue;
                    }
                    $key_array[] = $locale_source;

                    // trim leading and trailing ' and "
                    $translation_text = trim($token[1], "\x22\x27");
                    $translation_text = str_replace(array("\'"), array("'"), $translation_text);

                    $locale_md5 = md5($locale_source);
                    $translation_md5 = md5($translation_text);

                    if (false !== ($translation_id = $this->i18nTranslation->existsMD5($locale_md5, $locale))) {
                        // translation record exists - get the record
                        $translation = $this->i18nTranslation->select($translation_id);
                        if ($translation['translation_status'] === 'PENDING') {
                            // insert the translation
                            $data = array(
                                'translation_text' => $translation_text,
                                'translation_md5' => $translation_md5,
                                'translation_status' => 'TRANSLATED'
                            );
                            $this->i18nTranslation->update($translation_id, $data);

                            // add a new translation file information
                            $data = array(
                                'translation_id' => $translation_id,
                                'locale_id' => $translation['locale_id'],
                                'locale_locale' => $locale,
                                'locale_type' => $locale_type,
                                'extension' => $extension,
                                'file_path' => $realpath,
                                'file_md5' => md5($realpath)
                            );
                            $this->i18nTranslationFile->insert($data);
                        }
                        elseif ($translation['translation_status'] === 'TRANSLATED') {
                            // there exists already an translation
                            if (false !== ($file = $this->i18nTranslationFile->selectByExtension($translation_id, $locale, $extension))) {
                                if (($translation_md5 !== $translation['translation_md5']) && ($file['locale_type'] === 'CUSTOM')) {
                                    // translation is overwritten by a CUSTOM translation - create the regular one!

                                    // get the locale ID
                                    $locale_id = $this->i18nSource->existsMD5($locale_md5);

                                    // insert a new translation record
                                    $data = array(
                                        'locale_id' => $locale_id,
                                        'locale_source' => $locale_source,
                                        'locale_md5' => $locale_md5,
                                        'locale_locale' => $locale,
                                        'translation_text' => $translation_text,
                                        'translation_md5' => $translation_md5,
                                        'translation_remark' => '',
                                        'translation_status' => 'TRANSLATED'
                                    );
                                    $translation_id = $this->i18nTranslation->insert($data);

                                    // add a new translation file information
                                    $data = array(
                                        'translation_id' => $translation_id,
                                        'locale_id' => $locale_id,
                                        'locale_locale' => $locale,
                                        'locale_type' => $locale_type,
                                        'extension' => $extension,
                                        'file_path' => $realpath,
                                        'file_md5' => md5($realpath)
                                    );
                                    $this->i18nTranslationFile->insert($data);

                                    /* OLD SOLUTION - can be remove later ...
                                    $this->app['monolog']->addDebug("[i18nEditor] Translation ID {$translation['translation_id']} is overwritten by CUSTOM translation with File ID {$file['file_id']}!",
                                        array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                    */
                                }
                                elseif ($translation_md5 !== $translation['translation_md5']) {
                                    // the translation has changed
                                    if (false !== ($files = $this->i18nTranslationFile->selectByTranslationID($translation_id, $locale))) {
                                        if ((count($files) === 1) && ($files[0]['extension'] === $extension)) {
                                            // update the translation
                                            $data = array(
                                                'translation_text' => $translation_text,
                                                'translation_md5' => $translation_md5,
                                                'translation_status' => 'TRANSLATED'
                                            );

                                            $this->i18nTranslation->update($translation_id, $data);
                                            $this->app['monolog']->addDebug("[i18nEditor] Updated Translation ID $translation_id.",
                                                array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                            $this->setAlert('Updated Translation ID %id%', array('%id%' => $translation_id), self::ALERT_TYPE_SUCCESS);
                                        }
                                        else {
                                            // CONFLICTING translation
                                            $data = array(
                                                'translation_status' => 'CONFLICT'
                                            );
                                            $this->i18nTranslation->update($translation_id, $data);
                                            $this->app['monolog']->addDebug("[i18nEditor] There exists CONFLICTING translations for the translation ID $translation_id",
                                                array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                            self::$translation_conflicting[] = $translation_id;
                                        }
                                    }
                                }
                            }
                            else {
                                // Ooops, missing a translation file?
                                $data = array(
                                    'translation_id' => $translation_id,
                                    'locale_id' => $translation['locale_id'],
                                    'locale_locale' => $locale,
                                    'locale_type' => $locale_type,
                                    'extension' => $extension,
                                    'file_path' => $realpath,
                                    'file_md5' => md5($realpath)
                                );
                                // add a new translation file information!
                                $this->i18nTranslationFile->insert($data);

                                if ($translation['translation_md5'] !== $translation_md5) {
                                    // this translation causes a CONFLICT!
                                    $data = array(
                                        'translation_status' => 'CONFLICT'
                                    );
                                    $this->i18nTranslation->update($translation_id, $data);
                                    $this->app['monolog']->addDebug("[i18nEditor] There exists CONFLICTING translations for the translation ID $translation_id",
                                        array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                                    self::$translation_conflicting[] = $translation_id;
                                }
                            }
                        }
                        elseif ($translation['translation_status'] === 'CONFLICT') {
                            // this translation is already marked as CONFLICT - skip file, conflicts will be checked later ...

                        }
                        else {
                            // check the translation
                            $this->setAlert("Ooops, don't know how to handle the locale source '%source%', please check the protocol.",
                                array('%source%' => $locale_source), self::ALERT_TYPE_DANGER, true,
                                array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                        }
                    }
                    else {
                        // missing locale source !!!
                        if (in_array($locale_source, self::$config['translation']['system'])) {
                            // these locale is defined by the system, still add to the source!
                            if (false === ($locale_id = $this->i18nSource->existsMD5($locale_md5))) {
                                $data = array(
                                    'locale_source' => $locale_source,
                                    'locale_locale' => 'EN',
                                    'locale_md5' => $locale_md5,
                                    'locale_remark' => 'SYSTEM'
                                );
                                $locale_id = $this->i18nSource->insert($data);
                            }

                            $data = array(
                                'locale_id' => $locale_id,
                                'locale_source' => $locale_source,
                                'locale_md5' => $locale_md5,
                                'locale_locale' => $locale,
                                'translation_text' => $translation_text,
                                'translation_md5' => $translation_md5,
                                'translation_remark' => 'SYSTEM',
                                'translation_status' => 'TRANSLATED'
                            );
                            $translation_id = $this->i18nTranslation->insert($data);

                            // add a new translation file information
                            $data = array(
                                'translation_id' => $translation_id,
                                'locale_id' => $locale_id,
                                'locale_locale' => $locale,
                                'locale_type' => $locale_type,
                                'extension' => $extension,
                                'file_path' => $realpath,
                                'file_md5' => md5($realpath)
                            );
                            $this->i18nTranslationFile->insert($data);
                        }
                        else {
                            // translation is unassigned - probably the parser can not assign the translation to any source
                            // i.e. because a translation command is missing in source?
                            $data = array(
                                'extension' => $extension,
                                'locale_locale' => $locale,
                                'locale_source' => $locale_source,
                                'locale_md5' => $locale_md5,
                                'locale_type' => $locale_type,
                                'translation_text' => $translation_text,
                                'translation_md5' => $translation_md5,
                                'file_path' => $realpath,
                                'file_md5' => md5($realpath)
                            );

                            self::$translation_unassigned[] = $this->i18nTranslationUnassigned->insert($data);

                            $this->app['monolog']->addDebug("[i18nEditor] Can not assign translation to any source!",
                                array('extension' => $extension, 'locale' => $locale, 'locale_source' => $locale_source, 'translation_text' => $translation_text, 'method' => __METHOD__));
                        }
                    }
                    $translation_count++;
                }
            }
            $file_count++;
        }
    }

    /**
     * Loop through all registered conflicts and check if the conflict is solved
     *
     * @return boolean
     */
    protected function checkConflicts()
    {
        if (false === ($conflicts = $this->i18nTranslation->selectConflicts())) {
            return;
        }

        foreach ($conflicts as $conflict) {
            $conflict_solved = true;
            $files = $this->i18nTranslationFile->selectByLocaleID($conflict['locale_id'], $conflict['locale_locale']);

            if (count($files) > 1) {
                // if only one file exists, the conflict is automatically solved!
                foreach ($files as $file) {
                    // get all TOKENS from the code
                    $code = file_get_contents(realpath($file['file_path']));
                    $tokens = token_get_all($code);

                    $walking = false;
                    $key = null;
                    $source_detected = false;

                    foreach ($tokens as $token) {
                        if ($token[0] ===  T_ARRAY) {
                            $walking = true;
                        }
                        if (!$walking) continue;

                        if (is_null($key) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
                            // this is the first part: the KEY is the locale_source
                            $key = $token[1];
                            continue;
                        }
                        if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                            // this is the second part: the VALUE is the translation_text
                            $locale_source = trim($key, "\x22\x27");

                            // important: set the KEY to NULL and try to get the next pair
                            $key = null;

                            // trim leading and trailing ' and "
                            $translation_text = trim($token[1], "\x22\x27");

                            $locale_md5 = md5($locale_source);
                            $translation_md5 = md5($translation_text);

                            if ($locale_md5 === $conflict['locale_md5']) {
                                // ok - this is the conflicting entry
                                if ($translation_md5 !== $conflict['translation_md5']) {
                                    // the conflict is NOT solved
                                    $conflict_solved = false;
                                }
                                // important: set a mark that the source exists in the file!
                                $source_detected = true;

                                // here we can leave the TOKENS loop
                                break;
                            }
                        }
                    }

                    if (!$source_detected) {
                        // the conflicting entry does no longer exists!
                        $this->i18nTranslationFile->delete($file['file_id']);
                        if ((count($files) - 1) === 1) {
                            // only one file left ... so the conflict is automatically solved!
                            $conflict_solved = true;
                        }
                    }
                }
            }

            if ($conflict_solved) {
                // conflict is SOLVED!
                $data = array(
                    'translation_status' => 'TRANSLATED'
                );
                $this->i18nTranslation->update($conflict['translation_id'], $data);
                $this->setAlert('The translation conflict for the locale source <strong>%source%</strong> has been solved!',
                    array('%source%' => $conflict['locale_source']), self::ALERT_TYPE_SUCCESS, true,
                    array('locale' => $conflict['locale_locale'], 'locale_source' => $conflict['locale_source'], 'method' => __METHOD__));
            }
        }
    }

    /**
     * Get the with $path given locale file and return the translations as array.
     * If the file does not exist, the function return an empty array
     *
     * @param string $path
     * @return array
     */
    protected function getLocaleFileArray($path)
    {
        if (!$this->app['filesystem']->exists($path)) {
            // this file currently does not exists - return empty array!
            return array();
        }

        // get all TOKENS from the code
        $code = file_get_contents($path);
        $tokens = token_get_all($code);

        $walking = false;
        $key = null;

        $key_array = array();
        $translations = array();

        foreach ($tokens as $token) {
            if ($token[0] ===  T_ARRAY) {
                $walking = true;
            }
            if (!$walking) continue;

            if (is_null($key) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
                // this is the first part: the KEY is the locale_source
                $key = $token[1];
                continue;
            }

            if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                // this is the second part: the VALUE is the translation_text
                $source = trim($key, "\x22\x27");

                // important: set the KEY to NULL and try to get the next pair
                $key = null;

                if (in_array($source, $key_array)) {
                    // Ooops - we have an duplicate location key in the translanslation file !!!
                    continue;
                }
                $key_array[] = $source;

                $translations[$source] = trim($token[1], "\x22\x27");
            }
        }

        return $translations;
    }

    /**
     * Put the translation as locale file to the given path. This function check
     * the configuration, create backups and copy the locale file to source directories
     *
     * @param string $path
     * @param array $translations
     * @param string $extension
     * @return boolean
     */
    protected function putLocaleFile($path, $translations, $extension='UNKNOWN', $no_alert=false)
    {
        if (!self::$config['translation']['file']['save']) {
            // saving locale files is diaabled
            return false;
        }

        // create the locale file content
        $content = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/locale.php.twig'),
            array(
                'translations' => $translations,
                'extension' => $extension
            ));

        if ($this->app['filesystem']->exists($path) && self::$config['translation']['file']['backup']) {
            // create a backup file before writing the file
            $backup_path = str_replace('.php', '.bak', $path);
            // remove existing backup file
            $this->app['filesystem']->remove($backup_path);
            $this->app['filesystem']->rename($path, $backup_path);
        }

        if (false === file_put_contents($path, $content)) {
            $this->setAlert("Can't save the file %file%!", array('%file%', basename($path)), self::ALERT_TYPE_DANGER,
                true, array('error' => error_get_last(), 'method' => __METHOD__, 'line' => __LINE__));
            return false;
        }

        if (self::$config['developer']['enabled'] && self::$config['developer']['source']['copy']) {
            // developer mode is enabled and locales should be copied ...
            if (isset(self::$config['developer']['source']['extension'][$extension]) &&
                $this->app['filesystem']->exists(self::$config['developer']['source']['extension'][$extension])) {
                // locales for this extension should be copied to the source directory
                $subdirectory = substr($path,
                    strpos($path, DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR)+
                    strlen(DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR));
                if (self::$config['developer']['source']['custom'] ||
                    (false === strpos($subdirectory, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) ||
                    ((false !== strpos($subdirectory, DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR)) &&
                     false !== self::$config['developer']['source']['custom'])) {
                    // copy the locale file also to the source directory
                    $source_path = rtrim(self::$config['developer']['source']['extension'][$extension], DIRECTORY_SEPARATOR);
                    $source_path .= DIRECTORY_SEPARATOR.$subdirectory;
                    if (self::$config['developer']['source']['backup'] && $this->app['filesystem']->exists($source_path)) {
                        $backup_path = str_replace('.php', '.bak', $source_path);
                        // remove existing backup file
                        $this->app['filesystem']->remove($backup_path);
                        // create backup file
                        $this->app['filesystem']->rename($source_path, $backup_path);
                    }
                    // copy the locale file to the source directory
                    $this->app['filesystem']->copy($path, $source_path);
                    $this->setAlert('Copied the locale file to the source path: <i>%path%</i>',
                        array('%path%' => $source_path), self::ALERT_TYPE_SUCCESS);
                }

            }
        }

        if (!$no_alert) {
            $this->setAlert('Successfull put the locale entries to the locale file.', array(), self::ALERT_TYPE_INFO);
        }

        return true;
    }
}
