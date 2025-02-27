<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 */
class ilSrExternalPageContentPluginStakeholder extends AbstractResourceStakeholder
{
    public function __construct()
    {
    }

    public function getId(): string
    {
        return ilSrExternalPageContentPlugin::PLUGIN_NAME;
    }

    public function getOwnerOfNewResources(): int
    {
        return 6;
    }

}
