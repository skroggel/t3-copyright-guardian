<?php
declare(strict_types=1);
namespace Madj2k\CopyrightGuardian\Controller;

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
use Madj2k\CoreExtended\Utility\GeneralUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Class AbstractMediaSourceController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated since v11
 * @todo can be removed when support for v10 is dropped
 */
$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
$version = $typo3Version->getMajorVersion();
if ($version <= 10) {
    abstract class AbstractMediaSourceController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
    {
        /**
         * @var \Psr\Http\Message\ResponseFactoryInterface
         * @todo can be removed when support for v10 is dropped
         */
        protected $responseFactory;


        /**
         * @var \Psr\Http\Message\StreamFactoryInterface
         * @todo can be removed when support for v10 is dropped
         */
        protected $streamFactory;


        /**
         * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
         * @return void
         * @todo can be removed when support for v10 is dropped
         */
        public function injectResponseFactoryForV10(ResponseFactoryInterface $responseFactory)
        {
            $this->responseFactory = $responseFactory;
        }


        /**
         * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
         * @return void
         * @todo can be removed when support for v10 is dropped
         */
        public function injectStreamFactoryForV10(StreamFactoryInterface $streamFactory)
        {
            $this->streamFactory = $streamFactory;
        }
    }
} else {
    abstract class AbstractMediaSourceController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    }
}
