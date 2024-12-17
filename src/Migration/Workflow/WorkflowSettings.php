<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Workflow;

use srag\Plugins\SrExternalPageContent\Migration\Preview\PreviewSettings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WorkflowSettings
{
    private ?PreviewSettings $preview_settings = null;
    private bool $dry_run = true;

    public function __construct(
        bool $dry_run = true,
        ?PreviewSettings $preview_settings = null
    ) {
        $this->dry_run = $dry_run;
        $this->preview_settings = $preview_settings ?? new PreviewSettings('%s', "\n");
    }

    public function isDryRun(): bool
    {
        return $this->dry_run;
    }

    public function isInvasive(): bool
    {
        return !$this->dry_run;
    }

    public function getPreviewSettings(): PreviewSettings
    {
        return $this->preview_settings;
    }

}
