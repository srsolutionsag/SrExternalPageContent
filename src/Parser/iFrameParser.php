<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Parser;

use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;
use srag\Plugins\SrExternalPageContent\Helper\Sanitizer;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddableReasons;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class iFrameParser implements Parser
{
    private Sanitizer $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
    }

    public function parse(string $snippet): Embeddable
    {
        $html = new \DomDocument('1.0', 'UTF-8');
        $internal_errors = libxml_use_internal_errors(true);
        libxml_use_internal_errors($internal_errors);
        try {
            @$html->loadHTML($snippet);
        } catch (\Throwable $e) {
            return new NotEmbeddable('', 'parser_html_load_error', $e->getMessage());
        }

        $iframes = $html->getElementsByTagName('iframe');
        if ($iframes->length === 0) {
            return new NotEmbeddable('', 'parser_no_iframe_found');
        }
        $iframe = $iframes->item(0);
        if ($iframe === null) {
            return new NotEmbeddable('', NotEmbeddableReasons::NO_URL);
        }
        $url = $this->sanitizer->sanitizeURL($iframe->getAttribute('src'));
        $title = $this->sanitizer->sanitizeEncoding($iframe->getAttribute('title'));
        $frameborder = (int) $iframe->getAttribute('frameborder');
        $allow = explode(';', $iframe->getAttribute('allow'));
        $allow = array_map('trim', $allow);
        $allow = array_filter($allow);
        $referrerpolicy = $iframe->getAttribute('referrerpolicy');
        $allowfullscreen = $iframe->hasAttribute('allowfullscreen');
        if (in_array('fullscreen', $allow, true)) {
            $allowfullscreen = true;
        }
        $width = $iframe->getAttribute('width');
        $height = $iframe->getAttribute('height');

        $properties = [
            'title' => $title,
            'height' => empty($height) ? 'auto' : str_replace('px', '', $height),
            'width' => empty($width) ? 'auto' : str_replace('px', '', $width),
            'frameborder' => $frameborder,
            'allow' => $allow,
            'referrerpolicy' => $referrerpolicy,
            'allowfullscreen' => $allowfullscreen
        ];

        return new iFrame(
            0,
            $url,
            $properties
        );
    }

}
