<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\GlobalScreen;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;
use srag\Plugins\SrExternalPageContent\DIC;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Menu extends AbstractStaticMainMenuPluginProvider
{
    public function getStaticTopItems(): array
    {
        return [

        ];
    }

    public function getStaticSubItems(): array
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */

        $admin = StandardTopItemsProvider::getInstance()->getAdministrationIdentification();
        $standard_access = BasicAccessCheckClosuresSingleton::getInstance();

        return [
            $this->mainmenu
                ->link(
                    $this->if->identifier('sr_epc_whitelist'),
                )
                ->withTitle($sepcContainer->translator()->txt('external_contents'))
                ->withAction(
                    $this->dic->ctrl()->getLinkTargetByClass([
                        \ilUIPluginRouterGUI::class,
                        \ilSrExternalPagePluginDispatcherGUI::class,
                        \ilSEPCWhitelistConfigGUI::class
                    ])
                )
                ->withParent($admin)
                ->withVisibilityCallable(
                    $standard_access->hasAdministrationAccess()
                )
        ];
    }

}
