<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content\Dimension;

use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Settings\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
/**
 * @noRector TernaryToNullCoalescingRector
 */
class DimensionBuilder
{
    public const DEFAULT_WIDTH = 960;
    public const DEFAULT_HEIGHT = 540;

    protected ?int $default_width = self::DEFAULT_WIDTH;
    protected ?int $default_height = self::DEFAULT_HEIGHT;

    private const MODE = 'mode';
    private const RATIO = 'ratio';
    private const WIDTH = 'width';
    private const HEIGHT = 'height';

    public function __construct(?Settings $settings = null)
    {
        if ($settings !== null) {
            $this->default_width = $settings->get('default_width', null);
            $this->default_height = $settings->get('default_height', null);
        }
    }

    public function forJS(Dimension $dimension): string
    {
        return htmlspecialchars(json_encode((object) $this->toArray($dimension)), ENT_QUOTES, 'UTF-8');
    }

    public function build(int $mode, ?float $ratio, ?int $width, ?int $height): Dimension
    {
        return new Dimension($mode, $ratio, $width, $height);
    }

    /**
     * @noRector TernaryToNullCoalescingRector
     */
    public function fromArray(array $dimension_properties): Dimension
    {
        $height = $dimension_properties[self::HEIGHT] ?? null;
        $width = $dimension_properties[self::WIDTH] ?? null;
        return $this->build(
            $dimension_properties[self::MODE] ?? $default->getMode(),
            $dimension_properties[self::RATIO] ?? $default->getRatio(),
            $width,
            $height
        );
    }

    public function toArray(Dimension $dimension): array
    {
        return [
            self::MODE => $dimension->getMode(),
            self::RATIO => $dimension->getRatio(),
            self::WIDTH => $dimension->getMaxWidth(),
            self::HEIGHT => $dimension->getMaxHeight()
        ];
    }

    /**
     * @depracated use only for migration of old data
     */
    public function fromLegacyProperties(Embeddable $embeddable): Dimension
    {
        $width = $embeddable->getProperties()[self::WIDTH] ?? null;
        $height = $embeddable->getProperties()[self::HEIGHT] ?? null;
        $responsive = $embeddable->getProperties()['responsive'] ?? false;

        return $this->determineBest($width, $height, 'px', 'px', (bool) $responsive);
    }

    public function buildFromXML(\DOMElement $iframe): Dimension
    {
        $default = $this->default();
        // Determine Dimensions
        $style = $iframe->getAttribute('style');

        // width
        $width_unit = 'px';
        $width = $iframe->getAttribute('width');
        if (empty($width) && preg_match('/width:(?P<value>\d+)(?P<unit>px|%)?/m', $style, $width_matches)) {
            $width = (int) ($width_matches['value'] ?? $default->getMaxWidth());
            $width_unit = $width_matches['unit'] ?? 'px';
        } elseif (!empty($width) && preg_match('/(?P<value>\d+)(?P<unit>px|%)?/m', $width, $width_matches)) {
            $width = (int) ($width_matches['value'] ?? $default->getMaxWidth());
            $width_unit = $width_matches['unit'] ?? 'px';
        } else {
            $width = $default->getMaxWidth();
            $width_unit = 'px';
        }

        // height
        $height_unit = 'px';
        $height = $iframe->getAttribute('height');
        if (empty($height) && preg_match('/height:(?P<value>\d+)(?P<unit>px|%)?/m', $style, $height_matches)) {
            $height = (int) ($height_matches['value'] ?? $default->getMaxHeight());
            $height_unit = $height_matches['unit'] ?? 'px';
        } elseif (!empty($height) && preg_match('/(?P<value>\d+)(?P<unit>px|%)?/m', $height, $height_matches)) {
            $height = (int) ($height_matches['value'] ?? $default->getMaxHeight());
            $height_unit = $height_matches['unit'] ?? 'px';
        } else {
            $height = $default->getMaxHeight();
            $height_unit = 'px';
        }

        return $this->determineBest($width, $height, $width_unit, $height_unit, true);
    }

    private function determineBest(
        ?int $width,
        ?int $height,
        ?string $width_unit,
        ?string $height_unit,
        bool $responsive = true
    ): Dimension {
        $default = $this->default();
        // check if is a known aspect ratio
        if (
            $height_unit !== '%'
            && $width_unit !== '%'
            && $width !== null
            && $height !== null
            && $width > 0
            && $height > 0
        ) {
            $aspect_ratio = $width / $height;

            if (in_array($aspect_ratio, [
                DimensionMode::AS_16_9,
                DimensionMode::AS_4_3,
                DimensionMode::AS_1_1,
                DimensionMode::AS_3_4,
                DimensionMode::AS_9_16
            ], true)) {
                return new Dimension(
                    DimensionMode::ASPECT_RATIO,
                    $aspect_ratio,
                    $width,
                    $height
                );
            }
        }

        // prevent 0 values
        $width = ($width === 0) ? $default->getMaxWidth() : $width;
        $height = ($height === 0) ? $default->getMaxHeight() : $height;

        if (!$responsive) {
            return new Dimension(
                DimensionMode::FIXED,
                null,
                $width ?? $default->getMaxWidth(),
                $height ?? $default->getMaxHeight()
            );
        }

        if ($width_unit === '%' && $height_unit === '%') {
            return new Dimension(
                DimensionMode::FIXED,
                null,
                $default->getMaxWidth(),
                $default->getMaxHeight()
            );
        }

        if ($width_unit === '%' && $height_unit === 'px') {
            return new Dimension(
                DimensionMode::FIXED_HEIGHT,
                null,
                $width,
                $height
            );
        }

        return new Dimension(
            DimensionMode::ASPECT_RATIO,
            ($width ?? 1) / ($height ?? 1),
            $width,
            $height
        );
    }

    public function default(): Dimension
    {
        return new Dimension(
            DimensionMode::ASPECT_RATIO,
            DimensionMode::AS_16_9,
            $this->default_width,
            $this->default_height
        );
    }

}
