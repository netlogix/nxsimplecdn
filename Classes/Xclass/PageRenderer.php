<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\Xclass;

use Netlogix\Nxsimplecdn\Service\BaseUriService;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRenderer extends \TYPO3\CMS\Core\Page\PageRenderer
{
    protected function getAbsoluteWebPath(string $file): string
    {
        $file = parent::getAbsoluteWebPath($file);

        if ($this->getApplicationType() === 'BE') {
            return $file;
        }

        $baseUri = GeneralUtility::makeInstance(BaseUriService::class)
            ->getBaseUri();

        return (string) (new Uri($file))
            ->withScheme('https')
            ->withHost($baseUri->getHost());
    }
}
