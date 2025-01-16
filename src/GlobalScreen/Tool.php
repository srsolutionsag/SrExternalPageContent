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

use srag\Plugins\SrExternalPageContent\DIC;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use srag\Plugins\SrExternalPageContent\Helper\Hasher;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Tool extends AbstractDynamicToolPluginProvider
{
    use Hasher;

    protected PluginIdentificationProvider $if;

    private array $supported_types_full_migration = [
        'lm'
    ];

    private array $supported_types_single_migration = [
        'lm',
        'crs',
        'root',
        'grp',
        'cat',
        'fold'
    ];

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        if (!$called_contexts->current()->hasReferenceId()) {
            return [];
        }
        global $sepcContainer;
        /** @var DIC $sepcContainer */

        $standard_access = BasicAccessCheckClosuresSingleton::getInstance();
        $editor_shown = $called_contexts->current()->getAdditionalData()->is('copg_show_editor', true);
        $ref_id = $called_contexts->current()->getReferenceId();

        // check write access toi current object
        if (!$this->dic->access()->checkAccess('write', '', $ref_id->toInt())) {
            return [];
        }

        $show_tool = false;

        // check type
        $supported_types = [
            'lm'
        ];
        $object_id = $ref_id->toObjectId()->toInt();
        $type = \ilObject2::_lookupType($object_id);

        // if we are on a single page using the page editor, we maybe show the single tool
        if ($editor_shown) {
            $page_id = (int) ($this->dic->http()->request()->getQueryParams()['obj_id'] ?? 0);
            if ($page_id === 0) {
                // try single pages per object_iud
                $pages = $sepcContainer->pageRepo()->getByObjId($object_id);
                if (count($pages) === 0 || count($pages) > 1) { // if we have exactly one page, we use this
                    return [];
                }
                $page_id = $pages[0]->getPageId();
            }

            if (in_array($type, $this->supported_types_single_migration, true)) {
                return [$this->getSingleTool($sepcContainer, $page_id)]; // show single tool
            }
            return []; // show no tool
        }

        // if we are in a objects which supports the multi migration (but editor not active), we maybe show the muslti tool
        if (in_array($type, $this->supported_types_full_migration, true)) {
            // return [$this->getMultiTool($sepcContainer)]; TODO: implement multi tool
        }

        return [];
    }

    private function prepareLinkBuilder(string $mode, int $id): void
    {
        $this->dic->ctrl()->setParameterByClass(
            \ilSrExternalPagePluginDispatcherGUI::class,
            \ilSrExternalPagePluginDispatcherGUI::FALLBACK,
            $this->hash((string) $this->dic->http()->request()->getUri())
        );

        $this->dic->ctrl()->setParameterByClass(
            \ilSEPCMigrationGUI::class,
            \ilSEPCMigrationGUI::P_MODE,
            $mode
        );

        $this->dic->ctrl()->setParameterByClass(
            \ilSEPCMigrationGUI::class,
            \ilSEPCMigrationGUI::P_ID,
            $id
        );
    }

    protected function getSingleTool(DIC $c, int $page_id): \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool
    {
        $migratable_contents = $c->pageRepo()->countMigratableContents($page_id);

        $this->prepareLinkBuilder(\ilSEPCMigrationGUI::MODE_SINGLE, $page_id);

        $contents = [
            $this->dic->ui()->factory()->messageBox()->info(
                $c->translator()->sprintf('migration_info_iframes', [$migratable_contents])
            ),
            $this->dic->ui()->factory()->button()->bulky(
                $this->dic->ui()->factory()->symbol()->icon()->standard('nu', 'nu', 'small')->withAbbreviation('>'),
                $c->translator()->txt('migration_start'),
                $this->dic->ctrl()->getLinkTargetByClass(
                    [
                        \ilUIPluginRouterGUI::class,
                        \ilSrExternalPagePluginDispatcherGUI::class,
                        \ilSEPCMigrationGUI::class
                    ]
                )
            )
        ];

        return $this->buildTool(
            $c,
            $this->if->identifier('migration_tool_single'),
            $contents
        );
    }

    protected function getMultiTool(DIC $sepcContainer): \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool
    {
        return $this->factory
            ->tool(
                $this->if->identifier('migration_tool_multi')
            )
            ->withTitle($sepcContainer->translator()->txt('migration_tool'))
            ->withContentWrapper(
                fn(): Legacy => $this->dic->ui()->factory()->legacy(
                    'THE MULTI TOOL'
                )
            );
    }

    private function buildTool(
        DIC $c,
        IdentificationInterface $i,
        array $contents
    ): \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool {
        return $this->factory
            ->tool($i)
            ->withTitle($c->translator()->txt('migration_tool'))
            ->withPosition(0)
            ->withContentWrapper(
                fn(): Legacy => $this->dic->ui()->factory()->legacy(
                    $this->dic->ui()->renderer()->render($contents)
                )
            );
    }

}
