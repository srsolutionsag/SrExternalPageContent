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
    private string $parent_type;
    private string $language = '-';

    public function __construct(PageRepository $repository, int $page_id, string $parent_type)
    {
        $this->repository = $repository;
        $this->page_id = $page_id;
        $this->parent_type = $parent_type;
    }

    public function next(?int $after = null): ?Page
    {
        $this->repository->setSkipped($after);

        return $this->repository->get($this->page_id, $this->parent_type, $this->language);
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
