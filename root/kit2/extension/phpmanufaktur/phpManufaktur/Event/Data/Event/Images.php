<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;

class Images
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_images';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_event = FRAMEWORK_TABLE_PREFIX.'event_event';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `image_id` INT(11) NOT NULL AUTO_INCREMENT,
        `event_id` INT(11) NOT NULL DEFAULT '-1',
        `image_title` VARCHAR(255) NOT NULL DEFAULT '',
        `image_text` TEXT NOT NULL,
        `image_path` TEXT NOT NULL,
        `image_width` INT(11) NOT NULL DEFAULT '0',
        `image_height` INT(11) NOT NULL DEFAULT '0',
        `image_timestamp` TIMESTAMP,
        PRIMARY KEY (`image_id`),
        INDEX (`event_id`),
        CONSTRAINT
            FOREIGN KEY (`event_id`)
            REFERENCES $table_event (`event_id`)
            ON DELETE CASCADE
        )
    COMMENT='Images associated to Events'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_images'", array(__METHOD__, __LINE__));
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
        try {
            $table = self::$table_name;
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    DROP TABLE IF EXISTS `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Drop table 'event_images'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get a default record for a IMAGE
     *
     * @param integer $event_id
     * @return multitype:number string unknown
     */
    public function getDefaultRecord($event_id=-1)
    {
        return array(
            'image_id' => -1,
            'event_id' => $event_id,
            'image_title' => '',
            'image_text' => '',
            'image_path' => '',
            'image_width' => 0,
            'image_height' => 0,
            'image_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Insert a new image
     *
     * @param array $data
     * @param reference integer $image_id
     * @throws \Exception
     */
    public function insert($data, &$image_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'image_id') || ($key == 'image_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            if (!isset($data['event_id'])) {
                throw new \Exception("Missing the Event ID, can't insert the image!");
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $extra_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectByEventID($event_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `event_id`='$event_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $images = array();
            foreach ($results as $image) {
                $record = array();
                foreach ($image as $key => $value) {
                    $record[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $images[] = $record;
            }
            return $images;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function delete($image_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('image_id' => $image_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
