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
class Page
{
    private int $page_id;
    private string $content = '';
    private string $parent_type;

    public function __construct(
        int $page_id,
        string $parent_type,
        string $content = ''
    ) {
        $this->page_id = $page_id;
        $this->parent_type = $parent_type;
        $this->content = $content;
    }

    public function getPageId(): int
    {
        return $this->page_id;
    }

    public function getParentType(): string
    {
        return $this->parent_type;
    }

    public function withContent(string $content): self
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    public function getContent(): string
    {
        return $this->content;
    }

}
