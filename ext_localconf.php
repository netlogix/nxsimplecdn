<?php

declare(strict_types=1);

use Netlogix\Nxsimplecdn\Xclass\PageRenderer;

defined('TYPO3') || die();

(static function (): void {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\Page\PageRenderer::class]['className'] =
        PageRenderer::class;
})();
