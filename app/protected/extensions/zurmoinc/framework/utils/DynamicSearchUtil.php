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

    /**
     * Helper class for working with advanced search panel.
     */
    class DynamicSearchUtil
    {
        /**
         * Use when multiple relation attribute names
         * need to be combined together into one string that can easily
         * be parsed later.
         * @see FormModelUtil::DELIMITER which is only 2 __
         */
        const RELATION_DELIMITER = '___';

        public static function getSearchableAttributesAndLabels($viewClassName, $modelClassName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            $searchFormClassName      = $viewClassName::getModelForMetadataClassName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );
            $attributeIndexOrDerivedTypeAndLabels = array();
            foreach($attributesLayoutAdapter->makeDesignerLayoutAttributes()->get() as $attributeIndexOrDerivedType => $data)
            {
                if($searchFormClassName::isAttributeSearchable($attributeIndexOrDerivedType))
                {
                    $attributeIndexOrDerivedTypeAndLabels[$attributeIndexOrDerivedType] = $data['attributeLabel'];
                }
            }
            self::resolveAndAddViewDefinedNestedAttributes($modelAttributesAdapter->getModel(), $viewClassName, $attributeIndexOrDerivedTypeAndLabels);
            return $attributeIndexOrDerivedTypeAndLabels;
        }

        public static function getCellElement($viewClassName, $modelClassName, $elementName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            assert('is_string($elementName)');
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );

            $derivedAttributes         = $attributesLayoutAdapter->getAvailableDerivedAttributeTypes();
            $placeableLayoutAttributes = $attributesLayoutAdapter->getPlaceableLayoutAttributes();
            if (in_array($elementName, $derivedAttributes))
            {
                $element = array('attributeName' => 'null', 'type' => $elementName); // Not Coding Standard
            }
            elseif (isset($placeableLayoutAttributes[$elementName]) &&
                   $placeableLayoutAttributes[$elementName]['elementType'] == 'DropDownDependency')
            {
                throw new NotSupportedException();
            }
            elseif (isset($placeableLayoutAttributes[$elementName]))
            {
                $element = array(
                    'attributeName' => $elementName,
                    'type'          => $placeableLayoutAttributes[$elementName]['elementType']
                );
            }
            else
            {
                throw new NotSupportedException();
            }
            return $designerRules->formatSavableElement($element, $viewClassName);
        }

        public static function resolveAndAddViewDefinedNestedAttributes($model, $viewClassName, & $attributeIndexOrDerivedTypeAndLabels)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($viewClassName)');
            assert('is_array($attributeIndexOrDerivedTypeAndLabels)');
            $metadata = $viewClassName::getMetadata();
            if(isset($metadata['global']['definedNestedAttributes']))
            {
                foreach($metadata['global']['definedNestedAttributes'] as $definedNestedAttribute)
                {
                    $attributeIndexOrDerivedLabel = null;
                    $attributeIndexOrDerivedType  = self::makeDefinedNestedAttributeIndexOrDerivedTypeRecursively(
                                                            $model,
                                                            $attributeIndexOrDerivedLabel,
                                                            $definedNestedAttribute);
                    if($attributeIndexOrDerivedLabel == null)
                    {
                        throw new NotSupportedException();
                    }
                    $attributeIndexOrDerivedTypeAndLabels[$attributeIndexOrDerivedType] = $attributeIndexOrDerivedLabel;
                }
            }
        }

        protected static function makeDefinedNestedAttributeIndexOrDerivedTypeRecursively($model, & $attributeIndexOrDerivedLabel, $definedNestedAttribute)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($attributeIndexOrDerivedLabel) || $attributeIndexOrDerivedLabel == null');
            assert('is_array($definedNestedAttribute)');
            if(count($definedNestedAttribute) > 1)
            {
                //Each defined attribute should be in its own sub-array.
                throw new NotSupportedException();
            }
            foreach($definedNestedAttribute as $positionOrAttributeName => $nestedAttributeDataOrAttributeName)
            {
                if(is_array($nestedAttributeDataOrAttributeName))
                {
                    $attributeIndexOrDerivedLabel .= $model->getAttributeLabel($positionOrAttributeName) . ' - ';
                    $modelToUse      = SearchUtil::resolveModelToUseByModelAndAttributeName(
                                                $model,
                                                $positionOrAttributeName);
                    $string          = self::makeDefinedNestedAttributeIndexOrDerivedTypeRecursively(
                                                $modelToUse,
                                                $attributeIndexOrDerivedLabel,
                                                $nestedAttributeDataOrAttributeName);
                    return $positionOrAttributeName . self::RELATION_DELIMITER . $string;
                }
                else
                {
                    $attributeIndexOrDerivedLabel .= $model->getAttributeLabel($nestedAttributeDataOrAttributeName);
                    return $nestedAttributeDataOrAttributeName;
                }
            }
        }
    }
?>