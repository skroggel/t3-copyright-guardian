<?php
namespace Madj2k\CopyrightGuardian\Command;

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

use Madj2k\CopyrightGuardian\Domain\Repository\FileMetadataRepository;
use Madj2k\CopyrightGuardian\Service\MailService;
use Madj2k\CoreExtended\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CheckFileMetadataCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 copyright_guardian:checkFileMetadata'
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CheckFileMetadataCommand extends Command
{

    /**
     * @var \Madj2k\CopyrightGuardian\Repository\FileMetadataRepository|null
     */
    private ?FileMetadataRepository $fileMetadataRepository = null;


    public function __construct(FileMetadataRepository $fileMetadataRepository)
    {
        parent::__construct();
        $this->fileMetadataRepository = $fileMetadataRepository;
    }


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Gets all sys_file-Datensätze, which are missing at least one specific attribute in sys_file_metadata.')
            ->addOption(
                'rootPageUid',
                'r',
                InputOption::VALUE_REQUIRED,
                'Defines the root page as search scope.',
                1
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'Defines the email the report should be sent to.',
                ''
            );
    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $io->newLine();

        $rootPageUid = (int) $input->getOption('rootPageUid');

        $files = $this->fileMetadataRepository->findFilesWithIncompleteMetadataInRootline($rootPageUid);

        if (empty($files)) {
            $io->success('No records with missing meta data found.');
        } else {
            $io->title('Files with incomplete meta data');

            $io->table(
                [
                    'uid',
                    'identifier',
                    'alternative',
                    'tx_copyrightguardian_creator',
                    'tx_copyrightguardian_source'
                ],
                array_map(static function ($file) {
                    return [
                        $file['file_uid'],
                        $file['identifier'],
                        $file['alternative'] ?? '',
                        $file['tx_copyrightguardian_creator'] ?? '',
                        $file['tx_copyrightguardian_source'] ?? '',
                    ];
                }, $files)
            );

            if ($email = $input->getOption('email')) {
                // send mails
                /** @var MailService $mailService */
                $mailService = GeneralUtility::makeInstance(MailService::class);
                $mailService->incompleteFilesMail($email, $files);
            }

            $io->success(sprintf('%d files found.', count($files)));
        }

        return Command::SUCCESS;
    }
}
