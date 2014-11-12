<?php

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;

class ParticipantTag
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_participant_tag';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_tag_type = FRAMEWORK_TABLE_PREFIX.'contact_tag_type';
        $table_group = FRAMEWORK_TABLE_PREFIX.'event_group';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) DEFAULT NULL,
        `tag_name` VARCHAR(32) DEFAULT NULL,
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`)
        )
    COMMENT='The table to assign participant tags to event groups'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_participant_tag'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_participant_tag'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @param reference integer $id
     * @throws \Exception
     */
    public function insert($data, &$id=null)
    {
        try {
            $this->app['db']->insert(self::$table_name, $data);
            $id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select TAG names by the given group ID
     *
     * @param integer $group_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectTagNamesByGroupID($group_id)
    {
        try {
            $SQL = "SELECT `tag_name` FROM `".self::$table_name."` WHERE `group_id`='$group_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $tag_names = array();
            foreach ($results as $tag) {
                $tag_names[] = $tag['tag_name'];
            }
            return empty($tag_names) ? false : $tag_names;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the given TAG name assigned to the given group ID
     *
     * @param string $tag_name
     * @param integer $group_id
     * @throws \Exception
     */
    public function deleteTagByGroup($tag_name, $group_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array(
                'tag_name' => $tag_name,
                'group_id' => $group_id
            ));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
