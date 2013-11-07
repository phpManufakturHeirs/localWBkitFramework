<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Title
{

    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_title';
    }

    /**
     * Create the TITLE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `title_id` INT(11) NOT NULL AUTO_INCREMENT,
        `title_identifier` VARCHAR(32) NOT NULL DEFAULT 'NO_TITLE',
        `title_short` VARCHAR(32) NOT NULL DEFAULT '',
        `title_long` VARCHAR(64) NOT NULL DEFAULT '',
        PRIMARY KEY (`title_id`),
        UNIQUE INDEX `title_identifier` (`title_identifier` ASC)
        )
    COMMENT='The person title definition table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_title'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop table - switching check for foreign keys off before executing
     *
     * @throws \Exception
     */
    public function dropTable()
    {
        try {
            $table = self::$table_name;
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    DROP TABLE IF EXISTS `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Drop table 'contact_tag'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Initialize the title list with the defaults from /titles.json
     *
     * @throws \Exception
     */
    public function initTitleList()
    {
        try {
            // get the number of titles in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`title_id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/Contact/Data/Setup/Import/titles.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the title definition list: $json_import");
                }
                $titles = $this->app['utils']->readJSON($json_import);
                foreach ($titles as $title) {
                    $this->app['db']->insert(self::$table_name, array(
                        'title_identifier' => $title['identifier'],
                        'title_short' => $this->app['utils']->sanitizeText($title['short']),
                        'title_long' => $this->app['utils']->sanitizeText($title['long'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a array with all titles, prepared for usage with TWIG
     *
     * @throws \Exception
     * @return array
     */
    public function getArrayForTwig()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `title_short` ASC";
            $titles = $this->app['db']->fetchAll($SQL);
            $result = array();
            foreach ($titles as $title) {
                if ($title['title_identifier'] == 'NO_TITLE') {
                    // skip the NO_TITLE entry
                    continue;
                }
                $result[$title['title_identifier']] = $this->app['utils']->unsanitizeText($title['title_short']);
            }
            return $result;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all titles and return a ascending orderd list by identifier
     *
     * @throws \Exception
     * @return Ambigous <multitype:, unknown>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `title_identifier` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $titles = array();
            $level = 0;
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $titles[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $level++;
            }
            return $titles;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the desired title ID and return the record
     *
     * @param integer $title_id
     * @throws \Exception
     * @return array|boolean array on success, false if ID not exists
     */
    public function select($title_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `title_id`='$title_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['title_identifier'])) {
                $title = array();
                foreach ($result as $key => $value) {
                    $title[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $title;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the title ID from the definition table and also the title
     * identifier from all PERSON records!
     *
     * @param integer $title_id
     * @throws \Exception
     * @return boolean
     */
    public function delete($title_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('title_id' => $title_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new TITLE record
     *
     * @param array $data
     * @param reference integer $title_id
     * @throws \Exception
     */
    public function insert($data, &$title_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if ($key == 'title_id') continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $title_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the TITLE for the given ID
     *
     * @param array $data
     * @param integer $title_id
     * @throws \Exception
     */
    public function update($data, $title_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'title_id') || ($key == 'title_identifier')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('title_id' => $title_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given $title_identifier already exists
     *
     * @param string $title_identifier
     * @throws \Exception
     * @return boolean
     */
    public function existsTitle($title_identifier)
    {
        try {
            $SQL = "SELECT `title_identifier` FROM `".self::$table_name."` WHERE `title_identifier`='$title_identifier'";
            return ($title_identifier == $this->app['db']->fetchColumn($SQL));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
