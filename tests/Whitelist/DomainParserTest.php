<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Tests\Whitelist;

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Whitelist\DomainParser;

class DomainParserTest extends TestCase
{
    public function provideURLs(): array
    {
        return [
            ['https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2', 'www.youtube.com'],
            [
                'https://zuugs.hfh.ch/h5pgamipresslookbook/wp-admin/admin-ajax.php?action=h5p_embed&id=1',
                'zuugs.hfh.ch'
            ],
            ['https://tube.switch.ch/embed/UN873PBPxw', 'tube.switch.ch'],
            ['https://user@tube.switch.ch/embed/UN873PBPxw', 'tube.switch.ch'],
        ];
    }

    /**
     * @dataProvider provideURLs
     */
    public function testExtractHost(string $url, string $data): void
    {
        $domain_parser = new DomainParser();
        $domain = $domain_parser->extractHost($url);
        $this->assertEquals($data, $domain);
    }

    public function testExtractDomain(): void
    {
        $domain_parser = new DomainParser();
        $domain = $domain_parser->extractDomain('tube.switch.ch');
        $this->assertEquals('switch.ch', $domain);
    }
}
