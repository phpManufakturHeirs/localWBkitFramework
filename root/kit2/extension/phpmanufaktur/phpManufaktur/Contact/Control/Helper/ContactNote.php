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
use phpManufaktur\Contact\Data\Contact\Note;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;

class ContactNote extends ContactParent
{
    protected $Note = null;
    protected $ContactData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->Note = new Note($this->app);
        $this->ContactData = new ContactData($this->app);
    }

    /**
     * Return a default (empty) PERSON contact record.
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return $this->Note->getDefaultRecord();
    }

    /**
     * Validate the given NOTE record
     *
     * @param reference array $note_data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    public function validate(&$note_data, $contact_data=array(), $option=array())
    {
        // the note_id must be always set!
        if (!isset($note_data['note_id'])) {
            $this->setAlert("Missing the %identifier%! The ID should be set to -1 if you insert a new record.",
                array('%identifier%' => 'note_id'), self::ALERT_TYPE_WARNING);
            return false;
        }

        // check if any value is NULL
        foreach ($note_data as $key => $value) {
            if (is_null($value)) {
                switch ($key) {
                    case 'note_title':
                    case 'note_content':
                        $note_data[$key] = '';
                        break;
                    case 'contact_id':
                        $note_data[$key] = -1;
                        break;
                    case 'note_type':
                        $note_data[$key] = 'TEXT';
                        break;
                    case 'note_status':
                        $note_data[$key] = 'ACTIVE';
                        break;
                    default:
                        throw new ContactException("The key $key is not defined!");
                        break;
                }
            }
        }
        return true;
    }

    /**
     * Insert a new NOTE record. Check first for values which belong to depending
     * contact tables
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $note_id
     * @param reference boolean $has_inserted
     * @throws ContactException
     * @return boolean
     */
    public function insert($data, $contact_id, &$note_id=null, &$has_inserted=null)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;
        $has_inserted = false;

        if (!empty($data['note_content'])) {
            if (!$this->validate($data)) {
                return false;
            }
            $note_id = -1;
            $this->Note->insert($data, $note_id);
            $has_inserted = true;
            $this->app['monolog']->addInfo("Inserted note record for the contactID {$contact_id}", array(__METHOD__, __LINE__));
            if ($this->ContactData->getPrimaryNoteID($contact_id) < 1) {
                $this->ContactData->setPrimaryNoteID($contact_id, $note_id);
                $this->app['monolog']->addInfo("Set note ID $note_id as primary ID for contact $contact_id");
            }
        }
        return true;
    }

    /**
     * Process the update for the given note record
     *
     * @param array $new_data the note record to update
     * @param array $old_data the existing note record from database
     * @param integer $note_id
     * @param reference boolean $has_changed is set to true if the not was updated
     * @return boolean
     */
    public function update($new_data, $old_data, $note_id, &$has_changed)
    {
        $has_changed = false;

        if (empty($new_data['note_content'])) {
            // check if the entry can be deleted
            if ($this->ContactData->getPrimaryNoteID($old_data['contact_id']) == $note_id) {
                $this->setAlert("Can't delete the Note with the ID %note_id% because it is used as primary note for this contact.",
                    array('%note_id%' => $note_id), self::ALERT_TYPE_WARNING);
                return false;
            }
            // delet the note
            $this->Note->delete($note_id);
            $this->setAlert('The record with the ID %id% was successfull deleted.',
                array('%id%' => $note_id), self::ALERT_TYPE_SUCCESS);
            $has_changed = true;
            return true;
        }

        // now we can validate the address
        if (!$this->validate($new_data)) {
            return false;
        }

        // process the new data
        $changed = array();
        foreach ($new_data as $key => $value) {
            if (($key == 'note_id') || ($key == 'note_timestamp')) continue;
            if (isset($old_data[$key]) && ($old_data[$key] != $value)) {
                $changed[$key] = $value;
            }
        }

        if (!empty($changed)) {
            // update the communication record
            $this->Note->update($changed, $note_id);
            $has_changed = true;
        }

        return true;
    }
}

