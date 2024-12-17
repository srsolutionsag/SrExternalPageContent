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
 * @ilCtrl_isCalledBy ilPageProxyGUI: ilSEPCMigrationGUI
 */
class ilPageProxyGUI extends \ilPageObjectGUI
{
    private Page $_page;
    private bool $_original;

    public function __construct(
        Page $page,
        bool $original = false
    ) {
        $this->_page = $page;
        $this->_original = $original;
        parent::__construct($page->getParentType(), $page->getPageId());
    }

    public function afterConstructor(): void
    {
        $page_proxy = new ilPageProxy(0);
        $page_proxy->initProxy($this->_page, $this->_original);
        $this->obj = $page_proxy;
    }

    public function showProxyPage(): string
    {
        return $this->showPage();
    }

    public function getCompareMode(): bool
    {
        return true;
    }

}
