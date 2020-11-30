<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Feedback table
 * @author killing@leifos.de
 */
class ilUFreibFeedbackTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var int
     */
    protected $scorm_ref_id;

    /**
     * @var ilPlugin
     */
    protected $plugin;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilUFreibFeedbackRepo
     */
    protected $feedback_repo;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    function __construct($a_parent_obj, $a_parent_cmd, $scorm_ref_id, $plugin)
    {
        global $DIC;

        $this->id = "ufreibfeed";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->plugin = $plugin;
        $this->scorm_ref_id = $scorm_ref_id;
        $this->ui = $DIC->ui();
        $this->user = $DIC->user();

        $this->plugin->includeClass("class.ilUFreibFeedbackRepo.php");
        $this->feedback_repo = new ilUFreibFeedbackRepo();

        $this->lng->loadLanguageModule("trac");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn($this->lng->txt("name"), "lastname");
        $this->addColumn($this->plugin->txt("access_since"));
        $this->addColumn($this->plugin->txt("reminder_sent"));
        $this->addColumn($this->lng->txt("status"), "status");
        $this->addColumn($this->plugin->txt("feedback"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->plugin->getDirectory()."/templates/tpl.feedback_row.html");

        //$this->addMultiCommand("", $this->lng->txt(""));
        //$this->addCommandButton("", $this->lng->txt(""));
    }

    /**
     * Get items
     * @return array[]
     */
    protected function getItems()
    {
        $feedback_repo = $this->feedback_repo;

        return $feedback_repo->getScormFeedbackUsers($this->scorm_ref_id);
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;
        $feedback_repo = $this->feedback_repo;

        // list feedbacks
        ilDatePresentation::setUseRelativeDates(false);
        foreach ($feedback_repo->getFeedbacksForUser($a_set["usr_id"], $this->parent_obj->object->getRefId()) as $f) {
            $send_time = $f["send_time"]
                ? ilDatePresentation::formatDate(new ilDateTime($f["send_time"], IL_CAL_DATETIME))
                : $this->plugin->txt("mail_deleted");
            $feedback = $send_time.", ".$f["sender_name"];
            if (($f["sender_id"] == $this->user->getId()) && $f["mail_id"]) {
                $ctrl->setParameterByClass("ilmailfoldergui", "mail_id", $f["mail_id"]);
                $link = $ui->factory()->link()->standard($feedback,
                    $ctrl->getLinkTargetByClass(["ilmailgui", "ilmailfoldergui"], "showMail"));
                $feedback = $ui->renderer()->render($link);
            }
            $tpl->setCurrentBlock("feedback");
            $tpl->setVariable("FEEDBACK", $feedback);
            $tpl->parseCurrentBlock();
        }


        $tpl->setVariable("USER", $a_set["lastname"].", ".$a_set["firstname"]. " [".$a_set["login"]."]");

        switch ($a_set["status"]) {
            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                $status = $lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
                break;

            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                $status = $lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
                break;

            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                $status = $lng->txt(ilLPStatus::LP_STATUS_COMPLETED);
                break;

            case ilLPStatus::LP_STATUS_FAILED_NUM:
                $status = $lng->txt(ilLPStatus::LP_STATUS_FAILED);
                break;
        }

        $tpl->setVariable("STATUS", $status);
        $ctrl->setParameter($this->parent_obj, "recipient", $a_set["usr_id"]);
        $link = $ui->factory()->link()->standard(
            $this->plugin->txt("send_feedback"),
            $ctrl->getLinkTarget($this->parent_obj, "showFeedbackForm")
        );

        // access since
        $access_since = $feedback_repo->getAccessSinceTS($this->scorm_ref_id, $a_set["usr_id"]);
        if ($access_since > 0) {
            ilDatePresentation::setUseRelativeDates(false);

            $tpl->setVariable("ACCESS", ilDatePresentation::formatDate(new ilDateTime($access_since, IL_CAL_UNIX)));
        }

        // reminder sent
        $reminder_sent = $feedback_repo->getReminderSentTS($this->scorm_ref_id, $a_set["usr_id"]);
        if ($reminder_sent > 0) {
            ilDatePresentation::setUseRelativeDates(false);

            $tpl->setVariable("REMINDER", ilDatePresentation::formatDate(new ilDateTime($reminder_sent, IL_CAL_UNIX)));
        }

        $tpl->setVariable("ACTION", $ui->renderer()->render($link));
    }
}