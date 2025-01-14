<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('sr_epc_content', 'thumb_rid')) {
            return;
        }

        // create table column
        $this->db->addTableColumn('sr_epc_content', 'thumb_rid', [
            'type' => 'text',
            'length' => 64,
            'notnull' => false
        ]);
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('sr_epc_whitelist', 'auto_consent')) {
            return;
        }

        // create table column
        $this->db->addTableColumn('sr_epc_whitelist', 'auto_consent', [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]);
    }
}
