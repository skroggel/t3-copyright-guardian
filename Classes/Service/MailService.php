<?php
namespace Madj2k\CopyrightGuardian\Service;

use Madj2k\CoreExtended\Utility\GeneralUtility;
use Madj2k\Postmaster\Mail\MailMessage;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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

/**
 * Class MailService
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_CopyrightGuardian
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailService implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Handles password reset event
     *
     * @param string $email
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function incompleteFilesMail(string $email, array $files): void {

        /** @var \Madj2k\Postmaster\Mail\MailMessage $mailMessage */
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);

        // send new user an email with token
        $mailMessage->setTo(['email' => $email], [
            'marker'  => [
                'files' => $files
            ],
        ]);

        $mailMessage->getQueueMail()->setSubject(
            LocalizationUtility::translate(
                'mailService.backendUser.subject.incompleteFiles',
                'copyright_guardian',
                [count($files)],
                'de'
            )
        );

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            $mailMessage->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);

            $mailMessage->getQueueMail()->setPlaintextTemplate('Email/IncompleteFiles');
            $mailMessage->getQueueMail()->setHtmlTemplate('Email/IncompleteFiles');

            if (count($mailMessage->getTo())) {
                $mailMessage->send();
            }
        }
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('CopyrightGuardian', $which);
    }

}
