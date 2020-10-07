<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjUFreibFeedbackListGUI extends ilObjectPluginListGUI
{

    /**
     * Init type
     */
    function initType()
    {
        $this->setType(ilUFreibFeedbackPlugin::ID);
    }

    /**
     * Get name of gui class handling the commands
     */
    function getGuiClass()
    {
        return "ilObjUFreibFeedbackGUI";
    }

    /**
     * Get commands
     */
    function initCommands()
    {
        return array
        (
            array(
                "permission" => "write",
                "cmd" => "showFeedbacks",
                "default" => true
            ),
            array(
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->txt("edit"),
                "default" => false
            )
        );
    }

    /**
     * Get item properties
     * @return        array                array of property arrays:
     *                                "alert" (boolean) => display as an alert property (usually in red)
     *                                "property" (string) => property name
     *                                "value" (string) => property value
     */
    function getProperties()
    {
        global $lng, $ilUser;

        $props = array();

        $this->plugin->includeClass("class.ilObjUFreibFeedbackAccess.php");

        return $props;
    }
}