<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Helper;

use Silex\Application;
use phpManufaktur\Contact\Control\Helper\ContactParent;
use phpManufaktur\Contact\Data\Contact\Tag;
use phpManufaktur\Contact\Data\Contact\TagType;

class ContactTag extends ContactParent
{

    protected $Tag = null;
    protected $TagType = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->Tag = new Tag($this->app);
        $this->TagType = new TagType($this->app);
    }

    /**
     * Return the default record
     *
     * @return multitype:number string
     */
    public function getDefaultRecord()
    {
        return $this->Tag->getDefaultRecord();
    }

    /**
     * Validate the given Tag - use this function before inserting a new tag
     *
     * @param array $tag_data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    public function validate(&$tag_data, $contact_data=array(), $option=array())
    {
        // tag name must be set
        if (is_null($tag_data['tag_name']) || !isset($tag_data['tag_name']) || empty($tag_data['tag_name'])) {
            $this->setAlert('Missing the key %field_name%, it must always set and not empty!',
                array('%field_name%' => 'tag_name'), self::ALERT_TYPE_WARNING);
            return false;
        }

        // tag name must be valid
        $matches = array();
        $tag_data['tag_name'] = str_replace(' ', '_', strtoupper($tag_data['tag_name']));
        if (preg_match_all('/[^A-Z0-9_$]/', $tag_data['tag_name'], $matches)) {
            // name check fail
            $this->setAlert('Allowed characters for the %identifier% identifier are only A-Z, 0-9 and the Underscore. The identifier will be always converted to uppercase.',
                array('%identifier%' => 'Tag'), self::ALERT_TYPE_WARNING);
            return false;
        }

        // tag name must exists
        if (!$this->TagType->existsTag($tag_data['tag_name'])) {
            $this->setAlert('The #tag %tag_name% does not exists!',
                array('%tag_name%' => strtoupper($tag_data['tag_name'])), self::ALERT_TYPE_WARNING);
            return false;
        }

        return true;
    }

    /**
     * Insert a TAG
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $tag_id
     * @param reference boolean $has_inserted
     * @return boolean
     */
    public function insert($data, $contact_id, &$tag_id=null, &$has_inserted=null)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;
        $has_inserted = false;

        if (is_null($data['tag_name']) || !isset($data['tag_name']) || empty($data['tag_name'])) {
            // nothing to do...
            return true;
        }
        // validate...
        if (!$this->validate($data)) {
            return false;
        }

        if ($this->Tag->isTagAlreadySet($data['tag_name'], $contact_id)) {
            // nothing to do, TAG is already inserted ...
            return true;
        }

        $this->Tag->insert($data, $tag_id);
        $has_inserted = true;
        return true;
    }

    /**
     * Delete the given $tag_name from the TAG table
     *
     * @param string $tag_name
     */
    public function delete($tag_name)
    {
        $this->Tag->delete($tag_name);
    }

    /**
     * Check if the given $tag_name exists in the TAGTYPE table
     *
     * @param string $tag_name
     * @param integer $exclude_tag_id
     */
    public function existsTag($tag_name, $exclude_tag_id=null)
    {
        return $this->TagType->existsTag($tag_name, $exclude_tag_id);
    }

    /**
     * Create a new Tag Type with the given name and description
     *
     * @param string $tag_name
     * @param string $tag_description
     * @param integer reference $tag_type_id
     * @return integer new tag type ID
     */
    public function createTag($tag_name, $tag_description='', &$tag_type_id=-1) {
        $data = array(
            'tag_name' => $tag_name,
            'tag_description' => $tag_description
        );
        $this->TagType->insert($data, $tag_type_id);
        return $tag_type_id;
    }

    /**
     * Check if a tag is already set for the given contact ID
     *
     * @param string $tag_name
     * @param integer $contact_id
     */
    public function issetContactTag($tag_name, $contact_id)
    {
        return $this->Tag->isTagAlreadySet($tag_name, $contact_id);
    }

}
