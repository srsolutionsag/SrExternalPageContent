<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Tests\Parser;

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Parser\iFrameParser;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionMode;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;

class ParserTest extends TestCase
{
    public function iFrameProvider(): array
    {
        return [
            [
                'content' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
                'data' => [
                    'url' => 'https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2',
                    'title' => 'YouTube video player',
                    'frameborder' => '0',
                    'allow' => [
                        'accelerometer',
                        'autoplay',
                        'clipboard-write',
                        'encrypted-media',
                        'gyroscope',
                        'picture-in-picture',
                        'web-share'
                    ],
                    'referrerpolicy' => 'strict-origin-when-cross-origin',
                    'allowfullscreen' => true,
                    'height' => '315',
                    'width' => '560',
                    'mode' => DimensionMode::ASPECT_RATIO
                ]
            ],
            [
                'content' => '<iframe src="https://zuugs.hfh.ch/h5pgamipresslookbook/wp-admin/admin-ajax.php?action=h5p_embed&id=1" width="886" height="851" frameborder="0" allowfullscreen="allowfullscreen" title="Kreativit&auml;t"></iframe>',
                'data' => [
                    'url' => 'https://zuugs.hfh.ch/h5pgamipresslookbook/wp-admin/admin-ajax.php?action=h5p_embed&id=1',
                    'title' => 'Kreativität',
                    'frameborder' => '0',
                    'allow' => [],
                    'referrerpolicy' => '',
                    'allowfullscreen' => true,
                    'height' => '851',
                    'width' => '886',
                    'mode' => DimensionMode::ASPECT_RATIO
                ]
            ],
            [
                'content' => '<iframe width="640" height="360" src="https://tube.switch.ch/embed/UN873PBPxw" frameborder="0" allow="fullscreen"></iframe>',
                'data' => [
                    'url' => 'https://tube.switch.ch/embed/UN873PBPxw',
                    'title' => '',
                    'frameborder' => '0',
                    'allow' => ['fullscreen'],
                    'referrerpolicy' => '',
                    'allowfullscreen' => true,
                    'height' => '360',
                    'width' => '640',
                    'mode' => DimensionMode::ASPECT_RATIO
                ]
            ],
            [
                'content' => '<div style="width: 100%;"><div style="position: relative; padding-bottom: 56.25%; padding-top: 0; height: 0;"><iframe title="Tims Sicht Deutsch" frameborder="0" width="1200px" height="675px" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="https://view.genial.ly/64b44fff406e1b0012e07d92" type="text/html" allowscriptaccess="always" allowfullscreen="true" scrolling="yes" allownetworking="all"></iframe> </div> </div>',
                'data' => [
                    'url' => 'https://view.genial.ly/64b44fff406e1b0012e07d92',
                    'title' => 'Tims Sicht Deutsch',
                    'frameborder' => '0',
                    'allow' => [],
                    'referrerpolicy' => '',
                    'allowfullscreen' => true,
                    'height' => '675',
                    'width' => '1200',
                    'mode' => DimensionMode::ASPECT_RATIO
                ]
            ],
            [
                'content' => '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;"> <iframe style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/x7bnbnb?autoplay=1" width="100%" height="100%" allowfullscreen title="Dailymotion Video Player" allow="autoplay; web-share"> </iframe> </div>',
                'data' => [
                    'url' => 'https://www.dailymotion.com/embed/video/x7bnbnb?autoplay=1',
                    'title' => 'Dailymotion Video Player',
                    'frameborder' => '0',
                    'allow' => ['autoplay', 'web-share'],
                    'allowfullscreen' => true,
                    'height' => DimensionBuilder::DEFAULT_HEIGHT, // default
                    'width' => DimensionBuilder::DEFAULT_WIDTH, // default,
                    'mode' => DimensionMode::FIXED
                ]
            ],
            [
                'content' => '<div class="padlet-embed" style="border:1px solid rgba(0,0,0,0.1);border-radius:2px;box-sizing:border-box;overflow:hidden;position:relative;width:100%;background:#F4F4F4"><p style="padding:0;margin:0"><iframe src="https://phbern.padlet.org/embed/8ignua3iwgij8q1v" frameborder="0" allow="camera;microphone;geolocation" style="width:100%;height:608px;display:block;padding:0;margin:0"></iframe></p><div style="display:flex;align-items:center;justify-content:end;margin:0;height:28px"><a href="https://padlet.com?ref=embed" style="display:block;flex-grow:0;margin:0;border:none;padding:0;text-decoration:none" target="_blank"><div style="display:flex;align-items:center;"><img src="https://padlet.net/embeds/made_with_padlet_2022.png" width="114" height="28" style="padding:0;margin:0;background:0 0;border:none;box-shadow:none" alt="Mit Padlet erstellt"></div></a></div></div>
',
                'data' => [
                    'url' => 'https://phbern.padlet.org/embed/8ignua3iwgij8q1v',
                    'title' => '',
                    'frameborder' => '0',
                    'allow' => ['camera', 'microphone', 'geolocation'],
                    'allowfullscreen' => false,
                    'height' => 608,
                    'width' => 100,
                    'mode' => DimensionMode::FIXED_HEIGHT
                ]
            ],
        ];
    }

    /**
     * @dataProvider iFrameProvider
     */
    public function testIFrameParser(string $content, array $data): void
    {
        $iframe_parser = new iFrameParser();
        $embeddable = $iframe_parser->parse($content);
        $this->assertEquals($data['url'], $embeddable->getUrl());
        $this->assertEquals($data['title'], $embeddable->getTitle());
        $this->assertEquals($data['frameborder'], $embeddable->getFrameborder());
        $this->assertEquals($data['allow'] ?? [], $embeddable->getAllow());
        $this->assertEquals($data['referrerpolicy'] ?? '', $embeddable->getReferrerpolicy());
        $this->assertEquals($data['allowfullscreen'] ?? false, $embeddable->isAllowfullscreen());
        $this->assertEquals($data['height'], $embeddable->getDimension()->getMaxHeight());
        $this->assertEquals($data['width'], $embeddable->getDimension()->getMaxWidth());
        $this->assertEquals($data['mode'], $embeddable->getDimension()->getMode());
    }

    public function testParseScripts(): void
    {
        $iframe_parser = new iFrameParser();

        $content = '<iframe src="https://zuugs.hfh.ch/h5panwendungen/wp-admin/admin-ajax.php?action=h5p_embed&id=1" width="1062" height="622" frameborder="0" allowfullscreen="allowfullscreen" title="Die sensomotorische Entwicklung (0-2 Jahre) Alois Bigger"></iframe><script src="https://zuugs.hfh.ch/h5panwendungen/wp-content/plugins/h5p/h5p-php-library/js/h5p-resizer.js" charset="UTF-8"></script>';

        $embeddable = $iframe_parser->parse($content);
        $this->assertIsArray($embeddable->getScripts());
        $this->assertCount(1, $embeddable->getScripts());
    }

}
