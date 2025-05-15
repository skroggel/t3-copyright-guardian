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
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Class MediaSourceController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo AbstractMediaSourceController can be replaced \TYPO3\CMS\Extbase\Mvc\Controller\ActionController when support for v10 is dropped
 */
class MediaSourceController extends AbstractMediaSourceController
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
     * @throws AspectNotFoundException
     */
    public function listAction(): ResponseInterface
    {
        /** @var int $pid */
        $pid = intval($GLOBALS['TSFE']->id);

        /** @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage $language */
        /** @todo check with method_exists can be removed when support for v10 is dropped */
        $language = (
            (method_exists($this->request, 'getAttribute'))
                ? $this->request->getAttribute('language')
                : $GLOBALS['TSFE']->language
        );
        $languageUid = $language->getLanguageId();

        $mediaSources = $this->mediaSourceRepository->findAllByPage(
            $pid,
            $languageUid,
            $this->settings['resources']['includeFieldsList'] ?? ''
        );

        /** @todo check can be removed when support for v10 is dropped */
        $newsUid = 0;
        if (method_exists($this->request, 'getQueryParams')) {
            $paramArray = $this->request->getQueryParams();
            if (isset($paramArray['tx_news_pi1']['news'])) {
                $newsUid = intval($paramArray['tx_news_pi1']['news']);
            }
        } else {
            $paramArray = GeneralUtility::_GP('tx_news_pi1');
            if (isset($paramArray['news'])) {
                $newsUid = intval($paramArray['news']);
            }
        }

        // add images of current news item if any
        if ($newsUid) {
            $newsMediaSources = $this->mediaSourceRepository->findAllByForeignUid(
                $newsUid,
                $languageUid ,
                $this->settings['resources']['includeFieldsListNews'] ?? ''
            );

            foreach ($newsMediaSources as $newsMediaSource) {
                $mediaSources[] = $newsMediaSource;
            }
        }

        $this->view->assign('mediaSources', $mediaSources);

        return $this->htmlResponse();

    }


    /**
     * Returns a response object with either the given html string or the current rendered view as content.
     *
     * @param string|null $html
     * @return ResponseInterface
     * @todo can be removed when support for v10 is dropped
     */
    protected function htmlResponse(?string $html = null): ResponseInterface
    {
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 11) {
            if ($this->view instanceof ViewInterface) {
                $this->response->appendContent($this->view->render());
            }
        }

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream((string)($html ?? $this->view->render())));
    }


}
