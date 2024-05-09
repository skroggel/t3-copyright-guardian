<?php
declare(strict_types=1);

namespace Madj2k\CopyrightGuardian\Resource;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Madj2k\CopyrightGuardian\Domain\Repository\MediaSourceRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * FileReference
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CoreExtended
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FileReference extends \TYPO3\CMS\Core\Resource\FileReference
{

    /**
     * Returns the title text to this image
     *
     * @todo Possibly move this to the image domain object instead
     *
     * @return string
     */
    public function getTitle()
    {

        $copyrightInfo = [];

        if (
            ($this->hasProperty('tx_copyrightguardian_creator'))
            && ($creator = $this->getProperty('tx_copyrightguardian_creator'))
        ){
            $copyrightInfo[] = $this->getProperty('tx_copyrightguardian_creator');
        }

        if (
            ($this->hasProperty('tx_copyrightguardian_source'))
            && ($mediaSourceUid = $this->getProperty('tx_copyrightguardian_source'))
        ){

            /** @var \Madj2k\CopyrightGuardian\Domain\Repository\MediaSourceRepository $mediaSourceRepository */
            $mediaSourceRepository = GeneralUtility::makeInstance(MediaSourceRepository::class);

            /** @var \Madj2k\CopyrightGuardian\Domain\Model\MediaSource $mediaSource */
            if ($mediaSource = $mediaSourceRepository->findByUid($mediaSourceUid)) {

                $copyrightInfo[] = $mediaSource->getName();
            }
        }

        $copyrightInfoString = '';
        $title = $this->getProperty('title');
        if (count($copyrightInfo)) {
            $copyrightInfoString = '© ' . implode(' / ', $copyrightInfo);
        }

        if ($copyrightInfoString && $title) {
            return (string)$this->getProperty('title') . ' – ' . $copyrightInfoString;
        }

        if ($copyrightInfoString) {
            return $copyrightInfoString;
        }

        return (string)$this->getProperty('title');
    }

}
