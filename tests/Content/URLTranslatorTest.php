<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Tests\Content;

use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Content\URLTranslator;

class URLTranslatorTest extends TestCase
{
    private URLTranslator $translator;

    public function provideURLs(): \Iterator
    {
        $embed = 'https://www.youtube.com/embed/BXEVZNUKJ9o';

        // short links
        yield ['https://youtu.be/BXEVZNUKJ9o', $embed];
        yield ['https://youtu.be/BXEVZNUKJ9o?si=8Jc6wFZZw6zQYqD8', $embed];
        yield ['https://www.youtu.be/BXEVZNUKJ9o', $embed];

        // watch links, with and without subdomain, with leading parameters
        yield ['https://www.youtube.com/watch?v=BXEVZNUKJ9o', $embed];
        yield ['https://youtube.com/watch?v=BXEVZNUKJ9o', $embed];
        yield ['https://m.youtube.com/watch?v=BXEVZNUKJ9o', $embed];
        yield ['https://www.youtube.com/watch?app=desktop&v=BXEVZNUKJ9o', $embed];

        // shorts and live
        yield ['https://www.youtube.com/shorts/BXEVZNUKJ9o', $embed];
        yield ['https://www.youtube.com/live/BXEVZNUKJ9o', $embed];

        // already embeddable or foreign hosts must stay untouched
        yield [$embed, $embed];
        yield ['https://tube.switch.ch/embed/UN873PBPxw', 'https://tube.switch.ch/embed/UN873PBPxw'];
        yield [
            'https://zuugs.hfh.ch/h5pgamipresslookbook/wp-admin/admin-ajax.php?action=h5p_embed&id=1',
            'https://zuugs.hfh.ch/h5pgamipresslookbook/wp-admin/admin-ajax.php?action=h5p_embed&id=1'
        ];
    }

    /**
     * @dataProvider provideURLs
     */
    public function testTranslate(string $original, string $expected): void
    {
        $this->assertSame($expected, $this->translator->translate($original));
    }

    protected function setUp(): void
    {
        $this->translator = new URLTranslator();
    }
}
