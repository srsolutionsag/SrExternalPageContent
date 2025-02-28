<#1>
<?php
// create table for whitelist

/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists('sr_epc_whitelist')) {
    $ilDB->createTable('sr_epc_whitelist', [
        'id' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
        'status' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
        'domain' => ['type' => 'text', 'length' => 2048, 'notnull' => true],
        'title' => ['type' => 'text', 'length' => 1024, 'notnull' => false],
        'description' => ['type' => 'text', 'length' => 1024, 'notnull' => false],
    ]);
    $ilDB->addPrimaryKey('sr_epc_whitelist', ['id']);
    $ilDB->createSequence('sr_epc_whitelist');
    $ilDB->addIndex('sr_epc_whitelist', ['domain'], 'i1');
}

?>
<#2>
<?php
// create table for embedded content

/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists('sr_epc_content')) {
    $ilDB->createTable('sr_epc_content', [
        'id' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
        'type' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
        'status' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
        'url' => ['type' => 'text', 'length' => 2048, 'notnull' => true],
        'properties' => ['type' => 'clob', 'notnull' => false],
        'scripts' => ['type' => 'clob', 'notnull' => false]
    ]);
    $ilDB->addPrimaryKey('sr_epc_content', ['id']);
    $ilDB->createSequence('sr_epc_content');
}

?>
<#3>
<?php
// create table for settings

/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists('sr_epc_settings')) {
    $ilDB->createTable('sr_epc_settings', [
        'keyword' => ['type' => 'text', 'length' => 1024, 'notnull' => true],
        'value' => ['type' => 'clob', 'notnull' => false]
    ]);
    $ilDB->addPrimaryKey('sr_epc_settings', ['keyword']);
}

?>
<#4>
<?php
// we switch to text for the id column since we want to use UUIDs

/** @var $ilDB \ilDBInterface */

$ilDB->modifyTableColumn('sr_epc_content', 'id', [
    'type' => 'text',
    'length' => 64
]);
if ($ilDB->sequenceExists('sr_epc_content')) {
    $ilDB->dropSequence('sr_epc_content');
}

// ********************************************************************
// NO MORE STEPS AFTER HERE
// Please use the Setup/Objective mechanism to add more Database Steps
// ********************************************************************
?>
