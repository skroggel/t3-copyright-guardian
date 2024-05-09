<?php
defined('TYPO3') or die('Access denied.');
call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugins
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'MediaSource',
            [\Madj2k\CopyrightGuardian\Controller\MediaSourceController::class => 'list'],

            // non-cacheable actions
            [\Madj2k\CopyrightGuardian\Controller\MediaSourceController::class => ''],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        //=================================================================
        // XClasses
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\FileReference::class] = [
            'className' => \Madj2k\CopyrightGuardian\Resource\FileReference::class
        ];

    },
    'copyright_guardian'
);


