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

        $this->lng->loadLanguageModule("trac");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn($this->lng->txt("name"), "lastname");
        $this->addColumn($this->lng->txt("status"), "status");
        $this->addColumn($this->plugin->txt("feedback"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->plugin->getDirectory()."/templates/tpl.feedback_row.html");

        $this->addMultiCommand("", $this->lng->txt(""));
        $this->addCommandButton("", $this->lng->txt(""));
    }

    /**
     * Get items
     * @return array[]
     */
    protected function getItems()
    {
        $this->plugin->includeClass("class.ilUFreibFeedbackRepo.php");
        $feedback_repo = new ilUFreibFeedbackRepo();

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

        $tpl->setVariable("USER", $a_set["lastname"].", ".$a_set["firstname"]);

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

        $ctrl->setParameter($this->parent_obj, "recipient", $a_set["user_id"]);
        $link = $ui->factory()->link()->standard(
            $this->plugin->txt("send_feedback"),
            $ctrl->getLinkTarget($this->parent_obj, "showFeedbackForm")
        );

        $tpl->setVariable("ACTION", $ui->renderer()->render($link));
    }
}