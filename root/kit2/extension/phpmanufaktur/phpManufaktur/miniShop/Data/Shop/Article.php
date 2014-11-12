<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Shop;

use Silex\Application;

class Article
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'minishop_article';
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_base = FRAMEWORK_TABLE_PREFIX.'minishop_base';
        $table_group = FRAMEWORK_TABLE_PREFIX.'minishop_group';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `base_id` INT(11) NOT NULL DEFAULT -1,
        `base_name` VARCHAR(64) NOT NULL DEFAULT 'DEFAULT',
        `group_id` INT(11) NOT NULL DEFAULT -1,
        `group_name` VARCHAR(64) NOT NULL DEFAULT 'DEFAULT',
        `status` ENUM('AVAILABLE', 'AVAILABLE_DATE', 'AVAILABLE_DATE_ORDER', 'AVAILABLE_SOON', 'AVAILABLE_SOON_ORDER', 'NOT_AVAILABLE', 'LOCKED', 'DELETED') NOT NULL DEFAULT 'LOCKED',
        `publish_date` DATE NOT NULL DEFAULT '0000-00-00',
        `available_date` DATE NOT NULL DEFAULT '0000-00-00',
        `article_name` VARCHAR(64) NOT NULL DEFAULT '',
        `order_number` VARCHAR(64) NOT NULL DEFAULT '',
        `article_variant_name` VARCHAR(64) NOT NULL DEFAULT '',
        `article_variant_values` TEXT NOT NULL,
        `article_variant_name_2` VARCHAR(64) NOT NULL DEFAULT '',
        `article_variant_values_2` TEXT NOT NULL,
        `permanent_link` TEXT NOT NULL,
        `article_image` TEXT NOT NULL,
        `article_image_folder_gallery` TINYINT UNSIGNED NOT NULL DEFAULT 0,
        `seo_title` VARCHAR(256) NOT NULL DEFAULT '',
        `seo_description` VARCHAR(512) NOT NULL DEFAULT '',
        `seo_keywords` VARCHAR(512) NOT NULL DEFAULT '',
        `description_short` TEXT NOT NULL,
        `description_long` TEXT NOT NULL,
        `article_price` FLOAT(11) NOT NULL DEFAULT -1,
        `article_limit` TINYINT NOT NULL DEFAULT -1,
        `shipping_cost` FLOAT(11) NOT NULL DEFAULT 0,
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX (`group_id`, `group_name`, `base_id`, `base_name`),
        CONSTRAINT
            FOREIGN KEY (`base_id`)
            REFERENCES `$table_base` (`id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`base_name`)
            REFERENCES `$table_base` (`name`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`group_id`)
            REFERENCES `$table_group` (`id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`group_name`)
            REFERENCES `$table_group` (`name`)
            ON DELETE CASCADE
        )
    COMMENT='The article table for the miniShop'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('Created table '.$table, array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop the table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Get the values for the article status
     *
     * @return Ambigous <boolean, array>
     */
    public function getStatusTypes()
    {
        return $this->app['db.utils']->getEnumValues(self::$table_name, 'status');
    }

    /**
     * Select all articles
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status` != 'DELETED' ORDER BY `article_name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $articles = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $articles[] = $item;
                }
            }
            return (!empty($articles)) ? $articles : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given ID
     *
     * @param integer $id
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the article with the given ID
     *
     * @param integer $id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`=$id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $article = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $article[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($article)) ? $article : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the article record by the given permanent link name
     *
     * @param string $permanent_link
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByPermanentLink($permanent_link)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `permanent_link`='$permanent_link'";
            $result = $this->app['db']->fetchAssoc($SQL);
            $article = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $article[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($article)) ? $article : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if a permanent link already exists
     *
     * @param link $permalink
     * @throws \Exception
     * @return boolean
     */
    public function existsPermanentLink($permalink)
    {
        try {
            $SQL = "SELECT `permanent_link` FROM `".self::$table_name."` WHERE `permanent_link`='$permalink' ".
                "AND `status`!='DELETED'";
            $result = $this->app['db']->fetchColumn($SQL);
            return (strtolower($result) === strtolower($permalink));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count PermaLinks which starts LIKE the given $permalink
     *
     * @param string $permalink
     * @throws \Exception
     */
    public function countPermanentLinksLikeThis($permalink)
    {
        try {
            $SQL = "SELECT COUNT(`permanent_link`) FROM `".self::$table_name."` WHERE `permanent_link` ".
                "LIKE '$permalink%' AND `status`!='DELETED'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (isset($insert['id'])) {
                unset($insert['id']);
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record with the given ID
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $check = array('id', 'timestamp');
            foreach ($check as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $update = array();
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    continue;
                }
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('id' => $id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the article ID by the given PermanentLink
     *
     * @param string $permalink
     * @throws \Exception
     * @return ambigous <boolean, integer>
     */
    public function selectArticleIDbyPermaLink($permalink)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `permanent_link`='$permalink' AND `status`!= 'DELETED'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result > 0) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectByGroup($groups, $limit=-1, $order_by='publish_date', $order_direction='ASC',
        $status=array('AVAILABLE', 'AVAILABLE_DATE', 'AVAILABLE_DATE_ORDER', 'AVAILABLE_SOON', 'AVAILABLE_SOON_ORDER', 'NOT_AVAILABLE'))
    {
        try {
            if (!is_array($groups)) {
                throw new \Exception('The parameter groups must be of type array!');
            }
            $in_groups = "('".implode("','", $groups)."')";
            $in_status = "('".implode("','", $status)."')";

            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `group_name` IN $in_groups AND `status` IN $in_status ".
                "ORDER BY `$order_by` $order_direction";
            if ($limit > 0) {
                $SQL .= " LIMIT $limit";
            }
            $results = $this->app['db']->fetchAll($SQL);
            $articles = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $articles[] = $item;
                }
            }
            return (!empty($articles)) ? $articles : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
