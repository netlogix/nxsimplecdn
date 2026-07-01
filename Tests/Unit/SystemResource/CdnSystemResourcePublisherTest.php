<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\Tests\Unit\SystemResource;

use Netlogix\Nxsimplecdn\Service\BaseUriService;
use Netlogix\Nxsimplecdn\SystemResource\CdnSystemResourcePublisher;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Type\PublicResourceInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CdnSystemResourcePublisherTest extends UnitTestCase
{
    private function requestForSite(Site $site, int $applicationType): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getAttribute')
            ->willReturnMap([['applicationType', null, $applicationType], ['site', null, $site]]);

        return $request;
    }

    #[Test]
    public function generateUriRewritesHostToCdnBaseOnFrontendRequest(): void
    {
        $innerPublisher = $this->createMock(SystemResourcePublisherInterface::class);
        $innerPublisher
            ->method('generateUri')
            ->willReturn(new Uri('https://example.com/typo3temp/assets/foo.css'));

        $site = new Site('main', 1, ['base' => 'https://example.com/', 'cdnBase' => 'https://cdn.example.com/']);
        $request = $this->requestForSite($site, SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $publisher = new CdnSystemResourcePublisher($innerPublisher, new BaseUriService(), enabled: true);

        $uri = $publisher->generateUri($this->createStub(PublicResourceInterface::class), $request);

        $this->assertSame('https://cdn.example.com/typo3temp/assets/foo.css', (string) $uri);
    }

    #[Test]
    public function generateUriFallsBackToSiteBaseWhenNoCdnBaseIsConfigured(): void
    {
        $innerPublisher = $this->createMock(SystemResourcePublisherInterface::class);
        $innerPublisher
            ->method('generateUri')
            ->willReturn(new Uri('https://example.com/typo3temp/assets/foo.css'));

        $site = new Site('main', 1, ['base' => 'https://example.com/']);
        $request = $this->requestForSite($site, SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $publisher = new CdnSystemResourcePublisher($innerPublisher, new BaseUriService(), enabled: true);

        $uri = $publisher->generateUri($this->createStub(PublicResourceInterface::class), $request);

        $this->assertSame('https://example.com/typo3temp/assets/foo.css', (string) $uri);
    }

    #[Test]
    public function generateUriLeavesUriUntouchedOnBackendRequest(): void
    {
        $innerPublisher = $this->createMock(SystemResourcePublisherInterface::class);
        $innerPublisher
            ->method('generateUri')
            ->willReturn(new Uri('https://example.com/typo3temp/assets/foo.css'));

        $site = new Site('main', 1, ['base' => 'https://example.com/', 'cdnBase' => 'https://cdn.example.com/']);
        $request = $this->requestForSite($site, SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $publisher = new CdnSystemResourcePublisher($innerPublisher, new BaseUriService(), enabled: true);

        $uri = $publisher->generateUri($this->createStub(PublicResourceInterface::class), $request);

        $this->assertSame('https://example.com/typo3temp/assets/foo.css', (string) $uri);
    }

    #[Test]
    public function generateUriLeavesUriUntouchedWhenDisabled(): void
    {
        $innerPublisher = $this->createMock(SystemResourcePublisherInterface::class);
        $innerPublisher
            ->method('generateUri')
            ->willReturn(new Uri('https://example.com/typo3temp/assets/foo.css'));

        $site = new Site('main', 1, ['base' => 'https://example.com/', 'cdnBase' => 'https://cdn.example.com/']);
        $request = $this->requestForSite($site, SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $publisher = new CdnSystemResourcePublisher($innerPublisher, new BaseUriService(), enabled: false);

        $uri = $publisher->generateUri($this->createStub(PublicResourceInterface::class), $request);

        $this->assertSame('https://example.com/typo3temp/assets/foo.css', (string) $uri);
    }

    #[Test]
    public function generateUriLeavesUriUntouchedWhenRequestIsNull(): void
    {
        $innerPublisher = $this->createMock(SystemResourcePublisherInterface::class);
        $innerPublisher
            ->method('generateUri')
            ->willReturn(new Uri('https://example.com/typo3temp/assets/foo.css'));

        $publisher = new CdnSystemResourcePublisher($innerPublisher, new BaseUriService(), enabled: true);

        $uri = $publisher->generateUri($this->createStub(PublicResourceInterface::class), null);

        $this->assertSame('https://example.com/typo3temp/assets/foo.css', (string) $uri);
    }
}
