<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Copyright Guardian',
	'description' => 'Manages the copyright details of images and other media and offers a convienient way of displaying these automatically and directly on the page on which the media is used. In addition, a list of all media used and the associated copyright information can be output',
	'category' => 'FE',
	'author' => 'Steffen Kroggel',
	'author_email' => 'developer@steffenkroggel.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'clearCacheOnLoad' => 0,
	'version' => '12.4.0',
    'constraints' => [
		'depends' => [
			'typo3' => '10.4.0-12.4.99',
            'filemetadata' => '10.4.0-12.4.99',
        ],
		'conflicts' => [
		],
		'suggests' => [
        ],
	],
];
