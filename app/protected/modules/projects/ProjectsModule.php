<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ProjectsModule extends SecurableModule
    {
        const RIGHT_CREATE_PROJECTS = 'Create Projects';

        const RIGHT_DELETE_PROJECTS = 'Delete Projects';

        const RIGHT_ACCESS_PROJECTS = 'Access Projects Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Project');
        }

        public static function getTranslatedRightsLabels()
        {
            $params                              = LabelUtil::getTranslationParamsForAllModules();
            $labels                              = array();
            $labels[self::RIGHT_CREATE_PROJECTS] = Zurmo::t('ProjectsModule', 'Create ProjectsModulePluralLabel',     $params);
            $labels[self::RIGHT_DELETE_PROJECTS] = Zurmo::t('ProjectsModule', 'Delete ProjectsModulePluralLabel',     $params);
            $labels[self::RIGHT_ACCESS_PROJECTS] = Zurmo::t('ProjectsModule', 'Access ProjectsModulePluralLabel Tab', $params);
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'designerMenuItems' => array(
                    'showFieldsLink' => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink' => true,
                ),
                'globalSearchAttributeNames' => array(
                    'quantity',
                    'name'
                ),
                'tabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ProjectsModule', 'ProjectsModulePluralLabel', \$translationParams)",
                        'url'   => array('/products/default'),
                        'right' => self::RIGHT_ACCESS_PROJECTS,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Product';
        }

        public static function getSingularCamelCasedName()
        {
            return 'Product';
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('ProjectsModule', 'Product', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('ProjectsModule', 'Projects', array(), null, $language);
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_PROJECTS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_PROJECTS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_PROJECTS;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'ProjectsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('ProjectsDemoDataMaker');
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'ProjectsSearchForm';
        }

        public static function hasPermissions()
        {
            return true;
        }

        public static function isReportable()
        {
            return true;
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        public static function canHaveWorkflow()
        {
            return true;
        }
    }
?>