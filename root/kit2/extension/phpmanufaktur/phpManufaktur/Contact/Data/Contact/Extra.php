<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Extra
{

    protected $app = null;
    protected static $table_name = null;
    protected $ExtraType = null;


    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_extra';
        $this->ExtraType = new ExtraType($app);
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_extra_type = FRAMEWORK_TABLE_PREFIX.'contact_extra_type';
        $table_category = FRAMEWORK_TABLE_PREFIX.'contact_category';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `extra_id` INT(11) NOT NULL AUTO_INCREMENT,
        `extra_type_id` INT(11) DEFAULT NULL,
        `extra_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `category_id` INT(11) NOT NULL DEFAULT '-1',
        `category_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `extra_type_type` ENUM('TEXT','HTML','VARCHAR','INT','FLOAT','DATE','DATETIME','TIME') NOT NULL DEFAULT 'VARCHAR',
        `extra_text` TEXT NOT NULL,
        `extra_html` TEXT NOT NULL,
        `extra_varchar` VARCHAR(255) NOT NULL DEFAULT '',
        `extra_int` INT(11) NOT NULL DEFAULT '0',
        `extra_float` FLOAT NOT NULL DEFAULT '0',
        `extra_date` DATE NOT NULL DEFAULT '0000-00-00',
        `extra_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `extra_time` TIME NOT NULL DEFAULT '00:00:00',
        `extra_timestamp` TIMESTAMP,
        PRIMARY KEY (`extra_id`),
        INDEX (`category_id`, `category_type_name`, `extra_type_id`, `extra_type_name`),
        CONSTRAINT
            FOREIGN KEY (`extra_type_id`)
            REFERENCES $table_extra_type(`extra_type_id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`category_id`)
            REFERENCES $table_category(`category_id`)
            ON DELETE CASCADE
        )
    COMMENT='The table for extra fields associated to a contact'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_extra'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_extra'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function insert($contact_id, $category_id, $category_type_name, $extra_type_id, $value=null, &$extra_id=-1)
    {
        try {
            if (false === ($type = $this->ExtraType->select($extra_type_id))) {
                throw new \Exception("The extra type ID $extra_type_id does not exists!");
            }
            $data = array(
                'extra_type_id' => $extra_type_id,
                'extra_type_name' => $type['extra_type_name'],
                'category_id' => $category_id,
                'category_type_name' => $category_type_name,
                'contact_id' => $contact_id,
                'extra_type_type' => $type['extra_type_type']
            );
            if (!is_null($value)) {
                switch ($type['extra_type_type']) {
                    case 'TEXT':
                        $data['extra_text'] = $this->app['utils']->sanitizeText(strip_tags($value));
                        break;
                    case 'HTML':
                        $data['extra_html'] = $this->app['utils']->sanitizeText($value);
                        break;
                    default:
                        $data[sprintf('extra_%s', strtolower($type['extra_type_type']))] = $value;
                        break;
                }
            }
            $this->app['db']->insert(self::$table_name, $data);
            $extra_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function delete($contact_id, $category_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('contact_id' => $contact_id, 'category_id' => $category_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function select($contact_id, $category_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `category_id`='$category_id'";
            $results = $this->app['db']->fetchAll($SQL);
            if (!is_array($results)) {
                return false;
            }
            $fields = array();
            foreach ($results as $result) {
                $dummy = array();
                foreach ($result as $key => $value) {
                    $dummy[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                switch ($dummy['extra_type_type']) {
                    case 'TEXT':
                        $value = $dummy['extra_text']; break;
                    case 'HTML':
                        $value = $dummy['extra_html']; break;
                    case 'VARCHAR';
                        $value = $dummy['extra_varchar']; break;
                    case 'INT':
                        $value = $dummy['extra_int']; break;
                    case 'FLOAT':
                        $value = $dummy['extra_float']; break;
                    case 'DATE':
                        $value = $dummy['extra_date']; break;
                    case 'DATETIME':
                        $value = $dummy['extra_datetime']; break;
                    case 'TIME':
                        $value = $dummy['extra_time']; break;
                    default:
                        throw new \Exception("Unknown extra field type: {$dummy['extra_type_type']}");
                }
                $fields[] = array(
                    'extra_id' => $dummy['extra_id'],
                    'extra_type_id' => $dummy['extra_type_id'],
                    'extra_type_name' => $dummy['extra_type_name'],
                    'category_id' => $dummy['category_id'],
                    'category_type_name' => $dummy['category_type_name'],
                    'contact_id' => $dummy['contact_id'],
                    'extra_type_type' => $dummy['extra_type_type'],
                    'extra_value' => $value
                );
            }
            return $fields;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function update($extra_id, $data)
    {
        try {
            if (!isset($data['extra_type_type'])) {
                throw new \Exception("Missing the field `extra_type_type`!");
            }
            if (!isset($data['extra_value'])) {
                throw new \Exception("Missing the field `extra_value`!");
            }
            $value = $data['extra_value'];
            unset($data['extra_id']);
            unset($data['extra_value']);

            switch ($data['extra_type_type']) {
                case 'TEXT':
                    $data['extra_text'] = $this->app['utils']->sanitizeText(strip_tags($value));
                    break;
                case 'HTML':
                    $data['extra_html'] = $this->app['utils']->sanitizeText($value);
                    break;
                default:
                    $data[sprintf('extra_%s', strtolower($data['extra_type_type']))] = $value;
                    break;
            }
            $this->app['db']->update(self::$table_name, $data, array('extra_id' => $extra_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
