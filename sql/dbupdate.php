<#1>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'scorm_ref_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false
    ),
    'target_role_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false
    )
);
if(!$ilDB->tableExists("rep_robj_xfrf_data")) {
    $ilDB->createTable("rep_robj_xfrf_data", $fields);
    $ilDB->addPrimaryKey("rep_robj_xfrf_data", array("id"));
}
?>
<#2>
<?php
$fields = array(
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'mail_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false
    )
);
if(!$ilDB->tableExists("rep_robj_xfrf_feedback")) {
    $ilDB->createTable("rep_robj_xfrf_feedback", $fields);
    $ilDB->addPrimaryKey("rep_robj_xfrf_feedback", array("user_id", "mail_id"));
}
?>
<#3>
<?php
if (!$ilDB->tableColumnExists('rep_robj_xfrf_feedback', 'scorm_ref_id')) {
    $ilDB->addTableColumn('rep_robj_xfrf_feedback', 'scorm_ref_id', array(
        'type' => 'integer',
        'notnull' => false,
        'length' => 4,
        'default' => 0
    ));
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('rep_robj_xfrf_feedback', 'recipient_id')) {
    $ilDB->addTableColumn('rep_robj_xfrf_feedback', 'recipient_id', array(
        'type' => 'integer',
        'notnull' => false,
        'length' => 4,
        'default' => 0
    ));
}
?>
<#5>
<?php
if (!$ilDB->tableColumnExists('rep_robj_xfrf_feedback', 'recipient_mail_id')) {
    $ilDB->addTableColumn('rep_robj_xfrf_feedback', 'recipient_mail_id', array(
        'type' => 'integer',
        'notnull' => false,
        'length' => 4,
        'default' => 0
    ));
}
?>
<#6>
<?php
    $ilDB->renameTableColumn('rep_robj_xfrf_feedback', "scorm_ref_id", 'feedb_ref_id');
?>

