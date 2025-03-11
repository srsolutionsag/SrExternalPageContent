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
use srag\Plugins\SrExternalPageContent\Migration\Preview\PreviewSettings;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\WorkflowSettings;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\PageByPageWorkflow;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Migration\Page\PageRepository;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Migration\Preview\Preview;
use srag\Plugins\SrExternalPageContent\Migration\Page\SinglePageProvider;
use srag\Plugins\SrExternalPageContent\Migration\Page\ObjectPagesProvider;
use srag\Plugins\SrExternalPageContent\Migration\Page\PageProvider;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\MigrationWorkflow;
use srag\Plugins\SrExternalPageContent\Migration\Page\AllPagesProvider;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSEPCMigrationGUI: ilSrExternalPagePluginDispatcherGUI
 */
class ilSEPCMigrationGUI extends BaseGUI
{
    public const P_MODE = 'mode';
    public const MODE_SINGLE = 'single';
    public const MODE_MULTI = 'multi';
    public const P_ID = 'wid';
    public const P_LAST_WID = 'last_wid';
    private PreviewSettings $preview_settings;
    private ?string $mode = null;
    private PageRepository $page_repository;

    public function __construct(?string $fallback_uri = null)
    {
        parent::__construct($fallback_uri);
        $this->preview_settings = new PreviewSettings();
        $this->page_repository = $this->dic[PageRepository::class];
    }

    public function checkAccess(): void
    {
        if (!$this->access_checks->hasAdministrationAccess()()) {
            throw new ilException('Access Denied');
        }
    }

    public function saveParameters(): void
    {
        $this->ctrl->saveParameter($this, self::P_MODE);
        $this->ctrl->saveParameter($this, self::P_ID);
        $this->ctrl->saveParameter($this, self::P_LAST_WID);
    }

    public function executeCommand(): void
    {
        $this->saveParameters();
        $this->mode = $this->http_wrapper->query()->has(self::P_MODE)
            ? $this->http_wrapper->query()->retrieve(
                self::P_MODE,
                $this->dic->ilias()->refinery()->kindlyTo()->string()
            )
            : null;
        $this->performStandardCommands();
    }

    protected function index(): void
    {
        // Add some StyleSheets needed by Page
        $this->tpl->addCss(\ilUtil::getStyleSheetLocation());
        $this->tpl->addCss(\ilObjStyleSheet::getContentStylePath(0));

        $workflow = $this->buildWorkflow(
            new WorkflowSettings(
                true,
                $this->preview_settings
            )
        );

        $runner = $workflow->run();
        $current_page = $runner->current();
        if ($current_page === null) {
            if ($this->fallback_uri) {
                $this->tpl->setOnScreenMessage(
                    'success',
                    $this->translator->txt('migration_success'),
                    true
                );
                $this->ctrl->redirectToURL(
                    $this->fallback_uri
                );
                return;
            }
            $this->tpl->setOnScreenMessage(
                'info',
                $this->translator->txt('migration_none_found')
            );

            return;
        }

        // add buttons
        $this->toolbar->addComponent(
            $this->ui_factory->button()->primary(
                $this->translator->txt('perform_migration'),
                $this->ctrl->getLinkTarget($this, self::CMD_PERFORM)
            )
        );

        if ($this->mode !== self::MODE_SINGLE) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->translator->txt('perform_migration_all'),
                    $this->ctrl->getLinkTarget($this, self::CMD_PERFORM_ALL)
                )
            );
        }

        try {
            $preview = new Preview($this->preview_settings);
            $proxy = $preview->previewHTML($current_page, false);

            $original = $this->ui_factory->panel()->secondary()->legacy(
                $this->translator->txt('compare_original'),
                $this->ui_factory->legacy($preview->previewHTML($current_page, true))
            );

            $proxy = $this->ui_factory->panel()->secondary()->legacy(
                $this->translator->txt('compare_proxy'),
                $this->ui_factory->legacy($preview->previewHTML($current_page, false))
            );

            $original = $this->ui_renderer->render($original);
            $proxy = $this->ui_renderer->render($proxy);

            $div = "<div class='sr_epc_compare'>$original</div><div class='sr_epc_compare'>$proxy</div>";

            $this->tpl->setContent(
                $div
            );
        } catch (Throwable $t) {
            $this->tpl->setContent(
                'The page cannot be displayed due to an internal error: ' . $t->getMessage()
            );
        }

        $workflow->start($current_page->getPageId());
        if ($workflow->mayHaveNext() && $workflow->getLast() !== null) {
            $this->ctrl->setParameter($this, self::P_LAST_WID, (string) $current_page->getPageId());
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->translator->txt('next_page'),
                    $this->ctrl->getLinkTarget($this, self::CMD_INDEX)
                )
            );
        }
    }

    protected function perform(): void
    {
        $workflow = $this->buildWorkflow(
            new WorkflowSettings(
                false,
                $this->preview_settings
            )
        );

        $current_page = $workflow->run()->current();

        if ($current_page !== null) {
            $this->page_repository->store($current_page);
        }

        $this->ctrl->setParameter($this, self::P_LAST_WID, (string) $current_page->getPageId());
        $this->tpl->setOnScreenMessage(
            'success',
            $this->translator->txt('migration_success'),
            true
        );
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function performAll(): void
    {
        $workflow = $this->buildWorkflow(
            new WorkflowSettings(
                false,
                $this->preview_settings
            )
        );

        while (($page = $workflow->run()->current()) !== null) {
            $this->page_repository->store($page);
            $workflow->start($page->getPageId());
        }

        $this->tpl->setOnScreenMessage(
            'success',
            $this->translator->txt('migration_success'),
            true
        );
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function determinePageProvider(): PageProvider
    {
        $wid = $this->http_wrapper->query()->has(self::P_ID)
            ? $this->http_wrapper->query()->retrieve(self::P_ID, $this->dic->ilias()->refinery()->kindlyTo()->int())
            : null;

        switch ($this->mode) {
            case self::MODE_SINGLE:
                $page_provider = new SinglePageProvider($this->page_repository, $wid);
                break;
            case self::MODE_MULTI:
                $page_provider = new ObjectPagesProvider($this->page_repository, $wid);
                break;
            default:
                $page_provider = new AllPagesProvider($this->page_repository);
                break;
        }

        return $page_provider;
    }

    protected function buildWorkflow(WorkflowSettings $workflow_settings): MigrationWorkflow
    {
        // determine PageProvider
        $page_provider = $this->determinePageProvider();

        $workflow = new PageByPageWorkflow(
            $this->dic[ParserFactory::class],
            $page_provider,
            $this->dic[EmbeddableRepository::class],
            $workflow_settings,
            $this->dic[Check::class],
            (bool) $this->dic->settings()->get('silent_creation', false)
        );

        $query = $this->http_wrapper->query();
        $last = $query->has(self::P_LAST_WID)
            ? $query->retrieve(self::P_LAST_WID, $this->dic->ilias()->refinery()->kindlyTo()->int())
            : null;

        $workflow->start($last);

        return $workflow;
    }

}
