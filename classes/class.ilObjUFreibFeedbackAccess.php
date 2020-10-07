<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/UFreibFeedback/classes/class.ilObjUFreibFeedback.php");

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjUFreibFeedbackAccess extends ilObjectPluginAccess
{

    /**
     * Checks whether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     * @param string $a_cmd        command (not permission!)
     * @param string $a_permission permission
     * @param int    $a_ref_id     reference id
     * @param int    $a_obj_id     object id
     * @param int    $a_user_id    user id (default is current user)
     * @return bool true, if everything is ok
     */
    function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = 0)
    {
        global $ilUser, $ilAccess;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission) {
            case "read":
                /*if (!ilObjUFreibFeedbackAccess::checkOnline($a_obj_id) &&
                    !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
                {
                    return false;
                }*/
                break;
        }

        return true;
    }

}