<?php
declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\SignalSlot;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ResourceStorage
{

    public function addFileCacheTagForTypolink(array $parameters, ContentObjectRenderer $pObj)
    {
        $linkDetails = $parameters['linkDetails'];
        if (! is_array($linkDetails) || $linkDetails['type'] !== 'file') {
            return;
        }

        /** @var FileInterface $file */
        $file = $linkDetails['file'];
        if (! $file instanceof FileInterface) {
            return;
        }

        $this->getTypoScriptFrontendController()->addCacheTags(['sys_file_' . (int) $file->getProperty('uid')]);
    }

    private function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}
