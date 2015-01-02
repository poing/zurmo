<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class StickySearchUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveFinalOffsetForStickyList()
        {
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(0, 10, 100);
            $this->assertEquals(0, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(2, 10, 10);
            $this->assertEquals(0, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(4, 10, 10);
            $this->assertEquals(0, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(4, 10, 20);
            $this->assertEquals(0, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(9, 10, 20);
            $this->assertEquals(4, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(5, 10, 20);
            $this->assertEquals(0, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(9, 10, 12);
            $this->assertEquals(1, $finalOffset);
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList(3, 10, 20);
            $this->assertEquals(0, $finalOffset);
        }

        public function testResolveBreadCrumbViewForDetailsControllerAction()
        {
            TestHelpers::createControllerAndModuleByRoute('accounts/default');
            $controller      = Yii::app()->getController();
            $model           = new Account();

            //Test when there is not a stickyLoadUrl
            $stickySearchKey = 'abc';
            $view            = StickySearchUtil::
                               resolveBreadCrumbViewForDetailsControllerAction($controller, $stickySearchKey, $model);
            $this->assertTrue($view instanceof StickyDetailsAndRelationsBreadCrumbView);
            $content = $view->render();
            $this->assertNotContains('stickyModelId', $content);

            //Test when there is a stickyLoadUrl
            $stickySearchKey = 'abc';
            Yii::app()->user->setState($stickySearchKey, serialize(array('a', 'b')));
            $_GET['stickyOffset'] = '5';
            $view            = StickySearchUtil::
                               resolveBreadCrumbViewForDetailsControllerAction($controller, $stickySearchKey, $model);
            $this->assertTrue($view instanceof StickyDetailsAndRelationsBreadCrumbView);
            $content = $view->render();
            $this->assertContains('stickyListLoadingArea', $content);
        }
    }
?>