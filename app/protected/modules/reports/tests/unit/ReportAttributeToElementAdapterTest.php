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

    class ReportAttributeToElementAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
            //Need to instantiate a controller so the clipWidget can work properly in elements that utilize it.
            $controller                  = Yii::app()->createController('reports/default');
            list($controller, $actionId) = $controller;
            Yii::app()->setController($controller);

            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            $values = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestMultiDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            $values = array(
                'Radio 1',
                'Radio 2',
                'Radio 3',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestRadioDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            $values = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestTagCloud');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard
        }

        public function setup()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetFilterContentForRowsAndColumns()
        {
            $inputPrefixData      = array('some', 'prefix');
            $reportType           = Report::TYPE_ROWS_AND_COLUMNS;
            $modelClassName       = 'ReportModelTestItem';
            $treeType             = ReportRelationsAndAttributesTreeView::TREE_TYPE_FILTERS;
            $model                = new FilterForReportForm($modelClassName, $reportType);
            $form                 = new ZurmoActiveForm();

            //Test a boolean attribute which does not have an operator
            $model->attributeIndexOrDerivedType = 'boolean';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertTrue(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a currencyValue attribute
            $model->attributeIndexOrDerivedType = 'currencyValue';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, '"some[prefix][currencyIdForValue]"') === false);

            //Test a date attribute which does not have an operator but has a valueType
            $model->attributeIndexOrDerivedType = 'date';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertTrue(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][valueType]"')          === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a dateTime
            $model->attributeIndexOrDerivedType = 'dateTime';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertTrue(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][valueType]"')          === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a dropDown attribute with the operator set to multiple
            $model->attributeIndexOrDerivedType = 'dropDown';
            $model->operator                    = 'oneOf';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value][]"')            === false);
            $this->assertTrue(strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, 'multiple="multiple"') === false);
            //Test a dropDown attribute with the operator set to null;
            $model->operator                    = null;
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content,  '"some[prefix][value]"')              === false);
            $this->assertTrue (strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content,  '"some[prefix][availableAtRunTime]"') === false);
            $this->assertTrue (strpos($content,  'multiple="multiple"') === false);

            //Test a float attribute
            $model->attributeIndexOrDerivedType = 'float';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a integer attribute
            $model->attributeIndexOrDerivedType = 'integer';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a multiDropDown attribute with the operator set to multiple
            $model->attributeIndexOrDerivedType = 'multiDropDown';
            $model->operator                    = 'oneOf';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value][]"')            === false);
            $this->assertTrue(strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, 'multiple="multiple"') === false);
            //Test a multiDropDown attribute with the operator set to null;
            $model->operator                    = null;
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content,  '"some[prefix][value]"')              === false);
            $this->assertTrue (strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content,  '"some[prefix][availableAtRunTime]"') === false);
            $this->assertTrue (strpos($content,  'multiple="multiple"') === false);

            //Test a phone attribute
            $model->attributeIndexOrDerivedType = 'phone';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a radioDropDown attribute with the operator set to multiple
            $model->attributeIndexOrDerivedType = 'radioDropDown';
            $model->operator                    = 'oneOf';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value][]"')            === false);
            $this->assertTrue(strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, 'multiple="multiple"') === false);
            //Test a radioDropDown attribute with the operator set to null;
            $model->operator                    = null;
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content,  '"some[prefix][value]"')              === false);
            $this->assertTrue (strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content,  '"some[prefix][availableAtRunTime]"') === false);
            $this->assertTrue (strpos($content,  'multiple="multiple"') === false);

            //Test a string attribute
            $model->attributeIndexOrDerivedType = 'string';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a textArea attribute
            $model->attributeIndexOrDerivedType = 'textArea';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a url attribute
            $model->attributeIndexOrDerivedType = 'url';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);

            //Test a dynamically derived User
            $model->attributeIndexOrDerivedType = 'owner__User';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertTrue (strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value]"')              === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, '"some[prefix][stringifiedModelForValue]"') === false);

            //Test a tagCloud attribute with the operator set to multiple
            $model->attributeIndexOrDerivedType = 'tagCloud';
            $model->operator                    = 'oneOf';
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                                                      $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content, '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content, '"some[prefix][value][]"')            === false);
            $this->assertTrue(strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content, '"some[prefix][availableAtRunTime]"') === false);
            $this->assertFalse(strpos($content, 'multiple="multiple"') === false);
            //Test a tagCloud attribute with the operator set to null;
            $model->operator                    = null;
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($content,  '"some[prefix][operator]"')           === false);
            $this->assertFalse(strpos($content,  '"some[prefix][value]"')              === false);
            $this->assertTrue (strpos($content,  '"some[prefix][secondValue]"')        === false);
            $this->assertFalse(strpos($content,  '"some[prefix][availableAtRunTime]"') === false);
            $this->assertTrue (strpos($content,  'multiple="multiple"') === false);

            //likeContactState,
                //#1 - identify that this can potentially have a state.
                //in the reportRules we can identify it as having a state, even identify which element to use?
                //#2 - but we still dont know if we are approaching this via leads or contacts module.

            //ulitmately it is about resolving to either ContactStateStaticDropDownElement or LeadStateStaticDropDownElement
            //if you are in accounts and relate to contacts, how do we know this is contactsModule and not LeadsModule
            //from activities we only know the model , we would have to do a forker.
        }

        /**
         * @depends testGetFilterContentForRowsAndColumns
         */
        public function testGetGroupByContentForRowsAndColumns()
        {
            //todo: think about group by axis check check no axis here.
            $this->fail();
        }

        /**
         * @depends testGetGroupByContentForRowsAndColumns
         */
        public function testGetOrderByContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        /**
         * @depends testGetOrderByContentForRowsAndColumns
         */
        public function testGetDisplayAttributeContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        /**
         * @depends testGetDisplayAttributeContentForRowsAndColumns
         */
        public function testGetDrillDownDisplayAttributeContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        //etc. etc.


        public function testGetContentForSummation()
        {
            //todo:
            //we really shouldn't have to test filters again since nothing is really any different.
            //$this->fail();
        }

        public function testGetContentForMatrix()
        {
            //todo:
           //$this->fail();
        }
    }
?>