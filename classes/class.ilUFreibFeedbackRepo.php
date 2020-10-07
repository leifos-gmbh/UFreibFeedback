<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUFreibFeedbackRepo
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
    }

    public function getScormFeedbackUsers($scorm_ref_id) {
        // get all users being tracked by the scorm object
        $tr_data = ilTrQuery::getUserDataForObject(
            $scorm_ref_id,
            "lastname",
            "",
            0,
            9999,
            null,
                  ["lastname", "firstname", "status"],
            false,
            []
        );
        return $tr_data["set"];
    }
    
    public function saveFeedback($scorm_ref_id, $recipient_id) {

        $db = $this->db;

        $set = $db->queryF(
            "SELECT MAX(mail_id) as max_mail_id FROM mail " .
            " WHERE user_id = %s GROUP BY mail_id",
            ["integer"],
            [$this->user->getId()]
        );
        $rec = $db->fetchAssoc($set);
        $mail_id = $rec["max_mail_id"];

        $db->insert(
            "rep_robj_xfrf_feedback",
            [
                "user_id" => ["integer", $this->user->getId()],
                "mail_id" => ["integer", $mail_id],
                "scorm_ref_id" => ["integer", $scorm_ref_id],
                "recipient_id" => ["integer", $recipient_id]
            ]
        );
    }
}