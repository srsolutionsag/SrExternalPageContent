<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\Migration\Page\Page;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 * @ilCtrl_isCalledBy ilPageProxyGUI: ilSEPCSettingsGUI
 */
class ilPageProxy extends \ilPageObject
{
    private bool $_original = false;
    public ?Page $_page = null;

    protected ?string $_parent_type = null;

    public function initProxy(Page $page, bool $original = false): void
    {
        $this->_original = $original;
        $this->_page = $page;
        $this->parent_type = $page->getParentType();
        $this->_parent_type = $page->getParentType();
        $this->id = $page->getPageId();
        $this->read();
    }

    public function getXMLFromDom(
        bool $a_incl_head = false,
        bool $a_append_mobs = false,
        bool $a_append_bib = false,
        string $a_append_str = "",
        bool $a_omit_pageobject_tag = false,
        int $style_id = 0
    ): string {
        if (!$this->_original) {
            $this->xml = $this->_page->getContent();
            $this->buildDom(true);
        }

        return parent::getXMLFromDom(
            $a_incl_head,
            $a_append_mobs,
            $a_append_bib,
            $a_append_str,
            $a_omit_pageobject_tag,
            $style_id
        );
    }

    public function getParentType(): string
    {
        return $this->_parent_type ?? 'copa';
    }

    public function getWikiRefId(): int {
        $references = ilObject2::_getAllReferences($this->getWikiId());

        return reset($references) ?: 0;
    }

    public function getWikiId(): int
    {
        global $DIC;

        $wiki_id = $DIC->database()->queryF(
            'SELECT wiki_id FROM il_wiki_page WHERE id = %s',
            ['integer'],
            [$this->id]
        )->fetchAssoc()['wiki_id'] ?? 0;

        return (int) $wiki_id;
    }

}
