<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

if (getenv('CDN_BASE') === false || getenv('CDN_BASE') === '') {
    return Map::fromEntries();
}

return Map::fromEntries([
    Scope::backend(),
    new MutationCollection(
        new Mutation(
            MutationMode::Extend,
            Directive::DefaultSrc,
            SourceScheme::data,
            new UriValue(sprintf('https://%s', getenv('CDN_BASE'))),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::data,
            new UriValue(sprintf('https://%s', getenv('CDN_BASE'))),
        ),
    ),
]);
