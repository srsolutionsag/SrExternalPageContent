<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Transformation;

use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Migration\Workflow\WorkflowSettings;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Migration\Preview\PreviewDTO;
use srag\Plugins\SrExternalPageContent\Helper\Hasher;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class XMLTransformation implements Transformation
{
    use Hasher;

    private const ELEMENT_PLUGGED = 'Plugged';
    private const ATTRIBUTE_PLUGIN_NAME = 'PluginName';
    private const ATTRIBUTE_PLUGIN_VERSION = 'PluginVersion';
    private const ELEMENT_PLUGGED_PROPERTY = 'PluggedProperty';
    private const ATTRIBUTE_NAME = 'Name';
    private const IFRAME = 'iframe';
    private const XPATH = "//Paragraph[text()[contains(.," . self::IFRAME . ")]]";

    private EmbeddableRepository $embeddable_repository;
    private ParserFactory $parser_factory;
    private WorkflowSettings $workflow_settings;

    public function __construct(
        EmbeddableRepository $embeddable_repository,
        ParserFactory $parser_factory,
        WorkflowSettings $workflow_settings
    ) {
        $this->workflow_settings = $workflow_settings;
        $this->embeddable_repository = $embeddable_repository;
        $this->parser_factory = $parser_factory;
    }

    private function buildXPath(string $content): \DOMXPath
    {
    }

    public function transform(string $former_content): string
    {
        $preview = $this->workflow_settings->isDryRun();
        $dom = new \DOMDocument('', '');
        $dom->loadXML($former_content);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query(self::XPATH) as $paragraph) {
            /** @var \DOMNode $paragraph */
            if (stripos($paragraph->textContent, self::IFRAME) === false) {
                continue;
            }
            $content = str_replace(["&lt;", "&gt;"], ["<", ">"], $paragraph->textContent);

            $parser = $this->parser_factory->createParser($content);
            $embeddable = $parser->parse($content);
            if (!$embeddable instanceof iFrame) {
                continue;
            }

            if (!$preview) {
                $embeddable = $this->embeddable_repository->store($embeddable);
            }

            // Create Plugin Element
            $plugged = $dom->createElement(self::ELEMENT_PLUGGED);
            $plugged->setAttribute(self::ATTRIBUTE_PLUGIN_NAME, 'SrExternalPageContent');
            $plugged->setAttribute(self::ATTRIBUTE_PLUGIN_VERSION, '1.1.0');

            // Add Properties
            $embeddable_id = $dom->createElement(self::ELEMENT_PLUGGED_PROPERTY, (string) $embeddable->getId());
            $embeddable_id->setAttribute(self::ATTRIBUTE_NAME, 'embeddable_id');
            $plugged->appendChild($embeddable_id);

            $former_content = $dom->createElement(self::ELEMENT_PLUGGED_PROPERTY, $this->hash($paragraph->textContent));
            $former_content->setAttribute(self::ATTRIBUTE_NAME, 'former_content');
            $plugged->appendChild($former_content);

            if ($this->workflow_settings->isDryRun()) {
                $url = $dom->createElement(self::ELEMENT_PLUGGED_PROPERTY, PreviewDTO::sleep($embeddable));
                $url->setAttribute(self::ATTRIBUTE_NAME, 'preview');
                $plugged->appendChild($url);
            }

            //            $string = preg_replace("/<iframe\s(.+?)>(.+?)<\/iframe>/is", "|||-|||", $content);
            //            $surrounding_content = $dom->createElement('PluggedProperty', $string);
            //            $surrounding_content->setAttribute('Name', 'surrounding_content');
            //            $plugged->appendChild($surrounding_content);

            // replace the element with a new one
            /** @var \DOMNode $parent */
            $parent = $paragraph->parentNode;
            // $dom->importNode($plugged, true);
            $parent->replaceChild($plugged, $paragraph);
        }

        return $this->formatOutput($dom);
    }

    private function formatOutput(\DOMDocument $dom): string
    {
        if ($this->workflow_settings->isDryRun()) {
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            return $dom->saveXML($dom->documentElement);
        }

        return $dom->saveXML($dom->documentElement);
    }

}
