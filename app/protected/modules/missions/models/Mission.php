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

    class Mission extends OwnedSecurableItem implements MashableActivityInterface
    {
        const STATUS_AVAILABLE = 1;

        const STATUS_TAKEN     = 2;

        const STATUS_COMPLETED = 3;

        const STATUS_REJECTED  = 4;

        const STATUS_ACCEPTED  = 5;

        public static function getMashableActivityRulesType()
        {
            return 'Mission';
        }

        public static function getDescription($description)
        {
            assert('is_string($description) && $description != ""');
            return self::getSubset(null, null, null, "description = '$description'");
        }

        public function __toString()
        {
            try
            {
                if (trim($this->description) == '')
                {
                    return Yii::t('Default', '(Unnamed)');
                }
                return $this->description;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public function onCreated()
        {
            parent::onCreated();
            $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getModuleClassName()
        {
            return 'MissionsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'dueDateTime',
                    'latestDateTime',
                    'ownerHasReadLatest',
                    'reward',
                    'status',
                    'takenByUserHasReadLatest',
                ),
                'relations' => array(
                    'comments'                 => array(RedBeanModel::HAS_MANY,  'Comment', RedBeanModel::OWNED, 'relatedModel'),
                    'files'                    => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                    'takenByUser'              => array(RedBeanModel::HAS_ONE,   'User'),
                ),
                'rules' => array(
                    array('description',              'required'),
                    array('description',    		  'type', 'type' => 'string'),
                    array('dueDateTime', 	          'type', 'type' => 'datetime'),
                    array('latestDateTime', 		  'required'),
                    array('latestDateTime', 		  'readOnly'),
                    array('latestDateTime', 		  'type', 'type' => 'datetime'),
                    array('status',           		  'required'),
                    array('status',          		  'type',    'type' => 'integer'),
                    array('ownerHasReadLatest',       'boolean'),
                    array('reward',  	  'type', 'type' => 'string'),
                    array('takenByUserHasReadLatest', 'boolean'),

                ),
                'elements' => array(
                    'description'       => 'TextArea',
                    'dueDateTime'       => 'DateTime',
                    'files'             => 'Files',
                    'latestDateTime'    => 'DateTime',
                    'reward' => 'TextArea',
                ),
                'defaultSortAttribute' => 'subject',
                'noAudit' => array(
                    'description',
                    'dueDateTime',
                    'latestDateTime',
                    'ownerHasReadLatest',
                    'reward',
                    'status',
                    'takenByUserHasReadLatest'
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
          //  return 'MissionGamification';
        }

        /**
         * Alter hasReadLatest and/or ownerHasReadLatest based on comments being added.
         * (non-PHPdoc)
         * @see Item::beforeSave()
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if($this->comments->isModified() || $this->getIsNewModel())
                {
                    $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                    if($this->getIsNewModel())
                    {
                        $this->ownerHasReadLatest = true;
                    }
                }
                if($this->comments->isModified())
                {
                    foreach($this->comments as $comment)
                    {
                        if($comment->id < 0)
                        {
                            if(Yii::app()->user->userModel != $this->owner)
                            {
                                $this->ownerHasReadLatest = false;
                            }
                            if(Yii::app()->user->userModel != $this->takenByUser)
                            {
                                $this->takenByUserHasReadLatest = false;
                            }
                        }
                    }
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function hasRelatedItems()
        {
            return false;
        }
    }
?>