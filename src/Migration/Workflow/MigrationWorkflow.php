<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Migration\Workflow;

use srag\Plugins\SrExternalPageContent\Migration\Page\Page;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface MigrationWorkflow
{
    public function start(?int $after = null): void;

    /**
     * @return \Generator|Page[]
     */
    public function run(): \Generator;

    public function getLast(): ?Page;

    public function mayHaveNext(): bool;
}
