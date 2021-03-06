<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUFreibScormRoleRepo
{

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->db = $DIC->database();
    }

    /**
     * Get roles for scorm ref id
     * @param
     * @return
     */
    public function getRolesForScormRefId($scorm_ref_id)
    {
        $tree = $this->tree;
        $rbacreview = $this->rbacreview;

        $roles = ["" => "---"];

        if ($scorm_ref_id <= 0) {
            return $roles;
        }

        $path = $tree->getPathFull($scorm_ref_id);
        foreach ($path as $item) {
            if ($item["type"] == "crs") {
                $crs_ref_id = $item["child"];
            }
        }

        if ($crs_ref_id > 0) {
            $scorms = $tree->getSubTree($tree->getNodeData($crs_ref_id),true, "sahs");
            $scorms = array_filter(
                $scorms,
                function ($i) use ($scorm_ref_id) {
                    return $i["child"] != $scorm_ref_id;
                }
            );

            foreach ($scorms as $s) {
                foreach ($rbacreview->getLocalRoles($s["child"]) as $role_id) {
                    $obj_title = ilObject::_lookupTitle(ilObject::_lookupObjId($s["child"]));
                    $role = new ilObjRole($role_id);
                    $roles[$role_id] = $obj_title." - ".$role->getPresentationTitle();
                }
            }
        }
        return $roles;
    }

    public function assignUserToFollowUpRole($user_id, $feedb_ref_id) {
        $db = $this->db;

        $feedb_obj_id = ilObject::_lookupObjId($feedb_ref_id);

        $set = $db->queryF(
            "SELECT target_role_id FROM rep_robj_xfrf_data " .
            " WHERE id = %s ",
            ["integer"],
            [$feedb_obj_id]
        );
        $rec = $db->fetchAssoc($set);
        if ($rec["target_role_id"] > 0) {
            $this->rbacadmin->assignUser($rec["target_role_id"], $user_id);
        }
    }

}