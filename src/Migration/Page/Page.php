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
    private string $parent_type;
    private string $lang;
    private string $content = '';
    private int $failed_contents = 0;

    public function __construct(int $page_id, string $parent_type, string $lang, string $content = '')
    {
        $this->page_id = $page_id;
        $this->parent_type = $parent_type;
        $this->lang = $lang;
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

    public function increaseFailed(int $by): self
    {
        $this->failed_contents += $by;

        return $this;
    }

    public function getFailed(): int
    {
        return $this->failed_contents;
    }

    public function getLang(): string
    {
        return $this->lang;
    }



}
