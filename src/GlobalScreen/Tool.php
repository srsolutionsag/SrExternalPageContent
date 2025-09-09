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
        'fold',
        'copa',
        'cont',
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

        $settings = $sepcContainer->settings();
        if ($settings->get('show_tool', true) === false) {
            return [];
        }

        $editor_shown = $called_contexts->current()->getAdditionalData()->is('copg_show_editor', true);
        $ref_id = $called_contexts->current()->getReferenceId();

        // check write access toi current object
        if (!$this->dic->access()->checkAccess('write', '', $ref_id->toInt())) {
            return [];
        }
        $object_id = $ref_id->toObjectId()->toInt();
        $parent_type = \ilObject2::_lookupType($object_id);

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
                $parent_type = $pages[0]->getParentType();
            }

            if (
                in_array($parent_type, $this->supported_types_single_migration, true)
                && $sepcContainer->pageRepo()->countMigratableContents($page_id, $parent_type) > 0) {
                return [
                    $this->getSingleTool(
                        $sepcContainer,
                        $page_id,
                        $ref_id->toInt(),
                        $parent_type
                    )
                ]; // show single tool
            }
            return []; // show no tool
        }

        // if we are in a objects which supports the multi migration (but editor not active), we maybe show the muslti tool
        if (in_array($parent_type, $this->supported_types_full_migration, true) && $this->maybeHasMigratableContents(
            $sepcContainer,
            $object_id
        )) {
            return [$this->getMultiTool($sepcContainer, $object_id, $ref_id->toInt(), $parent_type)];
        }

        return [];
    }

    private function maybeHasMigratableContents(DIC $c, int $object_id): bool
    {
        // check if we have migratable contents
        return $c->pageRepo()->countPossiblePagesWithIframes($object_id) > 0;
    }

    private function prepareLinkBuilder(string $mode, int $id, int $ref_id, string $parent_type): void
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
        $this->dic->ctrl()->setParameterByClass(
            \ilSEPCMigrationGUI::class,
            \ilSEPCMigrationGUI::P_PTYPE,
            $parent_type
        );

        $this->dic->ctrl()->setParameterByClass(
            \ilSEPCMigrationGUI::class,
            \ilSEPCMigrationGUI::P_R_REF_ID,
            $ref_id
        );
    }

    protected function getSingleTool(
        DIC $c,
        int $page_id,
        int $ref_id,
        string $parent_type
    ): ?\ILIAS\GlobalScreen\Scope\Tool\Factory\Tool {
        $migratable_contents = $c->pageRepo()->countMigratableContents($page_id, $parent_type);

        $this->prepareLinkBuilder(\ilSEPCMigrationGUI::MODE_SINGLE, $page_id, $ref_id, $parent_type);

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

    protected function getMultiTool(
        DIC $c,
        int $object_id,
        int $ref_id,
        string $parent_type
    ): \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool {
        $this->prepareLinkBuilder(\ilSEPCMigrationGUI::MODE_MULTI, $object_id, $ref_id, $parent_type);

        $contents = [
            $this->dic->ui()->factory()->messageBox()->info(
                $c->translator()->txt('migration_info_iframes_object')
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
