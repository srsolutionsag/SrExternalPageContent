<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Data\Factory;
use srag\Plugins\SrExternalPageContent\Init;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\GlobalScreen\Menu;
use srag\Plugins\SrExternalPageContent\GlobalScreen\Tool;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\CLI\ObjectiveHelper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentPlugin extends ilPageComponentPlugin
{
    use ObjectiveHelper;

    public const PLUGIN_NAME = "SrExternalPageContent";
    private DIC $dic;
    private array $_run = [];

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

    private function achieve(Objective $c): void
    {
        static $inside_run; // we prevent recursion here
        if ($inside_run === true) {
            return;
        }
        $inside_run = true;
        $environment = new ArrayEnvironment([]);
        $this->achieveObjective($c, $environment);
    }

    protected function afterUpdate(): void
    {
        // Installing / Updating the plugin using the CLI works as expected. But while installing via GUI, we must
        // perform the custom objectives update steps manually.
        if (PHP_SAPI === 'cli') {
            return;
        }

        $agent = new ilSrExternalPageContentAgent(
            $this->dic->ilias()->refinery(),
            new Factory(),
            $this->dic->ilias()->language()
        );

        $this->achieve($agent->getUpdateObjective());
    }

    protected function afterUninstall(): void
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */
        $db = $sepcContainer->ilias()->database();
        $db->dropTable("sr_epc_whitelist", false);
        $db->dropTable("sr_epc_content", false);
        $db->dropTable("sr_epc_settings", false);

        $db->manipulateF(
            "DELETE FROM il_db_steps WHERE class = %s",
            ["text"],
            [ilSrExternalPageContentDBUpdateSteps::class]
        );
    }

}
