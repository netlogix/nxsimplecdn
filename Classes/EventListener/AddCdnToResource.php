<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\EventListener;

use Netlogix\Nxsimplecdn\Service\BaseUriService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AddCdnToResource
{
    protected $configuration = [];

    public function __construct(
        ExtensionConfiguration $extensionConfiguration = null
    ) {
        $this->configuration = $extensionConfiguration ? $extensionConfiguration->get('nxsimplecdn') :
            GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('nxsimplecdn');
    }

    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        if ((bool)$this->configuration['enabled'] === false) {
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

        if (($resource->getStorage()->getCapabilities(
        ) & ResourceStorageInterface::CAPABILITY_PUBLIC) !== ResourceStorageInterface::CAPABILITY_PUBLIC) {
            return;
        }

        $event->setPublicUrl($this->addCdnPrefixToUrl($resource, $driver));
    }

    private function addCdnPrefixToUrl(ResourceInterface $resourceObject, DriverInterface $driver): string
    {
        $publicUrl = $driver->getPublicUrl($resourceObject->getIdentifier());
        $cdnBaseUrl = GeneralUtility::makeInstance(BaseUriService::class)->getBaseUri();
        if (!$resourceObject instanceof ProcessedFile) {
            $publicUrl = GeneralUtility::createVersionNumberedFilename($publicUrl);
        }

        return (string) (new Uri($publicUrl))->withScheme('https')
            ->withHost($cdnBaseUrl->getHost());
    }
}
