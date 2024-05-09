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
use Psr\Http\Message\ResponseInterface;

/**
 * Class MediaSourceController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MediaSourceController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \Madj2k\CopyrightGuardian\Domain\Repository\MediaSourceRepository|null
     */
    protected ?MediaSourceRepository $mediaSourceRepository = null;


    /**
     * @param \Madj2k\CopyrightGuardian\Domain\Repository\MediaSourceRepository $mediaSourceRepository
     */
    public function injectMediaSourceRepository(MediaSourceRepository $mediaSourceRepository)
    {
        $this->mediaSourceRepository = $mediaSourceRepository;
    }


    /**
     * shows resources of current page
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        /** @var int $pid */
        $pid = $GLOBALS['TSFE']->id;

        /** @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage $language */
        $language = $this->request->getAttribute('language');

        $mediaSources = $this->mediaSourceRepository->findAllByPage(
            $pid,
            $language->getLanguageId(),
            $this->settings['resources']['includeFieldsList'] ?? ''
        );

        // add images of current news item if any
        if ($newsUid = intval($this->request->getQueryParams()['tx_news_pi1']['news'])) {
            $newsMediaSources = $this->mediaSourceRepository->findAllByForeignUid(
                $newsUid,
                $language->getLanguageId(),
                $this->settings['resources']['includeFieldsListNews'] ?? ''
            );

            foreach ($newsMediaSources as $newsMediaSource) {
                $mediaSources[] = $newsMediaSource;
            }
        }

        $this->view->assign('mediaSources', $mediaSources);
        return $this->htmlResponse();

    }

}
