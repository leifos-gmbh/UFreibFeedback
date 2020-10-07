<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/UFreibFeedback/classes/class.ilUFreibFeedbackPlugin.php");

/**
 * @author            Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilObjUFreibFeedbackGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjUFreibFeedbackGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilPropertyFormGUI
 */
class ilObjUFreibFeedbackGUI extends ilObjectPluginGUI
{
    /** @var  ilCtrl */
    protected $ctrl;

    /** @var  ilTabsGUI */
    protected $tabs;

    /** @var  ilTemplate */
    public $tpl;

    /**
     * Initialisation
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();

        $this->umail = new ilFormatMail($this->user->getId());
        $this->purifier = new ilMailBodyPurifier();

        $this->ctrl->saveParameter($this, "recipient");
    }

    /**
     * Get type.
     */
    final function getType()
    {
        return ilUFreibFeedbackPlugin::ID;
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    function performCommand($cmd)
    {
        $ctrl = $this->ctrl;

        switch ($ctrl->getNextClass()) {

            case "ilpropertyformgui":
                $form = $this->initPropertiesForm();
                $ctrl->forwardCommand($form);
                break;

            default:
                switch ($cmd) {
                    case "editProperties":   // list all commands that need write permission here
                    case "updateProperties":
                    case "saveProperties":
                    case "showFeedbacks":   // list all commands that need read permission here
                    default:
                        $this->checkPermission("read");
                        $this->$cmd();
                        break;
                }
                break;
        }
    }

    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return "editProperties";
    }

    /**
     * Get standard command
     */
    function getStandardCmd()
    {
        return "showFeedbacks";
    }

//
// DISPLAY TABS
//

    /**
     * Set tabs
     */
    function setTabs()
    {
        global $ilCtrl, $ilAccess;

        // tab for the "show content" command
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab("feedbacks", $this->plugin->txt("feedbacks"), $ilCtrl->getLinkTarget($this, "showFeedbacks"));
        }

        // standard info screen tab
        $this->addInfoTab();

        // a "properties" tab
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "properties",
                $this->txt("properties"),
                $ilCtrl->getLinkTarget($this, "editProperties")
            );
        }

        // standard export tab
        //$this->addExportTab();

        // standard permission tab
        $this->addPermissionTab();
    }

    /**
     * Edit Properties. This commands uses the form class to display an input form.
     */
    protected function editProperties()
    {
        $this->tabs->activateTab("properties");
        $form = $this->initPropertiesForm();
        $this->addValuesToForm($form);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function initPropertiesForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt("obj_xfrf"));

        $title = new ilTextInputGUI($this->plugin->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextInputGUI($this->plugin->txt("description"), "description");
        $form->addItem($description);

        // scorm ref id
        $scorm_ref_id = new ilRepositorySelector2InputGUI($this->plugin->txt("scorm_object"), "scorm_ref_id");
        $scorm_ref_id->getExplorerGUI()->setSelectableTypes(["sahs"]);
        $scorm_ref_id->getExplorerGUI()->setTypeWhiteList(
            ["sahs", "root", "cat", "grp", "fold", "crs"]
        );
        $form->addItem($scorm_ref_id);

        // role id
        $this->plugin->includeClass("class.ilUFreibScormRoleRepo.php");
        $role_repo = new ilUFreibScormRoleRepo();
        $roles = $role_repo->getRolesForScormRefId($this->object->getScormRefId());
        $si = new ilSelectInputGUI($this->plugin->txt("target_role"), "target_role_id");
        $si->setOptions($roles);
        $si->setInfo($this->plugin->txt("target_role_info"));

        $form->addItem($si);

        $form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));
        $form->addCommandButton("saveProperties", $this->plugin->txt("update"));



        return $form;
    }

    /**
     * @param $form ilPropertyFormGUI
     */
    protected function addValuesToForm(&$form)
    {
        $form->setValuesByArray(
            array(
                "title" => $this->object->getTitle(),
                "description" => $this->object->getDescription(),
                "scorm_ref_id" => $this->object->getScormRefId(),
                "target_role_id" => $this->object->getTargetRoleId(),
            )
        );
    }

    /**
     *
     */
    protected function saveProperties()
    {
        $form = $this->initPropertiesForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->fillObject($this->object, $form);
            $this->object->update();
            ilUtil::sendSuccess($this->plugin->txt("update_successful"), true);
            $this->ctrl->redirect($this, "editProperties");
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param $object ilObjUFreibFeedback
     * @param $form   ilPropertyFormGUI
     */
    private function fillObject($object, $form)
    {
        $object->setTitle($form->getInput('title'));
        $object->setDescription($form->getInput('description'));
        $object->setScormRefId($form->getInput('scorm_ref_id'));
        $object->setTargetRoleId($form->getInput('target_role_id'));
    }

    protected function showFeedbacks()
    {
        $this->tabs->activateTab("feedbacks");

        $this->plugin->includeClass("class.ilUFreibFeedbackTableGUI.php");
        $table_gui = new ilUFreibFeedbackTableGUI($this, "showFeedbacks",
            $this->object->getScormRefId(), $this->plugin);

        $this->tpl->setContent($table_gui->getHTML());
    }


    /**
     * We need this method if we can't access the tabs otherwise...
     */
    private function activateTab($active)
    {
         $this->tabs->activateTab($active);
    }

    public function showFeedbackForm()
    {
        $form = $this->initFeedbackForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init feedback form
     */
    public function initFeedbackForm()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // recipient
        $ne = new ilNonEditableValueGUI($this->plugin->txt("recipient"), "recipient");
        $ne->setValue(ilObjUser::_lookupLogin((int) $_GET["recipient"]));
        $form->addItem($ne);

        // text input
        $ti = new ilTextInputGUI($this->plugin->txt("subject"), "subject");
        $form->addItem($ti);

        // message
        $ta = new ilTextAreaInputGUI($this->plugin->txt("message"), "message");
        $form->addItem($ta);

        // save and cancel commands
        $form->addCommandButton("sendMessage", $lng->txt("send"));
        $form->addCommandButton("cancelSave", $lng->txt("cancel"));

        $form->setTitle($this->plugin->txt("feedback"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function sendMessage()
    {
        $ctrl = $this->ctrl;
        $message = (string) $_POST['message'];

        $mailBody = new ilMailBody($message, $this->purifier);

        $sanitizedMessage = $mailBody->getContent();

        $mailer = $this->umail
            ->withContextId('')
            ->withContextParameters([]);

        $mailer->setSaveInSentbox(true);

        $errors = $mailer->enqueue(
            ilUtil::securePlainString(ilObjUser::_lookupLogin((int) $_GET["recipient"])),
            "",
            "",
            ilUtil::securePlainString($_POST['subject']),
            $sanitizedMessage,
            false,
            null
        );
        if (!$errors) {
            $mailer->savePostData($this->user->getId(), array(), "", "", "", "", "", "", "", "");

            $this->plugin->includeClass("class.ilUFreibFeedbackRepo.php");
            $feedback_repo = new ilUFreibFeedbackRepo();
            $feedback_repo->saveFeedback($this->object->getScormRefId(), (int) $_GET["recipient"]);
        }
        $ctrl->redirect($this, "showFeedbacks");
    }

}