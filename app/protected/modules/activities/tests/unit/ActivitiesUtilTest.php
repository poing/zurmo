<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ActivitiesUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            $jane = UserTestHelper::createBasicUser('jane');
            UserTestHelper::createBasicUser('sally');
            UserTestHelper::createBasicUser('jason');
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            EmailBoxUtil::createBoxAndDefaultFoldersByUserAndName($jane, 'JaneBox');
        }

        /**
         * Testing that each model type can render properly and does not throw an exception
         */
        public function testRenderSummaryContentWithEmailMessage()
        {
            $super                      = User::getByUsername('super');
            $billy                      = User::getByUsername('billy');
            $this->assertEquals(0, count(EmailMessage::getAll()));

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = BaseControlUserConfigUtil::getUserToRunAs();
            $emailMessage->subject = 'My First Email';
            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;

            //Sending from the system, does not have a 'person'.
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'system@somewhere.com';
            $sender->fromName          = 'Zurmo System';
            $sender->personsOrAccounts->add($super);
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($billy);
            $emailMessage->recipients->add($recipient);
            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            $saved = $emailMessage->save();
            $this->assertTrue($saved);

            $content = ActivitiesUtil::renderSummaryContent($emailMessage,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($emailMessage,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($emailMessage,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'UserModule');
            $content = ActivitiesUtil::renderSummaryContent($emailMessage,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'UserModule');
        }

        public function testRenderSummaryContentWithTask()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $account                    = AccountTestHelper::createAccountByNameForOwner('taskAccount', $super);
            $task                       = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('aTask', $super, $account);

            $content = ActivitiesUtil::renderSummaryContent($task,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($task,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($task,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'UserModule');
            $content = ActivitiesUtil::renderSummaryContent($task,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'UserModule');
        }

        public function testRenderSummaryContentWithMeeting()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $account                    = AccountTestHelper::createAccountByNameForOwner('meetingAccount', $super);
            $meeting                    = MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('aMeeting', $super, $account);

            $content = ActivitiesUtil::renderSummaryContent($meeting,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($meeting,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($meeting,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'UserModule');
            $content = ActivitiesUtil::renderSummaryContent($meeting,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'UserModule');
        }

        public function testRenderSummaryContentWithNote()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $account                    = AccountTestHelper::createAccountByNameForOwner('noteAccount', $super);
            $note                       = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('aMeeting', $super, $account);

            $content = ActivitiesUtil::renderSummaryContent($note,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($note,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'HomeModule');
            $content = ActivitiesUtil::renderSummaryContent($note,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'UserModule');
            $content = ActivitiesUtil::renderSummaryContent($note,
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER,
                                                            'UserModule');
        }
    }