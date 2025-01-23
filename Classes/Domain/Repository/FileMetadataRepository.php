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

use TYPO3\CMS\Core\Database\ConnectionPool;

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
     * Gibt alle sys_file-Datensätze zurück, die in sys_file_metadata mindestens eine der Bedingungen erfüllen.
     *
     * @return array
     */
    public function findFilesWithIncompleteMetadata(): array
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

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select('f.uid AS file_uid', 'f.identifier', 'm.alternative', 'm.tx_copyrightguardian_creator', 'm.tx_copyrightguardian_source')
            ->from('sys_file', 'f')
            ->leftJoin(
                'f',
                'sys_file_metadata',
                'm',
                'f.uid = m.file'
            )
            ->where(
                $queryBuilder->expr()->$orCommand(
                    $queryBuilder->expr()->eq('m.alternative', $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull('m.alternative'),
                    $queryBuilder->expr()->eq('m.tx_copyrightguardian_creator', $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->eq('m.tx_copyrightguardian_source', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
                )
            );

        return $queryBuilder->$executeCommand()->fetchAllAssociative();
    }
}
