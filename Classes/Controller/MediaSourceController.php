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


    /**
     * shows resources of current page
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws AspectNotFoundException
     */
    public function listAction(): ResponseInterface
    {
        /** @var int $pid */
        $pid = $GLOBALS['TSFE']->id;

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

        /** @todo check with method_exists can be removed when support for v10 is dropped */
        $newsUid = (
            (method_exists($this->request, 'getQueryParams'))
                ? intval($this->request->getQueryParams()['tx_news_pi1']['news'])
                : GeneralUtility::_GP('tx_news_pi1')['news']
        );

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
     * @todo can be removed when support for v10 is dropped
     */
    protected function htmlResponse(string $html = null): ResponseInterface
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
