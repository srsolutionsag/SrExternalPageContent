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
class PageRepository
{
    private ?int $skipped = null;

    public function __construct(private \ilDBInterface $db)
    {
    }

    public function setSkipped(?int $page_id = null): void
    {
        $this->skipped = $page_id;
    }

    public function getByObjId(int $parent_id, string $language = 'de'): array
    {
        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type, lang
                        FROM page_object 
                        WHERE content LIKE %s AND parent_id = %s AND page_id > %s AND lang = %s
                        ORDER BY page_id ASC ",
            ['text', 'integer', 'integer', 'text'],
            ['%&lt;%iframe%', $parent_id, $this->skipped ?? 0, $language]
        );
        $pages = [];
        while ($d = $this->db->fetchObject($res)) {
            $pages[] = new Page(
                (int) $d->page_id,
                (string) $d->parent_type,
                (string) $d->content
            );
        }

        return $pages;
    }

    public function get(int $page_id, string $parent_type, string $language = 'de'): ?Page
    {
        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type, lang
                        FROM page_object 
                        WHERE content LIKE %s AND page_id = %s AND parent_type = %s AND lang = %s",
            ['text', 'integer', 'text', 'text'],
            ['%&lt;%iframe%', $page_id, $parent_type, $language]
        );
        $first = $this->db->fetchObject($res);

        if ($first === null) {
            return null;
        }

        return new Page(
            (int) $first->page_id,
            (string) $first->parent_type,
            (string) $first->lang,
            (string) $first->content
        );
    }

    public function store(Page $page): void
    {
        $this->db->manipulateF(
            "UPDATE page_object SET content = %s WHERE page_id = %s AND parent_type = %s AND lang = %s",
            ['clob', 'integer', 'text', 'text'],
            [$page->getContent(), $page->getPageId(), $page->getParentType(), $page->getLang()]
        );
    }

    public function getNext(): ?Page
    {
        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type, lang
                        FROM page_object 
                        WHERE content LIKE %s AND page_id > %s 
                        ORDER BY page_id ASC 
                        LIMIT 1 ",
            ['text', 'integer'],
            ['%&lt;%iframe%', $this->skipped ?? 0]
        );
        $first = $this->db->fetchObject($res);

        if ($first === null) {
            return null;
        }

        return new Page(
            (int) $first->page_id,
            (string) $first->parent_type,
            (string) $first->lang,
            (string) $first->content
        );
    }

    public function countPages(): int
    {
        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type 
                        FROM page_object 
                        WHERE content LIKE %s AND page_id > %s",
            ['text', 'integer'],
            ['%&lt;%iframe%', $this->skipped ?? 0]
        );
        return $res->rowCount();
    }

    public function countMigratableContents(int $page_id, string $parent_type): int
    {
        $page = $this->get($page_id, $parent_type);
        if ($page === null) {
            return 0;
        }
        $content = $page->getContent();
        $matches = preg_match_all('/&lt;iframe/m', $content);

        return (int) $matches;
    }

    public function countPossiblePagesWithIframes(int $object_id): int
    {
        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type 
                        FROM page_object 
                        WHERE content LIKE %s AND parent_id = %s",
            ['text', 'integer'],
            ['%&lt;%iframe%', $object_id]
        );
        return $res->rowCount();
    }

}
