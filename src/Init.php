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

use ILIAS\DI\Container;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepository;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepositoryDB;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Whitelist\DomainParser;
use srag\Plugins\SrExternalPageContent\Helper\Refinery;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepositoryDB;
use srag\Plugins\SrExternalPageContent\Renderer\RendererFactory;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepositoryWapper;
use srag\Plugins\SrExternalPageContent\Settings\SettingsRepository;
use srag\Plugins\SrExternalPageContent\Settings\SettingsRepositoryDB;
use srag\Plugins\SrExternalPageContent\Settings\Settings;
use srag\Plugins\SrExternalPageContent\Content\URLTranslator;
use srag\Plugins\SrExternalPageContent\Migration\Page\PageRepository;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Init
{
    /**
     * @var DIC|null
     */
    private static ?DIC $container = null;

    public static function init(\ilSrExternalPageContentPlugin $plugin, \ilPluginLanguage $language_handler): DIC
    {
        if (self::$container instanceof DIC) {
            return self::$container;
        }
        global $DIC;

        $container = new DIC();
        $container[Container::class] = static fn(): Container => $DIC;
        $container[Refinery::class] = static fn(): Refinery => new Refinery($DIC->refinery());
        $container[\ilSrExternalPageContentPlugin::class] = static fn(): \ilSrExternalPageContentPlugin => $plugin;
        $container[Translator::class] = static fn(): Translator => new Translator($language_handler);
        $container[ParserFactory::class] = static fn(): ParserFactory => new ParserFactory($container[DimensionBuilder::class]);
        $container[WhitelistRepository::class] = static fn(): WhitelistRepository => new WhitelistRepositoryDB(
            $DIC->database()
        );
        $container[DomainParser::class] = static fn (): DomainParser => new DomainParser();
        $container[Check::class] = static fn (): Check => new Check(
            $container[WhitelistRepository::class],
            $container[DomainParser::class]
        );
        $container[URLTranslator::class] = static fn (): URLTranslator => new URLTranslator();
        $container[EmbeddableRepository::class] = static fn (): EmbeddableRepository => new EmbeddableRepositoryWapper(
            new EmbeddableRepositoryDB(
                $DIC->database(),
                $container[DimensionBuilder::class]
            ),
            $container[Check::class],
            $container[URLTranslator::class],
        );
        $container[Settings::class] = static fn(): Settings => new Settings(
            $container[SettingsRepository::class]
        );
        $container[DimensionBuilder::class] = static fn(): DimensionBuilder => new DimensionBuilder($container[Settings::class]);
        $container[RendererFactory::class] = static fn(): RendererFactory => new RendererFactory(
            $container[Check::class],
            $container[Translator::class],
            $container[Settings::class],
            $container[DimensionBuilder::class]
        );
        $container[SettingsRepository::class] = static fn (): SettingsRepository => new SettingsRepositoryDB(
            $DIC->database()
        );

        $container[PageRepository::class] = static fn(): PageRepository => new PageRepository(
            $DIC->database()
        );

        return self::$container = $container;
    }
}
