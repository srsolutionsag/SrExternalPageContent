<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Tests\Dimension;

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionMode;

class DimensionTest extends TestCase
{
    private ?DimensionBuilder $dimension_builder = null;

    protected function setUp(): void
    {
        $this->dimension_builder = new DimensionBuilder();
    }

    public function legacyProvider(): array
    {
        $this->dimension_builder ??= new DimensionBuilder();
        return [
            [
                new iFrame('one', '', $this->dimension_builder->default(), ['width' => 1600, 'height' => 900, 'responsive' => true]),
                DimensionMode::ASPECT_RATIO,
                DimensionMode::AS_16_9,
                1600,
                900
            ],
            [
                new iFrame('two', '', $this->dimension_builder->default(), ['width' => 560, 'height' => 315, 'responsive' => true]),
                DimensionMode::ASPECT_RATIO,
                DimensionMode::AS_16_9,
                560,
                315
            ],
            [
                new iFrame('two', '', $this->dimension_builder->default(), ['width' => 400, 'height' => 200, 'responsive' => false]),
                DimensionMode::FIXED,
                null,
                400,
                200
            ],
            [
                new iFrame('two', '', $this->dimension_builder->default(), ['width' => 400, 'height' => 300, 'responsive' => true]),
                DimensionMode::ASPECT_RATIO,
                DimensionMode::AS_4_3,
                400,
                300
            ],
        ];
    }

    /**
     * @dataProvider legacyProvider
     */
    public function testLegacyMigration(
        Embeddable $e,
        int $mode,
        ?float $ratio,
        ?float $max_width = null,
        ?float $max_height = null
    ): void {
        $this->assertInstanceOf(iFrame::class, $e);

        $dimension = $this->dimension_builder->fromLegacyProperties($e);

        $this->assertEquals($mode, $dimension->getMode());
        $this->assertEquals($ratio, $dimension->getSetRatio());
        $this->assertEquals($max_width, $dimension->getMaxWidth());
        $this->assertEquals($max_height, $dimension->getMaxHeight());
    }

}
