<?php
defined('TYPO3') or die('Access denied.');
call_user_func(
    function (string $extKey) {

        $pluginConfig = ['media_source'];
        foreach ($pluginConfig as $pluginName) {

            $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
            $version = $typo3Version->getMajorVersion();
            /** @todo remove this if support for v10 is dropped */
            if ($version == 10) {

                // register normal plugin
                $pluginSignature = 'copyrightguardian_mediasource';
                 \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
                    $extKey,
                    \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($pluginName),
                    'CopyrightGuardian: Media Source'
                );

                // add flexform to plugin
                $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                    $pluginSignature, // wildcard when using third parameter, else use pluginSignature
                    'FILE:EXT:' . $extKey . '/Configuration/FlexForms/' .
                    \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($pluginName) . '.xml',
                );

            } else {
                // register normal plugin
                $pluginSignature = \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
                    $extKey,
                    \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($pluginName),
                    'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:plugin.' .
                    \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($pluginName) . '.title'
                );

                // add flexform to plugin
                $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                    '*', // wildcard when using third parameter, else use pluginSignature
                    'FILE:EXT:' . $extKey . '/Configuration/FlexForms/' .
                    \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($pluginName) . '.xml',
                    $pluginSignature // third parameter adds flexform to content-element below, too!
                );

                // add content element
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                    'tt_content',
                    'CType',
                    [
                        'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:plugin.' .
                            TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($pluginName) . '.title',
                        'value' => $pluginSignature,
                        'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/Extension.svg',
                        'group' => $extKey,
                    ]
                );

                // define TCA-fields
                // $GLOBALS['TCA']['tt_content']['types'][$pluginSignature] = $GLOBALS['TCA']['tt_content']['types']['list'];
                $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;general,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
                    pi_flexform,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    rowDescription,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ';
            }
        }
    },
    'copyright_guardian'
);
