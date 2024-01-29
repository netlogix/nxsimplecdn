<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\EventListener;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AddCdnToResource
{
    public function __invoke(GeneratePublicUrlForResourceEvent $event): void
    {
        $driver = $event->getDriver();
        $resource = $event->getResource();
        if (
            !($driver instanceof LocalDriver) ||
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
        $cdnBaseUrl = $this->getCdnBase();
        if (!$resourceObject instanceof ProcessedFile) {
            $publicUrl = GeneralUtility::createVersionNumberedFilename($publicUrl);
        }

        return (string) (new Uri($publicUrl))->withScheme('https')
            ->withHost($cdnBaseUrl->getHost());
    }

    private function getCdnBase(): UriInterface
    {
        $request = $this->getRequest();
        $site = $request->getAttribute('site');
        assert($site instanceof Site);

        try {
            return new Uri($site->getAttribute('cdnBase'));
        } catch (InvalidArgumentException) {
            return $site->getBase();
        }
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
