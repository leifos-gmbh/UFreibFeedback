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
    
    public function saveFeedback($feedb_ref_id, $recipient_id) {

        $db = $this->db;

        $set = $db->queryF(
            "SELECT MAX(mail_id) as max_mail_id FROM mail " .
            " WHERE user_id = %s",
            ["integer"],
            [$this->user->getId()]
        );
        $rec = $db->fetchAssoc($set);
        $mail_id = $rec["max_mail_id"];

        $set = $db->queryF(
            "SELECT MAX(mail_id) as max_mail_id FROM mail " .
            " WHERE user_id = %s",
            ["integer"],
            [$recipient_id]
        );
        $rec = $db->fetchAssoc($set);
        $recipient_mail_id = $rec["max_mail_id"];

        $db->insert(
            "rep_robj_xfrf_feedback",
            [
                "user_id" => ["integer", $this->user->getId()],
                "mail_id" => ["integer", $mail_id],
                "feedb_ref_id" => ["integer", $feedb_ref_id],
                "recipient_id" => ["integer", $recipient_id],
                "recipient_mail_id" => ["integer", $recipient_mail_id]
            ]
        );
    }
    
    public function getTriggerFeedbackRefIdsForRecipientMail($recipient_id, $recipient_mail_id) {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM rep_robj_xfrf_feedback " .
            " WHERE recipient_id = %s AND recipient_mail_id = %s",
            ["integer", "integer"],
            [$recipient_id, $recipient_mail_id]
        );
        $feedb_ref_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $feedb_ref_ids[$rec["feedb_ref_id"]] = $rec["feedb_ref_id"];
        }
        return $feedb_ref_ids;
    }

    public function getFeedbacksForUser($recipient_id, $feedb_ref_id) {
        $db = $this->db;

        $feedbacks = [];

        $set = $db->queryF(
            "SELECT * FROM rep_robj_xfrf_feedback " .
            " WHERE recipient_id = %s ".
            " AND feedb_ref_id = %s ",
            ["integer", "integer"],
            [$recipient_id, $feedb_ref_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $set2 = $db->queryF(
                "SELECT * FROM mail " .
                " WHERE mail_id = %s ",
                ["integer"],
                [$rec["mail_id"]]
            );
            $rec2 = $db->fetchAssoc($set2);

            $feedbacks[] = [
                "sender_id" => $rec["user_id"],
                "mail_id" => $rec2["mail_id"],
                "send_time" => $rec2["send_time"],
                "sender_name" => ilObjUser::_lookupFullname($rec["user_id"])
            ];
        }
        return $feedbacks;
    }
}