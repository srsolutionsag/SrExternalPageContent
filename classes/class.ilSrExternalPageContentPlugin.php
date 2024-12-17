<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\Init;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\GlobalScreen\Menu;
use srag\Plugins\SrExternalPageContent\GlobalScreen\Tool;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentPlugin extends ilPageComponentPlugin
{
    public const PLUGIN_NAME = "SrExternalPageContent";
    private DIC $dic;

    public function __construct(ilDBInterface $db, ilComponentRepositoryWrite $component_repository, string $id)
    {
        parent::__construct($db, $component_repository, $id);
        global $sepcContainer, $DIC;
        $sepcContainer = Init::init($this, $this->getLanguageHandler());
        $this->dic = $sepcContainer;

        if ($DIC->isDependencyAvailable('globalScreen')) {
            $this->provider_collection->setMainBarProvider(new Menu($DIC, $this));
            $this->provider_collection->setToolProvider(new Tool($DIC, $this, $sepcContainer));
        }
    }

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function isValidParentType($a_type): bool
    {
        $roles = $this->dic->settings()->get('roles', [2, 4]);
        return $this->dic->ilias()->rbac()->review()->isAssignedToAtLeastOneGivenRole(
            $this->dic->ilias()->user()->getId(),
            $roles
        );
    }

    public function txt(string $a_var): string
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */
        return $sepcContainer->translator()->txt($a_var);
    }

    protected function afterUninstall(): void
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */
        $sepcContainer->ilias()->database()->dropTable("sr_epc_whitelist", false);
        $sepcContainer->ilias()->database()->dropTable("sr_epc_content", false);
    }

}
