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
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionMode;

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

        // Dimensions
        $dimensions = $factory->switchableGroup(
            [
                DimensionMode::ASPECT_RATIO => $factory->group(
                    [
                        $factory->select(
                            $this->translator->txt('aspect_ratio'),
                            [
                                (string) DimensionMode::AS_16_9 => '16:9',
                                (string) DimensionMode::AS_4_3 => '4:3',
                                (string) DimensionMode::AS_1_1 => '1:1',
                                (string) DimensionMode::AS_3_4 => '3:4',
                                (string) DimensionMode::AS_9_16 => '9:16',
                            ],
                            $this->translator->txt('aspect_ratio_info')
                        )->withRequired(true),
                        $factory->numeric(
                            $this->translator->txt('aspect_ratio_width'),
                            $this->translator->txt('aspect_ratio_width_info')
                        )
                    ],
                    $this->translator->txt('aspect_ratio_dimensions'),
                    $this->translator->txt('aspect_ratio_dimensions_info')
                )->withValue(
                    [
                        (string) $this->embeddable->getDimension()->getRatio(),
                        $this->embeddable->getDimension()->getMaxWidth()
                    ]
                )->withAdditionalTransformation(
                    $this->refinery->trafo(
                        fn($d): array => [
                            $this->embeddable->getDimension()
                                             ->setRatio((float) $d[0])
                                             ->getRatio(),
                            $this->embeddable->getDimension()
                                             ->setMaxHeight(null)
                                             ->setMaxWidth($d[1])
                                             ->getMaxWidth()
                        ]
                    )
                ),
                DimensionMode::FIXED => $factory->group(
                    [
                        $factory->numeric(
                            $this->translator->txt('fixed_width'),
                            $this->translator->txt('fixed_width_info')
                        )->withRequired(true),
                        $factory->numeric(
                            $this->translator->txt('fixed_height'),
                            $this->translator->txt('fixed_height_info')
                        )->withRequired(true)
                    ],
                    $this->translator->txt('fixed_dimensions'),
                    $this->translator->txt('fixed_dimensions_info')
                )->withValue(
                    [
                        $this->embeddable->getDimension()->getMaxWidth(),
                        $this->embeddable->getDimension()->getMaxHeight()
                    ]
                )->withAdditionalTransformation(
                    $this->refinery->trafo(
                        fn($d): array => [
                            $this->embeddable->getDimension()
                                             ->setRatio(null)
                                             ->setMaxWidth($d[0])
                                             ->getMaxWidth(),
                            $this->embeddable->getDimension()
                                             ->setRatio(null)
                                             ->setMaxHeight($d[1])
                                             ->getMaxHeight()
                        ]
                    )
                ),
                DimensionMode::FIXED_HEIGHT => $factory->group(
                    [
                        $factory->numeric(
                            $this->translator->txt('fixed_height'),
                            $this->translator->txt('fixed_height_info')
                        )->withRequired(true)
                    ],
                    $this->translator->txt('fixed_height'),
                    $this->translator->txt('fixed_height_info')
                )->withValue(
                    [$this->embeddable->getDimension()->getMaxHeight()]
                )->withAdditionalTransformation(
                    $this->refinery->trafo(
                        fn($d): int => $this->embeddable->getDimension()
                                                        ->setMaxWidth(null)
                                                        ->setMaxHeight($d[0])
                                                        ->getMaxHeight()
                    )
                ),
            ],
            $this->translator->txt('dimensions'),
            $this->translator->txt('dimensions_info'),
        )->withValue(
            $this->embeddable->getDimension()->getMode()
        )->withAdditionalTransformation(
            $this->refinery->trafo(
                fn($d): int => $this->embeddable->getDimension()->setMode((int) $d[0])->getMode()
            )
        );

        // full form
        $inputs[] = $factory
            ->text(
                $this->translator->txt('url'),
                $this->translator->txt('url_info')
            )
            ->withValue($this->embeddable->getUrl())
            ->withRequired(true)
            ->withAdditionalTransformation(
                $this->refinery->constraint(
                    function ($d): bool {
                        $silent_creation = $this->dependencies->settings()->get('silent_creation', false);
                        if ($silent_creation) {
                            return $this->whitelist_check->createSilently($d);
                        }

                        return $this->whitelist_check->isAllowed($d);
                    },
                    $this->translator->txt('embed_content_invalid_url')
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn ($d): string => $this->embeddable->setUrl($d)->getUrl())
            );

        $inputs[] = $factory
            ->text(
                $this->translator->txt('title'),
                $this->translator->txt('title_info')
            )
            ->withValue($this->embeddable->getTitle())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn ($d): string => $this->embeddable->setTitle($d)->getTitle())
            );

        $inputs[] = $dimensions;

        $inputs[] = $factory
            ->numeric(
                $this->translator->txt('frameborder'),
                $this->translator->txt('frameborder_info')
            )->withValue($this->embeddable->getFrameborder())
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn ($d): int => $this->embeddable->setFrameborder((int) $d)->getFrameborder())
            );

        $inputs[] = $factory
            ->textarea(
                $this->translator->txt('scripts'),
                $this->translator->txt('scripts_info')
            )
            ->withValue(implode("\n", $this->embeddable->getScripts()))
            ->withAdditionalTransformation(
                $this->refinery->trafo(fn ($d): array => $this->embeddable->setScripts(explode("\n", $d))->getScripts())
            );

        $inputs[] = $factory
            ->file(new \ilSrExternalPagePluginUploadHandlerGUI(), $this->translator->txt('thumbnail'))
            ->withAcceptedMimeTypes([MimeType::IMAGE__JPEG, MimeType::IMAGE__PNG])
            ->withMaxFiles(1)
            ->withValue($this->embeddable->getThumbnailRid() === null ? [] : [$this->embeddable->getThumbnailRid()])
            ->withAdditionalTransformation(
                $this->refinery->trafo(
                    fn($d): ?string => $this->embeddable->setThumbnailRid($d[0] ?? null)->getThumbnailRid()
                )
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
            fn ($value): Embeddable => $this->embeddable_repository->store($this->embeddable)
        );
    }

}
