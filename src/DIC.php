<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent;

use Pimple\Container;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepository;
use srag\Plugins\SrExternalPageContent\Helper\Refinery;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Renderer\RendererFactory;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Settings\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DIC extends Container
{
    public function translator(): Translator
    {
        return $this[Translator::class];
    }

    public function plugin(): \ilSrExternalPageContentPlugin
    {
        return $this[\ilSrExternalPageContentPlugin::class];
    }

    public function ilias(): \ILIAS\DI\Container
    {
        return $this[\ILIAS\DI\Container::class];
    }

    public function whitelist(): WhitelistRepository
    {
        return $this[WhitelistRepository::class];
    }

    public function embeddables(): EmbeddableRepository
    {
        return $this[EmbeddableRepository::class];
    }

    public function refinery(): Refinery
    {
        return $this[Refinery::class];
    }

    public function renderer(): RendererFactory
    {
        return $this[RendererFactory::class];
    }

    public function check(): Check
    {
        return $this[Check::class];
    }

    public function settings(): Settings
    {
        return $this[Settings::class];
    }

}
