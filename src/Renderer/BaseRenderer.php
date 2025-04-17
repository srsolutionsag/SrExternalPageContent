<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Renderer;

use ILIAS\ResourceStorage\Services;
use srag\Plugins\SrExternalPageContent\Translator;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use ILIAS\Data\URI;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Settings\Settings;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseRenderer
{
    protected \ilGlobalTemplateInterface $main_tpl;
    protected DimensionBuilder $dimensions;
    protected Settings $settings;
    protected Check $check;
    protected Services $irss;
    protected Translator $translator;

    public function __construct(
        Translator $translator,
        Check $check,
        Settings $settings,
        DimensionBuilder $dimensions
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->check = $check;
        $this->settings = $settings;
        $this->translator = $translator;
        $this->irss = $DIC->resourceStorage();
        $this->dimensions = $dimensions;
    }

    protected function wrap(Embeddable $embeddable, string $content): string
    {
        $url = $embeddable->getUrl();
        $uri = new URI($url);

        $whitelisted_domain = $this->check->getBest($url);

        // add button zoom css
        $this->main_tpl->addInlineCss(
            '.sr-external-page-content-button { zoom: ' . $this->settings->get('button_zoom', '1.0') . '; }'
        );

        // content wrapper, we will move that later if there are other renderers
        $wrapper = new \ilTemplate(__DIR__ . '/../../templates/default/tpl.content_wrapper.html', false, false);
        $wrapper->setVariable('INFO', sprintf($this->translator->txt('before_load_info'), $uri->getHost()));
        $wrapper->setVariable('BUTTON_TEXT', $this->translator->txt('before_load_button'));
        $wrapper->setVariable('CONTENT', $content);
        $wrapper->setVariable('DIMENSIONS', $this->dimensions->forJS($embeddable->getDimension()));
        $wrapper->setVariable('MUST_CONSENT', '1');
        $wrapper->setVariable('LAST_RESET', $this->settings->get('reset_consent', 0));
        $wrapper->setVariable('DOMAIN', $uri->getHost());
        $wrapper->setVariable('BORDER_WIDTH', (int) ($embeddable->getProperties()['frameborder'] ?? 0));
        $wrapper->setVariable(
            'CONSENTED',
            $whitelisted_domain === null ? '0' : ($whitelisted_domain->isAutoConsent() ? '1' : '0')
        );
        $wrapper->setVariable('CONTENT_ID', 'srepc_' . $embeddable->getId());
        $thumbnail_src = $embeddable->getThumbnailRid() === null ? '' : $this->irss->consume()->src(
            $this->irss->manage()->find($embeddable->getThumbnailRid())
        )->getSrc();
        $wrapper->setVariable('THUMBNAIL', $thumbnail_src);
        $wrapper->setVariable('BUTTON_TEXT_THUMBNAIL', $this->translator->txt('close_thumbnail_button'));

        if($this->settings->get('grey_buttons', true)) {
            $wrapper->setVariable('BUTTON_CLASS', 'sr-external-page-content-grey-buttons');
        }

        foreach ($embeddable->getScripts() as $script) {
            $wrapper->setCurrentBlock('script');
            $wrapper->setVariable('SCRIPT', $script);
            $wrapper->parseCurrentBlock();
        }

        return $wrapper->get();
    }

}
