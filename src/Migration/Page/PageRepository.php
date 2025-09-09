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
    private \ilDBInterface $db;
    private ?int $skipped = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function setSkipped(?int $page_id = null): void
    {
        $this->skipped = $page_id;
    }

    public function getByObjId(int $parent_id, string $language = '-'): array
    {
        // determine type
        $res_type = $this->db->queryF(
            "SELECT type FROM object_data WHERE obj_id = %s",
            ['integer'],
            [$parent_id]
        );
        switch ($type = $this->db->fetchObject($res_type)->type) {
            case 'crs':
            case 'grp':
            case 'fold':
            case 'cat':
                $page_parent_type = 'cont';
                break;
            default:
                $page_parent_type = $type;
                break;
        }

        $res = $this->db->queryF(
            "SELECT page_id, content, parent_type, lang
                        FROM page_object 
                        WHERE content LIKE %s AND parent_id = %s AND page_id > %s AND lang = %s AND page_object.parent_type = %s
                        ORDER BY page_id ASC ",
            ['text', 'integer', 'integer', 'text', 'text'],
            ['%&lt;%iframe%', $parent_id, $this->skipped ?? 0, $language, $page_parent_type]
        );
        $pages = [];
        while ($d = $this->db->fetchObject($res)) {
            $pages[] = new Page(
                (int) $d->page_id,
                (string) $d->parent_type,
                (string) $d->lang,
                (string) $d->content
            );
        }

        return $pages;
    }

    public function get(int $page_id, string $parent_type, string $language = '-'): ?Page
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

    public function countMigratableContents(int $page_id, string $parent_type, string $language = '-'): int
    {
        $page = $this->get($page_id, $parent_type, $language);
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
