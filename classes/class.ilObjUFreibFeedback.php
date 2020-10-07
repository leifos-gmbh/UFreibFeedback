<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/UFreibFeedback/classes/class.ilObjUFreibFeedbackGUI.php");

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjUFreibFeedback extends ilObjectPlugin
{
    /**
     * @var int
     */
    protected $scorm_ref_id;

    /**
     * @var int
     */
    protected $target_role_id;

    /**
     * Constructor
     * @access        public
     * @param int $a_ref_id
     */
    function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    /**
     * Get type.
     */
    final function initType()
    {
        $this->setType(ilUFreibFeedbackPlugin::ID);
    }

    /**
     * Set scorm ref id
     * @param int $a_val scorm ref id
     */
    function setScormRefId($a_val)
    {
        $this->scorm_ref_id = $a_val;
    }

    /**
     * Get scorm ref id
     * @return int scorm ref id
     */
    function getScormRefId()
    {
        return $this->scorm_ref_id;
    }

    /**
     * Set target role id
     * @param int $a_val target role id
     */
    function setTargetRoleId($a_val)
    {
        $this->target_role_id = $a_val;
    }

    /**
     * Get target role id
     * @return int target role id
     */
    function getTargetRoleId()
    {
        return $this->target_role_id;
    }


    /**
     * Create object
     */
    function doCreate()
    {
        global $ilDB;

        $ilDB->manipulate(
            "INSERT INTO rep_robj_xfrf_data " .
            "(id) VALUES (" .
            $ilDB->quote($this->getId(), "integer") .
            ")"
        );
    }

    /**
     * Read data from db
     */
    function doRead()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM rep_robj_xfrf_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setScormRefId((int) $rec["scorm_ref_id"]);
        $this->setTargetRoleId((int) $rec["target_role_id"]);
    }

    /**
     * Update data
     */
    function doUpdate()
    {
        global $ilDB;

        $ilDB->manipulate(
            $up = "UPDATE rep_robj_xfrf_data SET " .
                " scorm_ref_id = " . $ilDB->quote($this->getScormRefId(), "integer") . ", " .
                " target_role_id = " . $ilDB->quote($this->getTargetRoleId(), "integer") .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Delete data from db
     */
    function doDelete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM rep_robj_xfrf_data WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Do Cloning
     */
    function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->setScormRefId($this->getScormRefId());
        $new_obj->setTargetRoleId($this->getTargetRoleId());
        $new_obj->update();
    }
}