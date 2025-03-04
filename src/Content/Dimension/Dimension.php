<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content\Dimension;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Dimension
{
    private int $mode;
    private ?float $ratio;
    private ?int $max_width = null;
    private ?int $max_height = null;

    public function __construct(
        int $mode,
        ?float $ratio,
        ?int $max_width = null,
        ?int $max_height = null
    ) {
        $this->mode = $mode;
        $this->ratio = $ratio;
        $this->max_width = $max_width;
        $this->max_height = $max_height;
        // check mode (sould be switched to ENUM in the future)

        if (!in_array($mode, [DimensionMode::FIXED, DimensionMode::FIXED_HEIGHT, DimensionMode::ASPECT_RATIO], true)) {
            throw new \InvalidArgumentException("Invalid mode");
        }
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getMaxWidth(): ?int
    {
        return $this->max_width;
    }

    public function getMaxHeight(): ?int
    {
        return $this->max_height;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function getClosestRatio(): ?float
    {
        if ($this->ratio === null) {
            return null;
        }

        $ratios = [
            DimensionMode::AS_16_9,
            DimensionMode::AS_4_3,
            DimensionMode::AS_1_1,
            DimensionMode::AS_3_4,
            DimensionMode::AS_9_16
        ];

        $closest_ratio = null;
        $closest_diff = PHP_FLOAT_MAX;

        foreach ($ratios as $ratio) {
            $diff = abs($ratio - $this->ratio);

            if ($diff < $closest_diff) {
                $closest_diff = $diff;
                $closest_ratio = $ratio;
            }
        }

        return $closest_ratio;
    }

    public function getSetRatio(): ?float
    {
        return $this->ratio;
    }

    public function setMode(int $mode): Dimension
    {
        $this->mode = $mode;
        return $this;
    }

    public function setRatio(?float $ratio): Dimension
    {
        $this->ratio = $ratio;
        return $this;
    }

    public function setMaxWidth(?int $max_width): Dimension
    {
        $this->max_width = $max_width;
        return $this;
    }

    public function setMaxHeight(?int $max_height): Dimension
    {
        $this->max_height = $max_height;
        return $this;
    }

}
