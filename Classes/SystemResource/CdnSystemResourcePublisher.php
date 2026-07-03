<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\SystemResource;

use Netlogix\Nxsimplecdn\Service\BaseUriService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\Type\PublicResourceInterface;

final readonly class CdnSystemResourcePublisher implements SystemResourcePublisherInterface
{
    public function __construct(
        private SystemResourcePublisherInterface $innerPublisher,
        private BaseUriService $baseUriService,
        #[
            Autowire(expression: '!!service("extension-configuration").get("nxsimplecdn", "enabled")'),
        ]
        private bool $enabled = false,
    ) {}

    public function publishResources(PackageInterface $package): FlashMessageQueue
    {
        return $this->innerPublisher->publishResources($package);
    }

    public function generateUri(
        PublicResourceInterface $publicResource,
        ?ServerRequestInterface $request,
        ?UriGenerationOptions $options = null,
    ): UriInterface {
        $uri = $this->innerPublisher->generateUri($publicResource, $request, $options);

        if (
            $this->enabled === false ||
            !($request instanceof ServerRequestInterface) ||
            ApplicationType::fromRequest($request)->isBackend()
        ) {
            return $uri;
        }

        return $uri->withScheme('https')->withHost($this->baseUriService->getBaseUri($request)->getHost());
    }
}
