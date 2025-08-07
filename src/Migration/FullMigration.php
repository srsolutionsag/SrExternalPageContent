<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration;

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use srag\Plugins\SrExternalPageContent\Migration\Page\AllPagesProvider;
use srag\Plugins\SrExternalPageContent\Migration\Page\PageRepository;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\WorkflowSettings;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\PageByPageWorkflow;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepositoryDB;
use ILIAS\Setup\CLI\IOWrapper;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepositoryDB;
use srag\Plugins\SrExternalPageContent\Whitelist\DomainParser;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;
use ILIAS\Setup\UnachievableException;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FullMigration implements Migration
{
    private ?\ilDBInterface $db = null;
    private ?AllPagesProvider $page_provider = null;
    private ?WorkflowSettings $settings = null;
    private ?PageByPageWorkflow $workflow = null;
    private ?int $after = null;

    private ?IOWrapper $io = null;
    private ?EmbeddableRepository $embeddable_repository = null;
    private ?PageRepository $page_repository = null;

    public function getLabel(): string
    {
        return "Migrate existing iframe contents to plugin";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        $this->page_provider = new AllPagesProvider(new PageRepository($this->db));
        $this->settings = new WorkflowSettings(
            false,
        );
        $this->embeddable_repository = new EmbeddableRepositoryDB($this->db, new DimensionBuilder());
        $this->page_repository = new PageRepository($this->db);
        $this->workflow = new PageByPageWorkflow(
            new ParserFactory(new DimensionBuilder()),
            $this->page_provider,
            $this->embeddable_repository,
            $this->settings,
            new Check(new WhitelistRepositoryDB($this->db), new DomainParser()),
            true
        );
        $this->after = null;
    }

    public function step(Environment $environment): void
    {
        if (!\ilSEPCMigrationGUI::ENABLE_ALL) {
            throw new UnachievableException(
                'The full migration is currently diabled.'
            );
        }

        $this->workflow->start($this->after);
        $page_id = ($maybe_page = $this->workflow->getLast()) !== null ? $maybe_page->getPageId() : null;
        $this->io->text("Migrate page with id: " . $page_id);
        try {
            $page = $this->workflow->run()->current();
            if ($page !== null) {
                $this->page_repository->store($page);
            }
        } catch (\Throwable $t) {
            $has_error = true;
        }
        $this->after = $page_id;
    }

    public function getRemainingAmountOfSteps(): int
    {
        return $this->page_provider->count();
    }

}
