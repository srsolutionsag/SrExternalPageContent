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
class AllPagesProvider implements PageProvider
{
    private PageRepository $repository;

    public function __construct(PageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function next(?int $after = null): ?Page
    {
        $this->repository->setSkipped($after);

        return $this->repository->getNext();
    }

    public function count(): int
    {
        return $this->repository->countPages();
    }

}
