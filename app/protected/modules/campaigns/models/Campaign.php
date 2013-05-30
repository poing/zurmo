<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class Campaign extends OwnedSecurableItem
    {
        const TYPE_MARKETING_LIST               = 1;

        const TYPE_DYNAMIC                      = 2;

        const STATUS_INCOMPLETE                 = 1;

        const STATUS_PAUSED                     = 2;

        const STATUS_ACTIVE                     = 3;

        const STATUS_PROCESSING                 = 4;

        const STATUS_COMPLETED                  = 5;

        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        public static function getModuleClassName()
        {
            return 'CampaignsModule';
        }

        public static function getStatusDropDownArray()
        {
            return array(
                static::STATUS_INCOMPLETE   => Zurmo::t('CampaignsModule', 'Incomplete'),
                static::STATUS_PAUSED       => Zurmo::t('CampaignsModule', 'Paused'),
                static::STATUS_ACTIVE       => Zurmo::t('CampaignsModule', 'Active'),
                static::STATUS_PROCESSING   => Zurmo::t('CampaignsModule', 'Processing'),
                static::STATUS_COMPLETED    => Zurmo::t('CampaignsModule', 'Completed'),
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Yii::t('Default', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaign', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaigns', array(), null, $language);
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getByStatus($status, $pageSize = null)
        {
            assert('is_int($status)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'status',
                    'operatorType'              => 'equals',
                    'value'                     => intval($status),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        public static function getByStatusAndSendingTime($status, $sendingTimestamp = null, $pageSize = null)
        {
            if (empty($sendingTimestamp))
            {
                $sendingTimestamp = time();
            }
            $sendingDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($sendingTimestamp);
            assert('is_int($status)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'status',
                    'operatorType'              => 'equals',
                    'value'                     => intval($status),
                ),
                2 => array(
                    'attributeName'             => 'sendingDateTime',
                    'operatorType'              => 'lessThan',
                    'value'                     => $sendingDateTime,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'name',
                    'subject',
                    'status',
                    'sendNow',
                    'sendingDateTime',
                    'supportsRichText',
                    'enableTracking',
                    'htmlContent',
                    'textContent',
                    'fromName',
                    'fromAddress',
                ),
                'rules' => array(
                    array('name',                   'required'),
                    array('name',                   'type',    'type' => 'string'),
                    array('name',                   'length',  'min'  => 3, 'max' => 64),
                    //array('type',                 'required'), // TODO: @Shoaibi: Medium: We don't need type for now.
                    array('type',                   'type',    'type' => 'integer'),
                    array('type',                   'numerical',  'min'  => static::TYPE_MARKETING_LIST,
                                                                                        'max' => static::TYPE_DYNAMIC),
                    array('type',                   'default', 'value' => static::TYPE_MARKETING_LIST),
                    array('status',                 'required'),
                    array('status',                 'type',    'type' => 'integer'),
                    array('status',                 'numerical',  'min'  => static::STATUS_INCOMPLETE,
                                                                                    'max' => static::STATUS_COMPLETED),
                    array('supportsRichText',       'required'),
                    array('supportsRichText',       'boolean'),
                    array('sendNow',                'required'),
                    array('sendNow',                'boolean'),
                    array('sendingDateTime',        'type', 'type' => 'datetime'),
                    array('sendingDateTime',        'validateSendDateTimeAgainstSendNow'),
                    array('fromName',                'required'),
                    array('fromName',               'type',    'type' => 'string'),
                    array('fromName',               'length',  'min'  => 3, 'max' => 64),
                    array('fromAddress',            'required'),
                    array('fromAddress',            'type', 'type' => 'string'),
                    array('fromAddress',            'length',  'min'  => 6, 'max' => 64),
                    array('fromAddress',            'email', 'except' => 'autoBuildDatabase'),
                    array('subject',                'required'),
                    array('subject',                'type',    'type' => 'string'),
                    array('subject',                'length',  'min'  => 3, 'max' => 64),
                    array('htmlContent',            'type',    'type' => 'string'),
                    array('textContent',            'type',    'type' => 'string'),
                    array('htmlContent',            'AtLeastOneContentAreaRequiredValidator'),
                    array('textContent',            'AtLeastOneContentAreaRequiredValidator'),
                    array('htmlContent',            'CampaignMergeTagsValidator', 'except' => 'autoBuildDatabase'),
                    array('textContent',            'CampaignMergeTagsValidator', 'except' => 'autoBuildDatabase'),
                    array('enableTracking',         'boolean'),
                    array('enableTracking',         'default', 'value' => false),
                ),
                'relations' => array(
                    'campaignItems'     => array(RedBeanModel::HAS_MANY, 'CampaignItem'),
                    'marketingList'     => array(RedBeanModel::HAS_ONE, 'MarketingList', RedBeanModel::NOT_OWNED),
                    'files'             => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED)
                ),
                'elements' => array(
                    'htmlContent'                   => 'TextArea',
                    'textContent'                   => 'TextArea',
                    'supportsRichText'              => 'CheckBox',
                    'enableTracking'                => 'CheckBox',
                    'sendNow'                       => 'CheckBox',
                    'sendDateTime'                  => 'DateTime',
                ),
                'defaultSortAttribute' => 'name',
            );
            return $metadata;
        }

        public function validateSendDateTimeAgainstSendNow($attribute, $params)
        {
            if (!$this->hasErrors('sendNow') && $this->sendNow == 0 && empty($this->$attribute))
            {
                $this->addError($attribute, Zurmo::t('CampaignsModule', 'Send On cannot be blank.'));
                return false;
            }
            return true;
        }

        public function beforeSave()
        {
            if (!parent::beforeSave())
            {
                return false;
            }
            if ($this->sendNow == 1 && empty($this->sendingDateTime))
            {
                // set sendingDateTime to a past date so its sending immediately in next job execution.
                $this->sendingDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 180);
            }
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name'                  => Zurmo::t('ZurmoModule', 'Name', null,  null, $language),
                    'type'                  => Zurmo::t('Core', 'Type', null,  null, $language),
                    'status'                => Zurmo::t('CampaignsModule', 'Status', null,  null, $language),
                    'sendNow'               => Zurmo::t('CampaignsModule', 'Send Now?', null,  null, $language),
                    'sendingDateTime'       => Zurmo::t('CampaignsModule', 'Send On', null,  null, $language),
                    'supportsRichText'      => Zurmo::t('CampaignsModule', 'Supports Rich Text(HTML) outgoing emails',
                                                                                                null,  null, $language),
                    'fromName'              => Zurmo::t('CampaignsModule', 'From Name', null,  null, $language),
                    'fromAddress'           => Zurmo::t('CampaignsModule', 'From Address', null,  null, $language),
                    'subject'               => Zurmo::t('EmailMessagesModule', 'Subject', null,  null, $language),
                    'htmlContent'           => Zurmo::t('EmailMessagesModule', 'Html Content', null,  null, $language),
                    'textContent'           => Zurmo::t('EmailMessagesModule', 'Text Content', null,  null, $language),
                )
            );
        }
    }
?>