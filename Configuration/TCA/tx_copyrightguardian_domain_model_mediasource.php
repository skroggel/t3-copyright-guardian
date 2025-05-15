<?php
$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
$version = $typo3Version->getMajorVersion();

/** @todo can be completely removed if support for v12 is dropped */
$additionalFieldDefinitions = [];
if ($version <= 12) {
    $additionalFieldDefinitions = [
        'sys_language_uid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0],
                ],
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_copyrightguardian_domain_model_mediasource',
                'foreign_table_where' => 'AND tx_copyrightguardian_domain_model_mediasource.pid=###CURRENT_PID### AND tx_copyrightguardian_domain_model_mediasource.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            /** @todo can be completely removed if support for v11 is dropped */
            'config' => ($version < 12 ?
                [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 13,
                    'eval' => 'datetime',
                    'checkbox' => 0,
                    'default' => 0,
                    'range' => [
                        'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true
                    ]
                ]:
                [
                    'type' => 'datetime',
                    'format' => 'date',
                    'default' => 0,
                ]
            )
        ],
        'endtime' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            /** @todo can be completely removed if support for v11 is dropped */
            'config' => ($version < 12 ?
                [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 13,
                    'eval' => 'datetime',
                    'checkbox' => 0,
                    'default' => 0,
                    'range' => [
                        'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true
                    ]
                ]:
                [
                    'type' => 'datetime',
                    'format' => 'date',
                    'default' => 0,
                ]
            )
        ],
    ];
}

return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'default_sortby' => 'ORDER BY internal DESC, name ASC',
		'versioningWS' => true,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => 'name,url,',
		'iconfile' => 'EXT:copyright_guardian/Resources/Public/Icons/tx_copyrightguardian_domain_model_mediasource.gif'
	],
	'types' => [
		'1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, --palette--;;1, name, url, internal, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => array_merge($additionalFieldDefinitions, [
		't3ver_label' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			],
		],
		'name' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource.name',
			'config' => [
				'type' => 'input',
				'size' => 30,
                /** @todo can be completely removed if support for v12 is dropped */
				'eval' => ($version <= 12 ? 'trim,required' : 'trim'),
                'required' => true
			],
		],
		'url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource.url',
            /** @todo can be completely removed if support for v11 is dropped */
            'config' => ($version <= 11 ?
                [
                    'type' => 'input',
                    'renderType' => 'inputLink',
			    ]:
                [
                    'type' => 'link',
                ]
            ),
		],
        'internal' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource.internal',
            'config' => [
                'type' => 'check',
                'default' => 0,
                /** @todo can be completely removed if support for v11 is dropped */
                'items' => ($version <= 11 ?
                    [
                        '1' => [
                            'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource.internal.I.disabled',
                            0
                        ],
                    ]:
                    [
                        [
                            'label' => 'LLL:EXT:copyright_guardian/Resources/Private/Language/locallang_db.xlf:tx_copyrightguardian_domain_model_mediasource.internal.I.disabled',
                            'value' => '0'
                        ]
                    ]
                ),
            ],
        ],
	]),
];
