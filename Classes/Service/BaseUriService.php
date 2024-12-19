<?php

declare(strict_types=1);

namespace Netlogix\Nxsimplecdn\Service;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;

final class BaseUriService
{
    public function getBaseUri(): UriInterface
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

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
