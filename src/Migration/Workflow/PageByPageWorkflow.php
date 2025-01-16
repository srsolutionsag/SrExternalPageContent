<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Migration\Workflow;

use srag\Plugins\SrExternalPageContent\Migration\Page\Page;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Migration\Transformation\XMLTransformation;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Migration\Page\PageProvider;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PageByPageWorkflow implements MigrationWorkflow
{
    private XMLTransformation $transfomation;
    private ?Page $page = null;
    private PageProvider $page_provider;

    public function __construct(
        ParserFactory $parser_factory,
        PageProvider $page_provider,
        EmbeddableRepository $embeddable_repository,
        WorkflowSettings $workflow_settings,
        Check $whitelist_check,
        bool $create_silently = true
    ) {
        $this->page_provider = $page_provider;
        $this->transfomation = new XMLTransformation(
            $embeddable_repository,
            $parser_factory,
            $workflow_settings,
            $whitelist_check,
            $create_silently
        );
    }

    public function start(?int $after = null): void
    {
        $this->page = $this->page_provider->next($after);
    }

    /**
     * @return \Generator|Page[]
     */
    public function run(): \Generator
    {
        if ($this->page === null) {
            return;
        }

        yield $this->page->withContent($this->transfomation->transform($this->page->getContent()));
    }

    public function getLast(): ?Page
    {
        return $this->page;
    }

    public function mayHaveNext(): bool
    {
        return $this->page_provider->canHaveNext();
    }

}
