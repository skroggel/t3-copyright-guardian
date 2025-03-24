<?php
defined('TYPO3') or die('Access denied.');
call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugins
        //=================================================================
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $version = $typo3Version->getMajorVersion();
        /** @todo remove this if support for v10 is dropped */
        if ($version == 10) {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'Madj2k.CopyrightGuardian',
                'MediaSource',
                ['MediaSource' => 'list'],

                // non-cacheable actions
                [],
            //\TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
            );

        } else {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                $extKey,
                'MediaSource',
                [\Madj2k\CopyrightGuardian\Controller\MediaSourceController::class => 'list'],

                // non-cacheable actions
                [\Madj2k\CopyrightGuardian\Controller\MediaSourceController::class => ''],
                \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
            );
        }

        //=================================================================
        // XClasses
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\FileReference::class] = [
            'className' => \Madj2k\CopyrightGuardian\Resource\FileReference::class
        ];

    },
    'copyright_guardian'
);


