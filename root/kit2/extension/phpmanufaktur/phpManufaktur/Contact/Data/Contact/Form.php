<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Form
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_form';
    }

    /**
     * Create the FORM table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `form_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `contact_data` TEXT NOT NULL,
        `form_name` VARCHAR(255) NOT NULL DEFAULT '',
        `form_config` TEXT NOT NULL,
        `form_data` TEXT NOT NULL,
        `form_submitted_when` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `form_submitter_ip` VARCHAR(64) NOT NULL DEFAULT '0.0.0.0',
        `form_timestamp` TIMESTAMP,
        PRIMARY KEY (`form_id`),
        INDEX (`contact_id`)
        )
    COMMENT='The contact form table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_form'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete table - switching check for foreign keys off before executing
     *
     * @throws \Exception
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

/**
     * Insert a new record in the FORM table
     *
     * @param array $data
     * @param reference integer $form_id
     * @throws \Exception
     */
    public function insert($data, &$form_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'form_id') || ($key == 'form_timestamp')) continue;
                $insert[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!isset($insert['form_submitted_when'])) {
                // add the 'form_submitted_when' field with the actual date/time
                $insert['form_submitted_when'] = date('Y-m-d H:i:s');
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $contact_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
