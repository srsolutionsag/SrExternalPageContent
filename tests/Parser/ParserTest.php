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

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Parser\iFrameParser;

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
                    'width' => '560'
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
                    'width' => '886'
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
                    'width' => '640'
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
                    'width' => '1200'
                ]
            ],
            //            [
            //                'content'=> '<iframe id="odysee-iframe" style="width:100%; aspect-ratio:16 / 9;" src="https://odysee.com/$/embed/@theswisshiker:6/gemütliche-wanderung-in-zürich-mit:8?r=F1nzvjpeB6mpCmTwd1QTaf2bPyumSMPQ" allowfullscreen></iframe>',
            //                'data' => [
            //                    'url' => 'https://odysee.com/$/embed/@theswisshiker:6/gemütliche-wanderung-in-zürich-mit:8?r=F1nzvjpeB6mpCmTwd1QTaf2bPyumSMPQ',
            //                ]
            //            ],
            [
                'content' => '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;"> <iframe style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/x7bnbnb?autoplay=1" width="100%" height="100%" allowfullscreen title="Dailymotion Video Player" allow="autoplay; web-share"> </iframe> </div>',
                'data' => [
                    'url' => 'https://www.dailymotion.com/embed/video/x7bnbnb?autoplay=1',
                    'title' => 'Dailymotion Video Player',
                    'frameborder' => '0',
                    'allow' => ['autoplay', 'web-share'],
                    'allowfullscreen' => true,
                    'height' => 100,
                    'width' => 100
                ]
            ]
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
        $this->assertEquals($data['referrerpolicy'], $embeddable->getReferrerpolicy());
        $this->assertEquals($data['allowfullscreen'], $embeddable->isAllowfullscreen());
        $this->assertEquals($data['height'], $embeddable->getHeight());
        $this->assertEquals($data['width'], $embeddable->getWidth());
    }
}
