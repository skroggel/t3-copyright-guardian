<?php
defined('TYPO3') or die('Access denied.');
call_user_func(
    function (string $extKey) {

        $tempColumnsMedia = [
            'columns' => [
                'tx_copyrightguardian_creator' => [
                    'exclude' => 1,
                    'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_copyrightguardian_creator',
                    'config' => [
                        'type' => 'input',
                        'size' => 20,
                        'eval' => 'trim'
                    ],
                ],
                'tx_copyrightguardian_source' => [
                    'exclude' => 1,
                    'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_copyrightguardian_source',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'size' => 1,
                        'foreign_table' => 'tx_copyrightguardian_domain_model_mediasource',
                        'foreign_table_where' => 'ORDER BY internal DESC, name ASC',
                        'minitems' => 0,
                        'maxitems' => 1,
                        'default' => 0,
                        'items' => [
                            ['---', '0'],
                        ],
                    ],
                ],
            ],
        ];

        // insert columns
        $GLOBALS['TCA']['sys_file_metadata'] = array_replace_recursive($GLOBALS['TCA']['sys_file_metadata'], $tempColumnsMedia);

        // replace default fields with ours
        foreach ($GLOBALS['TCA']['sys_file_metadata']['types'] as $type => &$config) {

            // replace old ones
            foreach (['creator', 'creator_tool', 'publisher', 'source', 'copyright'] as $field) {
                $config = str_replace($field . ',', '', $config);
            }

            // insert new ones
            $config = str_replace(
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,',
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:tabs.metadata,' .
                    implode(',', array_keys($tempColumnsMedia['columns'])) . ',',
                $config
            );
        }

    },
    'copyright_guardian'
);
