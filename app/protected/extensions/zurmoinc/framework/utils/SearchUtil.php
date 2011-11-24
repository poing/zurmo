<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    /**
     * Helper functionality to convert POST/GET
     * search information into variables and arrays
     * that the RedBeanDataProvider will accept.
     */
    class SearchUtil
    {
        /**
         * Get the search attributes array by resolving the GET array
         * for the information.
         */
        public static function resolveSearchAttributesFromGetArray($getArrayName)
        {
            assert('is_string($getArrayName)');
            $searchAttributes = array();
            if (!empty($_GET[$getArrayName]))
            {
                $searchAttributes = SearchUtil::getSearchAttributesFromSearchArray($_GET[$getArrayName]);
            }
            return $searchAttributes;
        }

        /**
         * Get the sort attribute array by resolving the GET array
         * for the information.
         */
        public static function resolveSortAttributeFromGetArray($getArrayPrefixName)
        {
            $sortAttribute = null;
            if (!empty($_GET[$getArrayPrefixName . '_sort']))
            {
                $sortAttribute = SearchUtil::getSortAttributeFromSortString($_GET[$getArrayPrefixName . '_sort']);
            }
            return $sortAttribute;
        }

        /**
         * Get the sort descending array by resolving the GET array
         * for the information.
         */
        public static function resolveSortDescendingFromGetArray($getArrayPrefixName)
        {
            $sortDescending = false;
            if (!empty($_GET[$getArrayPrefixName . '_sort']))
            {
                $sortDescending = SearchUtil::isSortDescending($_GET[$getArrayPrefixName . '_sort']);
            }
            return $sortDescending;
        }

        /**
         * Convert incoming sort array into the sortAttribute part
         * Examples: 'name.desc'  'officeFax'
         */
        public static function getSortAttributeFromSortString($sortString)
        {
            $sortInformation = explode(".", $sortString);
            if ( count($sortInformation) == 2)
            {
                $sortAttribute = $sortInformation[0];
            }
            elseif ( count($sortInformation) == 1)
            {
                $sortAttribute = $sortInformation[0];
            }
            return $sortAttribute;
        }

        /**
         * Find out if the sort should be descending
         */
        public static function isSortDescending($sortString)
        {
            $sortInformation = explode(".", $sortString);
            if (count($sortInformation) == 2)
            {
                if ($sortInformation[1] == 'desc')
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * Convert search array into RedBeanDataProvider ready
         * array. Primary purpose is to set null any 'empty', but
         * set element in the array.
         */
        public static function getSearchAttributesFromSearchArray($searchArray)
        {
            array_walk_recursive($searchArray, 'SearchUtil::changeEmptyValueToNull');
            return $searchArray;
        }

        /**
         * if a value is empty, then change it to null
         */
        private static function changeEmptyValueToNull(&$value, $key)
        {
            if (empty($value) && !is_numeric($value))
            {
                $value = null;
            }
        }

        public static function adaptSearchAttributesToSetInRedBeanModel($searchAttributes, $model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            $searchAttributesReadyToSetToModel = array();
            if($model instanceof SearchForm)
            {
                $modelToUse =  $model->getModel();
            }
            else
            {
                $modelToUse =  $model;
            }
            foreach($searchAttributes as $attributeName => $data)
            {
                if($modelToUse->isAttribute($attributeName))
                {
                    $type = ModelAttributeToMixedTypeUtil::getType($modelToUse, $attributeName);
                    switch($type)
                    {
                        case 'CheckBox':

                            if(is_array($data) && isset($data['value']))
                            {
                                $data = $data['value'];
                            }
                            elseif(is_array($data) && $data['value'] == null)
                            {
                                $data = null;
                            }
                        default :
                            continue;
                    }
                }
                $searchAttributesReadyToSetToModel[$attributeName] = $data;
            }
            return $searchAttributesReadyToSetToModel;
        }
    }
?>