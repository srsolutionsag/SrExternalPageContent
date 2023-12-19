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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use srag\Plugins\SrExternalPageContent\Whitelist\DomainParser;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepository;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistedDomain;

require_once __DIR__ . '/../../vendor/autoload.php';

class CheckTest extends TestCase
{
    private ?DomainParser $parser = null;
    /**
     * @var object&MockObject|MockObject|WhitelistRepository|WhitelistRepository&object&MockObject|WhitelistRepository&MockObject
     */
    private ?WhitelistRepository $repository = null;
    private ?Check $check = null;

    protected function setUp(): void
    {
        $this->parser = new DomainParser();
        $this->repository = $this->createMock(WhitelistRepository::class);
        $this->check = new Check($this->repository, $this->parser);
    }

    public function testMatch(): void
    {
        $this->repository->expects($this->once())
                         ->method('getPossibleMatches')
                         ->with('youtube.com')
                         ->willReturn([
                             new WhitelistedDomain(1, '*.youtube.com', 'YouTube', 'YouTube'),
                         ]);

        $this->assertTrue(
            $this->check->isAllowed('https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2')
        );
    }

    public function testNoMatch(): void
    {
        $this->repository->expects($this->once())
                         ->method('getPossibleMatches')
                         ->with('youtube.com')
                         ->willReturn([
                             new WhitelistedDomain(1, 'download.youtube.com'),
                             new WhitelistedDomain(2, 'video.youtube.com'),
                             new WhitelistedDomain(2, 'play.youtube.com'),
                         ]);

        $this->assertFalse(
            $this->check->isAllowed('https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2')
        );
    }

    public function testEverytingIsAllowed(): void
    {
        $this->repository->expects($this->once())
                         ->method('getPossibleMatches')
                         ->with('youtube.com')
                         ->willReturn([
                             new WhitelistedDomain(1, '*'),
                         ]);

        $this->assertTrue(
            $this->check->isAllowed('https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2')
        );
    }

    public function testEverytingFromComIsAllowed(): void
    {
        $this->repository->expects($this->once())
                         ->method('getPossibleMatches')
                         ->with('youtube.com')
                         ->willReturn([
                             new WhitelistedDomain(1, '*.com'),
                         ]);

        $this->assertTrue(
            $this->check->isAllowed('https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2')
        );
    }

    public function testNothingIsAllowed(): void
    {
        $this->repository->expects($this->once())
                         ->method('getPossibleMatches')
                         ->with('youtube.com')
                         ->willReturn([]);

        $this->assertFalse(
            $this->check->isAllowed('https://www.youtube.com/embed/ZXiM9dqcOHI?si=o2TmeJm925khPpZ2')
        );
    }
}
