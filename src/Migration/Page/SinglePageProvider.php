<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Page;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class SinglePageProvider implements PageProvider
{
    private PageRepository $repository;
    private int $page_id;

    public function __construct(PageRepository $repository, int $page_id)
    {
        $this->repository = $repository;
        $this->page_id = $page_id;
    }

    public function next(?int $after = null): ?Page
    {
        $this->repository->setSkipped($after);

        return $this->repository->get($this->page_id);
    }

    public function count(): int
    {
        return 1;
    }

    public function canHaveNext(): bool
    {
        return false;
    }

}
