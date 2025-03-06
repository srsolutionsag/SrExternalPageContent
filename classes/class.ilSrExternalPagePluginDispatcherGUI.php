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
use srag\Plugins\SrExternalPageContent\Helper\Hasher;
use ILIAS\Data\URI;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPagePluginDispatcherGUI: ilSrExternalPageContentConfigGUI
 * @ilCtrl_isCalledBy ilSrExternalPagePluginDispatcherGUI: ilUIPluginRouterGUI
 */
class ilSrExternalPagePluginDispatcherGUI extends BaseGUI
{
    use Hasher;

    public const SOURCE_ADMINISTRATION = 'admin';
    public const SOURCE_REPO = 'repo';
    public const FALLBACK = 'fb';

    protected const TAB_WHITELIST = 'whitelist';
    protected const TAB_SETTINGS = 'settings';
    protected const TAB_MIGRATRATION = 'migration';
    private array $classes = [
        ilSEPCWhitelistConfigGUI::class => self::TAB_WHITELIST,
        ilSEPCSettingsGUI::class => self::TAB_SETTINGS,
        ilSEPCMigrationGUI::class => self::TAB_MIGRATRATION,
    ];
    protected ?string $fallback_uri = null;

    public function checkAccess(): void
    {
        if (!$this->access_checks->isUserLoggedIn()()) {
            throw new ilException('Access Denied');
        }
    }

    public function executeCommand(): void
    {
        $this->checkAccess();

        // Store Fallback URI if available
        if ($this->dic->ilias()->http()->wrapper()->query()->has(self::FALLBACK)) {
            $fallback_uri = $this->unhash(
                $this->dic->ilias()->http()->wrapper()->query()->retrieve(
                    self::FALLBACK,
                    $this->dic->ilias()->refinery()->kindlyTo()->string()
                )
            );
            try {
                $fallback_uri = new URI($fallback_uri);
                $this->fallback_uri = $fallback_uri->getPath() . '?' . $fallback_uri->getQuery();
            } catch (Throwable $e) {
                $this->fallback_uri = null;
            }

            $this->ctrl->saveParameter($this, self::FALLBACK);
        }

        $this->initTabs();

        $next_class = $this->ctrl->getNextClass();
        foreach ($this->classes as $class => $tab) {
            if (strtolower($class) === $next_class) {
                $this->tabs->activateTab($tab);
                $this->ctrl->forwardCommand(new $class($this->fallback_uri));
            }
        }

        // check fo calls from ilUIPluginRouterGUI
        foreach ($this->ctrl->getCallHistory() as $item) {
            if (($item['cmdClass'] ?? '') === ilUIPluginRouterGUI::class) {
                $this->tpl->setTitle($this->translator->txt('external_contents'));
                $this->tpl->loadStandardTemplate();
                $this->tpl->printToStdout();
                return;
            }
        }
    }

    private function initTabs(): void
    {
        if ($this->fallback_uri !== null) {
            $this->tabs->setBackTarget(
                $this->translator->txt('tab_' . self::FALLBACK),
                $this->fallback_uri
            );

            $next_class = $this->ctrl->getNextClass();
            $tab = null;
            foreach ($this->classes as $class => $tab) {
                $instance = new $class();
                $instance->saveParameters();
                if (strtolower($class) === $next_class) {
                    $this->tabs->addTab(
                        $tab,
                        $this->translator->txt('tab_' . $tab),
                        $this->ctrl->getLinkTargetByClass($class)
                    );
                    $this->tabs->activateTab($tab);
                    break; // stop after this tab, we only want one tab beside the back tab
                }
            }

            return;
        }

        // otherwise we show all tabs
        foreach ($this->classes as $class => $tab) {
            $this->tabs->addTab(
                $tab,
                $this->translator->txt('tab_' . $tab),
                $this->ctrl->getLinkTargetByClass($class)
            );
        }
    }
}
