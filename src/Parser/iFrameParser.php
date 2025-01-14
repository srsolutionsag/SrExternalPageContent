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
use srag\Plugins\SrExternalPageContent\Content\UniqueIdGenerator;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class iFrameParser implements Parser
{
    private Sanitizer $sanitizer;
    private int $default_width = iFrame::DEFAULT_WIDTH;
    private int $default_height = iFrame::DEFAULT_HEIGHT;
    private UniqueIdGenerator $id_generator;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
        $this->id_generator = new UniqueIdGenerator();
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

        // prepend https if not present
        if (strpos($url, 'http') !== 0) {
            $url = 'https://' . ltrim($url, '/');
        }

        // test URL
        try {
            $url_test = new URI($url);
        } catch (\Throwable $e) {
            return new NotEmbeddable($url, NotEmbeddableReasons::NO_URL, $e->getMessage());
        }

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
        $style = $iframe->getAttribute('style');

        $width = $iframe->getAttribute('width');
        if (empty($width) && preg_match('/width:[ ]?(?<value>([\d]+)(px|%))/m', $style, $width_matches)) {
            $width = $width_matches['value'] ?? '';
        }

        $height = $iframe->getAttribute('height');
        if (empty($height) && preg_match('/height:[ ]?(?<value>([\d]+)(px|%))/m', $style, $height_matches)) {
            $height = $height_matches['value'] ?? '';
        }

        // determine unit of width and height
        if (strpos($width, 'px') !== false) {
            $width = str_replace('px', '', $width);
        } elseif (strpos($width, '%') !== false) {
            $width = $this->default_width;
        } else {
            $width = (int) $width;
        }

        // determine unit of width and height
        if (strpos($height, 'px') !== false) {
            $height = str_replace('px', '', $height);
        } elseif (strpos($height, '%') !== false) {
            $height = $this->default_height;
        } else {
            $height = (int) $height;
        }

        $properties = [
            'title' => $title,
            'height' => $height,
            'width' => $width,
            'frameborder' => $frameborder,
            'allow' => $allow,
            'referrerpolicy' => $referrerpolicy,
            'allowfullscreen' => $allowfullscreen,
            'responsive' => true,
        ];

        // scripts parsen
        $scripts = [];
        $script_tags = $html->getElementsByTagName('script');

        foreach ($script_tags as $script_tag) {
            $scripts[] = $script_tag->getAttribute('src');
        }

        return new iFrame(
            $this->id_generator->generate(),
            $url,
            $properties,
            $scripts
        );
    }

}
