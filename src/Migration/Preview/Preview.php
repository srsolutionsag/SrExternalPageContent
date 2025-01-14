<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Preview;

use srag\Plugins\SrExternalPageContent\Migration\Page\Page;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Preview
{
    private PreviewSettings $preview_settings;
    private \XSLTProcessor $xslt;

    public function __construct(
        PreviewSettings $setting
    ) {
        $this->preview_settings = $setting;

        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet(new \SimpleXMLElement(file_get_contents('./Services/COPage/xsl/page.xsl')));
    }

    public function preview(Page $page): string
    {
        return $this->prepareOutput(
            $page->getContent()
        );
    }

    public function previewHTML(Page $page, bool $original = false): string
    {
        $page_proxy = new \ilPageProxyGUI($page, $original);

        return $this->prepareOutput(
            $page_proxy->showProxyPage()
        );
    }

    private function prepareOutput(string $content): string
    {
        $content = sprintf($this->preview_settings->getEncapsulation(), $content);
        return str_replace("\n", $this->preview_settings->getLineEnding(), $content);
    }

}
