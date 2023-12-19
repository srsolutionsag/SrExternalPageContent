<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\BaseGUI;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPagePluginDispatcherGUI: ilSrExternalPageContentConfigGUI
 */
class ilSrExternalPagePluginDispatcherGUI extends BaseGUI
{
    protected const TAB_WHITELIST = 'whitelist';
    protected const TAB_SETTINGS = 'settings';
    private array $classes = [
        ilSEPCWhitelistConfigGUI::class => self::TAB_WHITELIST,
        ilSEPCSettingsGUI::class => self::TAB_SETTINGS,
    ];

    public function executeCommand(): void
    {
        $this->initTabs();

        $next_class = $this->ctrl->getNextClass();
        foreach ($this->classes as $class => $tab) {
            if (strtolower($class) === $next_class) {
                $this->tabs->activateTab($tab);
                $this->ctrl->forwardCommand(new $class());
                return;
            }
        }
    }

    private function initTabs(): void
    {
        foreach ($this->classes as $class => $tab) {
            $this->tabs->addTab(
                $tab,
                $this->translator->txt('tab_' . $tab),
                $this->ctrl->getLinkTargetByClass($class)
            );
        }
    }
}
