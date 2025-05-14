<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\EventListener;

use Netlogix\Nxsimplecdn\Service\BaseUriService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\Capabilities;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener]
final readonly class AddCdnToResource
{
    public function __construct(
        protected BaseUriService $baseUriService,
        #[
            Autowire(expression: '!!service("extension-configuration").get("nxsimplecdn", "enabled")'),
        ]
        protected bool $enabled = false,
    ) {}

    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        if ($this->enabled === false) {
            return;
        }

        if ($event->getPublicUrl() !== null) {
            return;
        }

        $driver = $event->getDriver();
        $resource = $event->getResource();
        if (
            !($driver instanceof LocalDriver) ||
            !isset($GLOBALS['TYPO3_REQUEST']) ||
            ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
        ) {
            return;
        }

        if (!$resource instanceof File && !$resource instanceof ProcessedFile) {
            return;
        }

        if ($resource->getStorage()->getCapabilities()->hasCapability(Capabilities::CAPABILITY_PUBLIC) === false) {
            return;
        }

        if (
            $resource instanceof File &&
            GeneralUtility::makeInstance(OnlineMediaHelperRegistry::class)->getOnlineMediaHelper($resource) !==
                false
        ) {
            return;
        }

        $event->setPublicUrl($this->addCdnPrefixToUrl($resource, $driver));
    }

    private function addCdnPrefixToUrl(ResourceInterface $resourceObject, DriverInterface $driver): string
    {
        $publicUrl = $driver->getPublicUrl($resourceObject->getIdentifier());
        if (!$resourceObject instanceof ProcessedFile) {
            $publicUrl = GeneralUtility::createVersionNumberedFilename($publicUrl);
        }

        return (string) (new Uri($publicUrl))
            ->withScheme('https')
            ->withHost($this->baseUriService->getBaseUri()->getHost());
    }
}
