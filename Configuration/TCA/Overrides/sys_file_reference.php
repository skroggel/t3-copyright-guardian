<?php
defined('TYPO3') or die('Access denied.');
call_user_func(
    function (string $extKey) {

        /*
        // field is currently not saved in backend and thus commented out
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference',
            [

                'tx_copyrightguardian_images_no_copyright' => [
                    'exclude' => 0,
                    'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:sys_file_reference.tx_copyrightguardian_images_no_copyright',
                    'config' => [
                        'type' => 'check',
                        'default' => 0,
                         // @todo can be completely removed if support for v11 is dropped
                        'items' =>  ($version <= 11 ?
                            [
                                [
                                    'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:sys_file_reference.tx_copyrightguardian_images_no_copyright.I.disabled',
                                    '0'
                                ],
                            ]:
                            [
                                [
                                    'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:sys_file_reference.tx_copyrightguardian_images_no_copyright.I.disabled',
                                    'value' => '0'
                                ]
                            ]
                        ),
                    ],
                ],
            ]
        );

        // Add fields
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            'sys_file_reference',
            'imageoverlayPalette',
            '--linebreak--,tx_copyrightguardian_images_no_copyright',
            'after:title');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            'sys_file_reference',
            'videoOverlayPalette',
            '--linebreak--,tx_copyrightguardian_images_no_copyright',
            'after:title');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            'sys_file_reference',
            'audioOverlayPalette',
            '--linebreak--,tx_copyrightguardian_images_no_copyright',
            'after:title');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            'sys_file_reference',
            'filePalette',
            '--linebreak--,tx_copyrightguardian_images_no_copyright',
            'after:title');
        */
    },
    'copyright_guardian'
);
