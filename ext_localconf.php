<?php
defined('TYPO3_MODE') or die();

(function () {
    if (TYPO3_MODE == 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc']['tx_nxsimplecdn'] = \Netlogix\Nxsimplecdn\SignalSlot\ResourceStorage::class . '->addFileCacheTagForTypolink';
    }
})();
