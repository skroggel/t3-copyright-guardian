<?php
declare(strict_types=1);
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$iconList = [];
foreach (
    [
        'copyrightguardian-plugin-mediasource' => 'Extension.svg',
    ] as $identifier => $path) {
    $iconList[$identifier] = [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:copyright_guardian/Resources/Public/Icons/' . $path,
    ];
}

return $iconList;
