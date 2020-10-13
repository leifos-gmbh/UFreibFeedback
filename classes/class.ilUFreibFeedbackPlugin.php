<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUFreibFeedbackPlugin extends ilRepositoryObjectPlugin
{
    const ID = "xfrf";

    // must correspond to the plugin subdirectory
    function getPluginName()
    {
        return "UFreibFeedback";
    }

    protected function uninstallCustom()
    {
        // TODO: Nothing to do here.
    }

    /**
     * @inheritdoc
     */
    public function allowCopy()
    {
        return true;
    }

    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        // note: the event is raised by the UFreibPsyUI plugin, since it
        // is not part of the ILIAS core
        // string(13) "Services/Mail" string(8) "mailRead" array(2) { ["mail_id"]=> int(13) ["user_id"]=> int(300) }

        if ($a_component == "Services/Mail" && $a_event == "mailRead") {
            $mail_id = $a_parameter["mail_id"];
            $user_id = $a_parameter["user_id"];

            $this->includeClass("class.ilUFreibFeedbackRepo.php");
            $feedback_repo = new ilUFreibFeedbackRepo();
            $this->includeClass("class.ilUFreibScormRoleRepo.php");
            $role_repo = new ilUFreibScormRoleRepo();

            $feedb_ref_ids = $feedback_repo->getTriggerFeedbackRefIdsForRecipientMail($user_id, $mail_id);
            foreach ($feedb_ref_ids as $feedb_ref_id) {
                // assign user to follow-up role
                $role_repo->assignUserToFollowUpRole($user_id, $feedb_ref_id);
            }
            // get the triggering scorm ref id
        }
    }

}