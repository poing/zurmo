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

    /**
     * View that renders a list in the form of a
     * tree widget.
     */
    class GroupsModalTreeListView extends GroupsTreeListView
    {
        /**
         * Id of input field in display for saving back a selected
         * record from the modal list view.
         * @see $sourceIdFieldId
         */
        protected $sourceIdFieldId;

        /**
         * Name of input field in display for saving back a selected
         * record from the modal list view.
         * @see $sourceNameFieldId
         */
        protected $sourceNameFieldId;

        /**
         * The id of the modal container where the list view resides
         * @var type
         */
        protected $modalId;

        /**
         * sourceIdFieldName and sourceNameFieldId are needed to know
         * which fields in the parent form to populate data with
         * upon selecting a row in the listview
         *
         */
        public function __construct($controllerId, $moduleId, $modelId, $items, $sourceIdFieldId, $sourceNameFieldId, $modalId)
        {
            assert('$controllerId      != null');
            assert('$moduleId          != null');
            assert('is_array($items)'          );
            assert('$sourceIdFieldId   != null');
            assert('$sourceNameFieldId != null');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelId                = $modelId;
            $this->items                  = $items;
            $this->sourceIdFieldId        = $sourceIdFieldId;
            $this->sourceNameFieldId      = $sourceNameFieldId;
            $this->modalId                = $modalId;
        }

        /**
         * Override because we do not need to render
         * the view tool bar or any extra spacing.
         */
        protected function renderContent()
        {
            return $this->renderTreeMenu('group', 'groups', Zurmo::t('ZurmoModule', 'Group'));
        }

        protected function makeTreeMenuNodeLink($label, $action, $groupId)
        {
            return ZurmoHtml::Link($label,
                    'javascript:transferModalValues("#' . $this->modalId . '", '
                                                         . CJavaScript::encode(array($this->sourceIdFieldId    => $groupId,
                                                                                     $this->sourceNameFieldId  => $label)) . '
                    );'
            );
        }

        protected static function shouldRenderConfigureLink()
        {
            return false;
        }
    }
?>
