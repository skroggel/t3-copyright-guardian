<?php
declare(strict_types=1);
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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class MediaSourceRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MediaSourceRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @var array
     */
    protected array $rootline = [];

    
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
     * Finds all used media on a given page
     *
     * @param int $pid
     * @param int $languageUid
     * @param string $tableFields
     * @return array
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function findAllByPage(
        int $pid,
        int $languageUid = 0,
        string $tableFields = 'pages.media, tt_content.image, tt_content.assets'
    ): array {

        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $executeCommand = 'executeQuery';
        $orCommand = 'or';
        $andCommand = 'and';

        if ($typo3Version->getMajorVersion() < 12) {
            $executeCommand = 'execute';
            $orCommand = 'orX';
            $andCommand = 'andX';
        }

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file_reference');

        if ($tableFieldExpression = $this->buildTableFieldExpressionForPage($queryBuilder, $pid, $tableFields)) {

            $this->buildBasicQuery($queryBuilder, $tableFieldExpression, $languageUid);

            // make sure the corresponding pages and contents are not hidden or deleted
            $queryBuilder->andWhere(
                $queryBuilder->expr()->$orCommand(
                    $queryBuilder->expr()->$andCommand(
                        $queryBuilder->expr()->eq(
                            'content.hidden',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'content.deleted',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                    ),
                    $queryBuilder->expr()->$andCommand(
                        $queryBuilder->expr()->eq(
                            'pages.hidden',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'pages.deleted',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                    )
                ),
            );

            // for the andWhere-checks to work, we need the tables joined
            $queryBuilder->leftJoin(
                'ref',
                'tt_content',
                'content',
                (string) $queryBuilder->expr()->$andCommand(
                    $queryBuilder->expr()->eq(
                        'content.uid',
                        $queryBuilder->quoteIdentifier('ref.uid_foreign')
                    ),
                    $queryBuilder->expr()->eq(
                        'ref.tablenames',
                        $queryBuilder->createNamedParameter('tt_content', Connection::PARAM_STR)
                    ),
                )
            )->leftJoin(
                'ref',
                'pages',
                'pages',
                (string) $queryBuilder->expr()->$andCommand(
                    $queryBuilder->expr()->eq(
                        'pages.uid',
                        $queryBuilder->quoteIdentifier('ref.uid_foreign')
                    ),
                    $queryBuilder->expr()->eq(
                        'ref.tablenames',
                        $queryBuilder->createNamedParameter('pages', Connection::PARAM_STR)
                    ),
                )
            );

            $result = $queryBuilder->$executeCommand();
            return $result->fetchAllAssociative();
        }

        return [];
    }


    /**
     * Finds all used media for a given foreign uid
     *
     * @param int $foreignUid
     * @param int $languageUid
     * @param string $tableFields
     * @return array
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function findAllByForeignUid(
        int $foreignUid,
        int $languageUid,
        string $tableFields = 'tx_news_domain_model_news.fal_media'
    ): array {

        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $executeCommand = 'executeQuery';
        if ($typo3Version->getMajorVersion() < 12) {
            $executeCommand = 'execute';
        }

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file_reference');

        if ($tableFieldExpression = $this->buildTableFieldExpressionForForeignUid($queryBuilder, $foreignUid, $tableFields)) {

            $this->buildBasicQuery($queryBuilder, $tableFieldExpression, $languageUid);
            $result = $queryBuilder->$executeCommand();
            return $result->fetchAllAssociative();
        }

        return [];
    }


    /**
     * Builds constrains for relevant table.fields including a check for rootline-fields
     *
     * @param \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder
     * @param int $foreignUid
     * @param string $tableFields
     * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression|\Doctrine\DBAL\Query\Expression\CompositeExpression
     * @todo return-value can be set explicitly when support for v10 is dropped
     */
    protected function buildTableFieldExpressionForForeignUid(QueryBuilder $queryBuilder, int $foreignUid, string $tableFields)
    {
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $andCommand = 'and';

        if ($typo3Version->getMajorVersion() < 12) {
            $andCommand = 'andX';
        }

        // split string list into separate page.field-parts
        $tableFieldsSplitted = GeneralUtility::trimExplode(',', $tableFields, true);

        /** @var \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression $tableFieldExpression */
        $tableFieldExpression = CompositeExpression::or(null);

        // add selected table.field combinations to constraint
        // therefore we have to split the table from the field
        foreach ($tableFieldsSplitted as $tableField) {
            list($table, $field) = GeneralUtility::trimExplode('.', $tableField, true);

            $tableFieldExpression = $tableFieldExpression->with(
                $queryBuilder->expr()->$andCommand(
                    $queryBuilder->expr()->eq(
                        'ref.uid_foreign',
                        $queryBuilder->createNamedParameter($foreignUid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter($table, Connection::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter($field, Connection::PARAM_STR)
                    )
                )
            );
        }

        return $tableFieldExpression;
    }


    /**
     * Builds constrains for relevant table.fields including a check for rootline-fields
     *
     * @param \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder
     * @param int $pid
     * @param string $tableFields
     * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression|\Doctrine\DBAL\Query\Expression\CompositeExpression
     * @todo return-value can be set explicitly when support for v10 is dropped
     */
    protected function buildTableFieldExpressionForPage(QueryBuilder $queryBuilder, int $pid, string $tableFields)
    {
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $andCommand = 'and';

        if ($typo3Version->getMajorVersion() < 12) {
            $andCommand = 'andX';
        }

        $this->rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pid)->get();

        // split string list into separate page.field-parts
        $tableFieldsSplitted = GeneralUtility::trimExplode(',', $tableFields, true);

        /** @var \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression $tableFieldExpression */
        $tableFieldExpression = CompositeExpression::or(null);

        // add selected table.field combinations to constraint
        // therefore we have to split the table from the field
        foreach ($tableFieldsSplitted as $tableField) {
            list($table, $field) = GeneralUtility::trimExplode('.', $tableField, true);

            // check if a rootline fetch is needed
            if ($rootlinePages = $this->getRootlinePages($field)) {
                $pidConstraint = $queryBuilder->expr()->in(
                    'ref.pid',
                    $rootlinePages
                );
            } else {
                $pidConstraint = $queryBuilder->expr()->eq(
                    'ref.pid',
                    $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
                );
            }

            $tableFieldExpression = $tableFieldExpression->with(
                $queryBuilder->expr()->$andCommand(
                    $pidConstraint,
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter($table, Connection::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter($field, Connection::PARAM_STR)
                    )
                )
            );
        }

        return $tableFieldExpression;
    }


    /**
     * Get relevant rootline pages for the fetch
     *
     * @param string $field
     * @return array
     */
    protected function getRootlinePages(string $field): array
    {
        $rootlinePages = [];
        foreach ($this->rootline as $page) {

            if (isset($page[$field])) {
                $rootlinePages[] = $page['uid'];
            }

            if (! empty($page[$field])) {
                break;
            }
        }

        return $rootlinePages;
    }


    /**
     * Build the basic query around the constraints for given table.field definitions
     *
     * @param \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder
     * @param \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression|\Doctrine\DBAL\Query\Expression\CompositeExpression $tableFieldExpression
     * @param int $languageUid
     * @return void
     * @todo type-hint for second parameter can be set explicitly when support for v10 is dropped
     */
    protected function buildBasicQuery (QueryBuilder $queryBuilder, $tableFieldExpression, int $languageUid = 0): void
    {

        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
        $orCommand = 'or';
        $andCommand = 'and';

        if ($typo3Version->getMajorVersion() < 12) {
            $orCommand = 'orX';
            $andCommand = 'andX';
        }

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder->select(
            'ref.uid as refUid', 'ref.pid as refPid', 'ref.tablenames as refTablenames', 'ref.fieldname as refFieldname', 'ref.title as refTitle', 'ref.description as refDescription', 'ref.alternative as refAlternative',
            'file.name as fileName', 'file.extension as fileExtension', 'file.size as fileSize', 'file.mime_type as fileMimeType',
            'meta.title as metaTitle', 'meta.description as metaDescription', 'meta.alternative as metaAlternative', 'meta.tx_copyrightguardian_creator as metaCreator',
            'mediaSource.name as mediaSourceName', 'mediaSource.url as mediaSourceUrl', 'mediaSource.internal as mediaSourceInternal',
        )
            ->from('sys_file_reference', 'ref')
            ->where(
                $queryBuilder->expr()->$andCommand(
                    $queryBuilder->expr()->eq(
                        'ref.sys_language_uid',
                        $queryBuilder->createNamedParameter($languageUid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'ref.tx_copyrightguardian_images_no_copyright',
                        $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'ref.tx_copyrightguardian_images_no_copyright',
                        $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->$orCommand(
                        $queryBuilder->expr()->neq(
                            'meta.tx_copyrightguardian_creator',
                            $queryBuilder->createNamedParameter('', Connection::PARAM_STR)
                        ),
                        $queryBuilder->expr()->gt(
                            'meta.tx_copyrightguardian_source',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                    ),
                    $tableFieldExpression,
                )
            )->innerJoin(
                'ref',
                'sys_file',
                'file',
                $queryBuilder->expr()->eq(
                    'file.uid',
                    $queryBuilder->quoteIdentifier('ref.uid_local')
                )
            )->innerJoin(
                'ref',
                'sys_file_metadata',
                'meta',
                $queryBuilder->expr()->eq(
                    'meta.file',
                    $queryBuilder->quoteIdentifier('file.uid')
                )
            )->leftJoin(
                 'ref',
                 'tx_copyrightguardian_domain_model_mediasource',
                 'mediaSource',
                 $queryBuilder->expr()->eq(
                     'mediaSource.uid',
                     $queryBuilder->quoteIdentifier('meta.tx_copyrightguardian_source')
                 )
            )->orderBy('ref.pid', 'ASC');

    }


}
