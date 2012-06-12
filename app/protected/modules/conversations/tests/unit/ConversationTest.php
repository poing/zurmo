<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ConversationTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetConversationById()
        {
            $super                     = User::getByUsername('super');
            $fileModel                 = ZurmoTestHelper::createFileModel();
            $accounts                  = Account::getByName('anAccount');
            $nowStamp                  = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $participant               = UserTestHelper::createBasicUser('steven');

            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $participant;
            $conversationParticipant->hasReadLatest = false;

            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test subject';
            $conversation->description = 'My test description';
            $conversation->conversationItems->add($participant);
            $conversation->files->add($fileModel);
            $conversation->conversationParticipants->add($conversationParticipant);
            $this->assertTrue($conversation->save());
            $id = $conversation->id;
            unset($conversation);

            $conversation = Conversation::getById($id);
            $this->assertEquals('My test subject',        $conversation->subject);
            $this->assertEquals('My test description',    $conversation->description);
            $this->assertEquals($super,                   $conversation->owner);
            $this->assertEquals(1,                        $conversation->conversationItems->count());
            $this->assertEquals($accounts[0],             $conversation->conversationItems->offsetGet(0));
            $this->assertEquals(1,                        $conversation->files->count());
            $this->assertEquals($fileModel,               $conversation->files->offsetGet(0));
            $this->assertEquals($nowStamp,                $conversation->latestDateTime);
            $this->assertEquals(1,                        $conversation->conversationParticipants->count());
            $this->assertEquals($conversationParticipant, $conversation->conversationParticipants->offsetGet(0));
            $this->assertEquals($participant,             $conversation->conversationParticipants->offsetGet(0)->person);
            $this->assertEquals(0,                        $conversation->ownerHasReadLatest);
        }

        public function testAddingComments()
        {
            $conversations = Conversation::getAll();
            $conversation  = $conversations[0];
            $participant   = User::getByUserName('steven');
            $latestStamp   = $conversation->latestDateTime;

            //latestDateTime should not change when just saving the conversation
            $conversation->conversationParticipants->offsetGet(0)->hasReadLatest = true;
            $conversation->ownerHasReadLatest                                    = true;
            $this->assertTrue($conversation->save());
            $this->assertEquals($latestStamp, $conversation->latestDateTime);
            $this->assertEquals(1, $conversation->ownerHasReadLatest);

            //Add comment, this should update the latestDateTime,
            //it should reset hasReadLatest on conversation participants
            $comment              = new Comment();
            $comment->description = 'This is my first comment';
            $conversation->comments->add($comment);
            $this->assertTrue($conversation->save());
            $this->assertNotEquals($latestStamp, $conversation->latestDateTime);
            $this->assertFalse($conversation->conversationParticipants->offsetGet(0)->hasReadLatest);
            //super made the comment, so this should remain the same.
            $this->assertEquals(1, $conversation->ownerHasReadLatest);

            //set it to read latest
            $conversation->conversationParticipants->offsetGet(0)->hasReadLatest = true;
            $this->assertTrue($conversation->save());
            $this->assertTrue($conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            //have steven make the comment. Now the ownerHasReadLatest should set to false, and hasReadLatest should remain true
            Yii::app()->user->userModel = $participant;
            $comment              = new Comment();
            $comment->description = 'This is steven`\s first comment';
            $conversation->comments->add($comment);
            $this->assertTrue($conversation->save());
            $this->assertTrue($conversation->conversationParticipants->offsetGet(0)->hasReadLatest);
            $this->assertEquals(0, $conversation->ownerHasReadLatest);
        }

        public function testResolveConversationParticipantsForExplicitModelPermissions()
        {
            $super                     = User::getByUsername('super');
            $steven                    = User::getByUsername('steven');
            $sally                     = UserTestHelper::createBasicUser('sally');
            $mary                      = UserTestHelper::createBasicUser('mary');

            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test subject2';
            $conversation->description = 'My test description2';
            $this->assertTrue($conversation->save());
            $id = $conversation->id;
            unset($conversation);

            //Set explicitPermissions. Should not add any at this point
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Conversation::getById());
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));

            //Attempt to resolve against posted conversationParticipants data
            $postData = array();
            $postData['itemIds'] = $super->id;
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            //Should still be 0, because super is the owner, and would not be specially added. (This is just a safety test here)
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, $conversation->conversationParticipants->count());

            //Add steven as a conversation participant.
            $conversation              = Conversation::getById($id);
            $postData = array();
            $postData['itemIds'] = $super->id . ',' . $steven->id;
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost($conversation,
                                                                                         $postData,
                                                                                         $explicitReadWriteModelPermissions);
            $this->assertTrue($conversation->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);
            $id = $conversation->id;
            unset($conversation);

            //At this point there should be one readWritePermitable.  "Steven"
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Conversation::getById());
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals($steven, $readWritePermitables[$steven->id]);
            $this->assertEquals(1, $conversation->conversationParticipants->count());
            $this->assertEquals($steven, $conversation->conversationParticipants[0]);
        }

        public function testGetUnreadConversationCount()
        {
            $super                     = User::getByUsername('super');
            $mary                      = User::getByUsername('mary');
            $count                     = Conversation::getUnreadCountByUser($super);
            $this->assertEquals(0, $count);

            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test subject2';
            $conversation->description = 'My test description2';
            $this->assertTrue($conversation->save());

            //when super adds a comment, it should remain same count
            $comment                   = new Comment();
            $comment->description      = 'This is my first comment';
            $conversation->comments->add($comment);
            $this->assertTrue($conversation->save());
            $count                     = Conversation::getUnreadCountByUser($super);
            $this->assertEquals(0, $count);

            //when mary adds a comment, super's count should go up (assumming count was previously 0)
            Yii::app()->user->userModel = $mary;
            $comment                   = new Comment();
            $comment->description      = 'This is mary\'s first comment';
            $conversation->comments->add($comment);
            $this->assertTrue($conversation->save());
            Yii::app()->user->userModel = $super;
            $count                     = Conversation::getUnreadCountByUser($super);
            $this->assertEquals(0, $count);
        }
    }
?>