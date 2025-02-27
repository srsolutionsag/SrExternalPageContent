<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepositoryDB;

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

    public function step_3(): void
    {
        if ($this->db->tableColumnExists('sr_epc_content', 'dimensions')) {
            return;
        }
        // introduce new columns for size settings
        $this->db->addTableColumn('sr_epc_content', 'dimensions', [
            'type' => 'clob',
            'notnull' => false
        ]);

        $this->db->manipulate('UPDATE sr_epc_content SET dimensions = \'[]\'');
    }

    public function step_4(): void
    {
        $repo = new EmbeddableRepositoryDB($this->db);
        $dimensions = new DimensionBuilder();
        foreach ($repo->all() as $embeddable) {
            try {
                $embeddable->setDimension($dimensions->fromLegacyProperties($embeddable));
            } catch (Throwable $e) {
                $embeddable->setDimension($dimensions->default());
            }
            $repo->store($embeddable);
        }
    }
}
