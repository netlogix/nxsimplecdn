<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\EventListener;

use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\AfterLinkIsGeneratedEvent;

class AddFileCacheTagForTypolink
{
    public function __invoke(AfterLinkIsGeneratedEvent $event): void
    {
        if ($event->getLinkResult()->getType() !== 'file') {
            return;
        }

        if (!array_key_exists('href', $event->getContentObjectRenderer()->parameters)) {
            return;
        }

        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $resolvedLink = $linkService->resolveByStringRepresentation(
            $event
                ->getContentObjectRenderer()
                ->parameters['href']
        );

        if (!array_key_exists('file', $resolvedLink)) {
            return;
        }

        /** @var FileInterface $file */
        $file = $resolvedLink['file'];

        $this->getTypoScriptFrontendController()
            ->addCacheTags(['sys_file_' . (int) $file->getProperty('uid')]);
    }

    private function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
