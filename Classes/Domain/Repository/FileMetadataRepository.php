<?php

namespace Madj2k\CopyrightGuardian\Domain\Repository;
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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class FileMetadataRepository
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FileMetadataRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var \TYPO3\CMS\Core\Database\ConnectionPool|null
     */
    protected ?ConnectionPool $connectionPool = null;


    /**
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool
     * @return void
     */
    public function injectConnectionPool(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * Returns all sys_file records used within given root pid, which are
     * missing at least one required field entry.
     *
     * @param int $rootPageUid
     * @return array
     */
    public function findFilesWithIncompleteMetadataInRootline(
        int $rootPageUid = 1
    ): array
    {

        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $executeCommand = 'executeQuery';
        $orCommand = 'or';
        $andCommand = 'and';

        if ($typo3Version->getMajorVersion() < 12) {
            $executeCommand = 'execute';
            $orCommand = 'orX';
            $andCommand = 'andX';
        }

        $connection = $this->connectionPool->getConnectionForTable('sys_file_metadata');

        $pages = $this->getRecursivePages($rootPageUid);

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select('f.uid AS file_uid', 'f.identifier', 'm.alternative', 'm.tx_copyrightguardian_creator', 's.name AS tx_copyrightguardian_source')
            ->from('sys_file', 'f')
            ->leftJoin(
                'f',
                'sys_file_metadata',
                'm',
                'f.uid = m.file'
            )
            ->leftJoin(
                'f',
                'sys_file_reference',
                'r',
                'f.uid = r.uid_local'
            )
            ->leftJoin(
                'm',
                'tx_copyrightguardian_domain_model_mediasource',
                's',
                'm.tx_copyrightguardian_source = s.uid'
            )
            ->where(
                $queryBuilder->expr()->$andCommand(
                    $queryBuilder->expr()->in('r.pid', $queryBuilder->createNamedParameter($pages, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)),
                    $queryBuilder->expr()->$orCommand(
                        $queryBuilder->expr()->eq('m.alternative', $queryBuilder->createNamedParameter('')),
                        $queryBuilder->expr()->isNull('m.alternative'),
                        $queryBuilder->expr()->eq('m.tx_copyrightguardian_creator', $queryBuilder->createNamedParameter('')),
                        $queryBuilder->expr()->eq('m.tx_copyrightguardian_source', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
                    )
                )
            )
            ->groupby('f.uid');

        return $queryBuilder->$executeCommand()->fetchAllAssociative();
    }

    /**
     * Holt alle Seiten-IDs innerhalb der Rootline einer bestimmten Seite.
     *
     * @param int $rootPageUid
     * @return array
     */
    protected function getRecursivePages(int $rootPageUid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $queryBuilder = $connection->createQueryBuilder();

        // Funktion zur Rekursion
        $pages = [];
        $this->fetchChildPages($rootPageUid, $pages, $queryBuilder);

        return $pages;
    }

    protected function fetchChildPages(int $parentUid, array &$pages, $queryBuilder): void
    {
        // Hole alle Seiten, deren pid der parentUid entspricht
        $result = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->execute()
            ->fetchAllAssociative();

        // Jede Seite durchgehen und rekursiv ihre Kinder abfragen
        foreach ($result as $row) {
            $pages[] = (int)$row['uid'];
            $this->fetchChildPages((int)$row['uid'], $pages, $queryBuilder);
        }
    }
}
