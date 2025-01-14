<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Forms;

use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use ILIAS\Refinery\Transformation;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use ILIAS\FileUpload\MimeType;

class IFrameSection extends Base implements FormElement
{
    protected iFrame $embeddable;
    private const F_URL = 'url';

    public function __construct(
        DIC $dependencies,
        iFrame $embeddable
    ) {
        $this->embeddable = $embeddable;
        parent::__construct($dependencies);
    }

    protected function getSectionTitle(): string
    {
        return $this->translator->txt('iframe_section_title');
    }

    public function getInputs(): array
    {
        $inputs = [];

        $factory = $this->ui_factory->input()->field();

        $inputs[] = $factory
            ->text(
                $this->translator->txt('url'),
                $this->translator->txt('url_info')
            )
            ->withValue($this->embeddable->getUrl())
            ->withRequired(true)
            ->withAdditionalTransformation(
                $this->refinery->constraint(
                    fn($d): bool => $this->whitelist_check->isAllowed($d),
                    $this->translator->txt('embed_content_invalid_url')
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): string => $this->embeddable->setUrl($d)->getUrl())
            );

        $inputs[] = $factory
            ->text(
                $this->translator->txt('title'),
                $this->translator->txt('title_info')
            )
            ->withValue($this->embeddable->getTitle())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): string => $this->embeddable->setTitle($d)->getTitle())
            );

        $inputs[] = $factory
            ->numeric(
                $this->translator->txt('width'),
                $this->translator->txt('width_info')
            )
            ->withValue($this->embeddable->getWidth())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): int => $this->embeddable->setWidth((int) $d)->getWidth())
            );

        $inputs[] = $factory
            ->numeric(
                $this->translator->txt('height'),
                $this->translator->txt('height_info')
            )
            ->withValue($this->embeddable->getHeight())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): int => $this->embeddable->setHeight((int) $d)->getHeight())
            );

        $inputs[] = $factory
            ->checkbox(
                $this->translator->txt('responsive'),
                $this->translator->txt('responsive_info')
            )
            ->withValue($this->embeddable->isResponsive())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): bool => $this->embeddable->setResponsive((bool) $d)->isResponsive())
            );

        $inputs[] = $factory
            ->numeric(
                $this->translator->txt('frameborder'),
                $this->translator->txt('frameborder_info')
            )->withValue($this->embeddable->getFrameborder())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): int => $this->embeddable->setFrameborder((int) $d)->getFrameborder())
            );

        $inputs[] = $factory
            ->textarea(
                $this->translator->txt('scripts'),
                $this->translator->txt('scripts_info')
            )
            ->withValue(implode("\n", $this->embeddable->getScripts()))
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): array => $this->embeddable->setScripts(explode("\n", $d))->getScripts())
            );

        $inputs[] = $factory
            ->file(new \ilSrExternalPagePluginUploadHandlerGUI(), $this->translator->txt('thumbnail'))
            ->withAcceptedMimeTypes([MimeType::IMAGE__JPEG, MimeType::IMAGE__PNG])
            ->withMaxFiles(1)
            ->withValue($this->embeddable->getThumbnailRid() === null ? [] : [$this->embeddable->getThumbnailRid()])
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn($d): ?string => $this->embeddable->setThumbnailRid($d[0] ?? null)->getThumbnailRid())
            )->withAdditionalTransformation(
                $this->getFinalTransformation()
            );

        /*
        $allow_options = [
            'autoplay',
            'fullscreen',
            'picture-in-picture',
            'accelerometer',
            'clipboard-write',
            'encrypted-media',
            'gyroscope',
            'web-share'
        ];
        $inputs[] = $factory->multiSelect(
            $this->translator->txt('allow'),
            $allow_options,
            $this->translator->txt('allow_info')
        )->withDisabled(true);
        */
        return $inputs;
    }

    protected function getFinalTransformation(): Transformation
    {
        return $this->refinery->trafo(
            fn($value): Embeddable => $this->embeddable_repository->store($this->embeddable)
        );
    }

}
