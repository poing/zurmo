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

    class BuilderCanvasWizardView extends ComponentForEmailTemplateWizardView
    {
        const REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID    = 'refresh-canvas-from-saved-template';

        const CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME         = 'serializedData';

        const CANVAS_IFRAME_ID                              = 'canvas-iframe';

        const PREVIEW_IFRAME_ID                             = 'preview-iframe';

        const PREVIEW_IFRAME_CONTAINER_ID                   = 'preview-iframe-container';

        const PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID        = 'preview-iframe-container-close-link';

        const ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID        = 'element-edit-form-overlay-container';

        const UL_ELEMENT_TO_PLACE_ID                        = 'building-blocks';

        const ELEMENT_IFRAME_OVERLAY_ID                     = 'iframe-overlay';

        const ELEMENTS_MENU_BUTTON_ID                       = 'builder-elements-menu-button';

        const CANVAS_CONFIGURATION_MENU_BUTTON_ID           = 'builder-canvas-configuration-menu-button';

        const PREVIEW_MENU_BUTTON_ID                        = 'builder-preview-menu-button';

        const ELEMENTS_CONTAINER_ID                         = 'droppable-element-sidebar';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Canvas');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'builderCanvasPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'builderCanvasSaveLink';
        }

        protected function renderNextPageLinkContent()
        {
            //todo: temporary. removed save button for now. eventually we can add this back in
            return null;
        }

        /**
         * @return string
         */
        public static function getFinishLinkId()
        {
            return 'builderCanvasFinishLink';
        }

        protected function renderNextPageLinkLabel()
        {
            return Zurmo::t('Core', 'Save');
        }

        protected function renderFinishLinkLabel()
        {
            return Zurmo::t('Core', 'Finish');
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $previewContainerContent   = $this->resolvePreviewContainerContent();
            $freezeOverlayContent      = $this->renderFreezeOverlayContent();
            $leftSidebarContent        = $this->renderLeftSidebarContent();
            $rightSidebarContent       = $this->resolveCanvasContent();
            $this->wrapContentForLeftSideBar($leftSidebarContent);
            $this->wrapContentForRightSideBar($rightSidebarContent);
            $content = $freezeOverlayContent . $previewContainerContent .
                       $leftSidebarContent . $rightSidebarContent;
            $content = ZurmoHtml::tag('div', $this->resolveContentHtmlOptions(), $content);
            $this->wrapContentForAttributesContainer($content);
            return $content;
        }

        protected function renderLeftSidebarContent()
        {
            // TODO: @Shoaibi: Critical1: Hidden elements for all serializedData Indexes?
            $hiddenElements      = null;
            $leftSidebarContent  = $this->renderLeftSidebarToolbarContent();
            $leftSidebarContent .= $this->resolveElementsSidebarContent();
            $this->renderHiddenElements($hiddenElements, $leftSidebarContent);
            $leftSidebarContent .= $this->renderRefreshCanvasLinkContent($leftSidebarContent);
            return $leftSidebarContent;
        }

        protected function renderLeftSidebarToolbarContent()
        {
            $this->registerLeftSideToolbarScripts();
            $content  = '<div class="view-toolbar-container clearfix"><nav class="pillbox clearfix">';
            $element  = new EmailTemplateBuilderElementsMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::ELEMENTS_MENU_BUTTON_ID, 'class' => 'active'),
                                  'iconClass'=> 'icon-layout'));
            $content .= $element->render();
            $element  = new EmailTemplateBuilderCanvasConfigurationMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::CANVAS_CONFIGURATION_MENU_BUTTON_ID), 'iconClass'=> 'icon-layout'));
            $content .= $element->render();
            $element  = new EmailTemplateBuilderPreviewMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::PREVIEW_MENU_BUTTON_ID), 'iconClass'=> 'icon-layout'));
            $content .= $element->render();
            $content .= '</nav></div>';
            return $content;
        }

        protected function registerLeftSideToolbarScripts()
        {
            $script  = '$("#' . static::ELEMENTS_MENU_BUTTON_ID . '").live("click", function()
                         {
                            if(!$("#' . static::ELEMENTS_MENU_BUTTON_ID . '").hasClass("active"))
                            {
                                $("#' . static::ELEMENTS_MENU_BUTTON_ID . '").addClass("active");
                            }
                            $("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").removeClass("active");
                            $("#' . static::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID . '").hide();
                            $("#' . static::ELEMENTS_CONTAINER_ID . '").show();
                         });

            ';
            $script .= '$("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").live("click", function()
                         {
                            if(!$("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").hasClass("active"))
                            {
                                $("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").addClass("active");
                            }
                            $("#' . static::ELEMENTS_MENU_BUTTON_ID . '").removeClass("active");
                            $("#' . static::ELEMENTS_CONTAINER_ID . '").hide();
                            $("#' . static::CANVAS_IFRAME_ID . '").contents().find("#element-actions-canvas1").find(".' .
                            BaseBuilderElement::OVERLAY_ACTION_EDIT . '").trigger("click");
                         });

            ';
            $script .= '$("#' . static::PREVIEW_MENU_BUTTON_ID . '").live("click", function()
                         {
                            $("#' . static::PREVIEW_IFRAME_CONTAINER_ID . '").show();
                            jsonSerializedData = {dom: $.parseJSON(emailTemplateEditor.compileSerializedData())};
                            serializedData     = JSON.stringify(jsonSerializedData);
                            $.ajax({
                                url  : "' . $this->resolvePreviewActionUrl() . '",
                                type : "POST",
                                data : {serializedData: serializedData, "YII_CSRF_TOKEN": "' . Yii::app()->request->csrfToken . '"},
                                success: function (html)
                                {
                                    $("#' . static::PREVIEW_IFRAME_ID . '").contents().find("html").html(html);
                                }
                            });
                         });

            ';
            $script .= '$("#' . static::PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID . '").live("click", function()
                         {
                            $("#' . static::PREVIEW_IFRAME_CONTAINER_ID . '").hide();
                         });
            ';
            Yii::app()->getClientScript()->registerScript('emailTemplateBuilderCanvasLeftSideToolbarScripts', $script);
        }


        protected function renderFreezeOverlayContent()
        {
            $span = ZurmoHtml::tag('span', array('class' => 'big-spinner'));
            return ZurmoHtml::tag('div', array('id' => static::ELEMENT_IFRAME_OVERLAY_ID, 'class' => 'ui-overlay-block'), $span);
        }

        protected function resolveCachedSerializedDataHiddenInputJQuerySelector()
        {
            return '#' . get_class($this->model) . '_' . static::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME . '_dom';
        }

        protected function resolveContentHtmlOptions()
        {
            return array('id' => 'builder', 'class' => 'strong-right clearfix');
        }

        protected function renderRefreshCanvasLinkContent()
        {
            $linkContent    = ZurmoHtml::link('Reload Canvas', '#', $this->resolveRefreshCanvasLinkHtmlOptions());
            $this->wrapContentInTableCell($linkContent, array('colspan' => 2));
            $this->wrapContentInTableRow($linkContent);
            return $linkContent;
        }

        protected function resolveRefreshCanvasLinkHtmlOptions()
        {
            return array('id' => static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID, 'style' => 'display:none');
        }

        protected function renderActionLinksContent()
        {
            $content    = parent::renderActionLinksContent();
            $content    .= $this->renderFinishLinkContent();
            return $content;
        }

        protected function renderFinishLinkContent()
        {
            $params                = array();
            $params['label']       = $this->renderFinishLinkLabel();
            $params['htmlOptions'] = $this->resolveFinishLinkHtmlOptions();
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        protected function resolveFinishLinkHtmlOptions()
        {
            return array('id' => static::getFinishLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
        }

        protected function generateWidgetTagsForUIAccessibleElements($uiAccessibleElements)
        {
            $content    = null;
            foreach ($uiAccessibleElements as $element)
            {
                $content    .=  $element::resolveDroppableWidget('li');
            }
            $content        = ZurmoHtml::tag('ul', $this->resolveWidgetTagsWrapperHtmlOptions(), $content);
            return $content;
        }

        protected function resolveWidgetTagsWrapperHtmlOptions()
        {
            return array('id' => 'building-blocks', 'class' => 'clearfix builder-elements builder-elements-droppable');
        }

        protected function resolveElementsSidebarContent()
        {
            $uiAccessibleElements   = PathUtil::getAllUIAccessibleBuilderElementClassNames();
            $content                = $this->generateWidgetTagsForUIAccessibleElements($uiAccessibleElements);
            $this->wrapContentInDiv($content, $this->resolveElementsSidebarHtmlOptions());
            $content                .= ZurmoHtml::tag('div', array('id' => static::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID), '');
            $this->wrapContentInTableCell($content, array('colspan' => 2));
            $this->wrapContentInTableRow($content);
            return $content;
        }

        protected function resolveElementsSidebarHtmlOptions()
        {
            return array('id' => static::ELEMENTS_CONTAINER_ID);
        }

        protected function resolveCanvasContent()
        {
            $canvasContent  = ZurmoHtml::tag('iframe', $this->resolveCanvasIFrameHtmlOptions(), '');
            return $canvasContent;
        }

        protected function resolveCanvasIFrameHtmlOptions()
        {
            return array('id' => static::CANVAS_IFRAME_ID,
                            'src' => $this->resolveCanvasActionUrl(),
                            'width' => '100%',
                            'height'    => '100%',
                            'frameborder' => 0);
        }

        protected function resolvePreviewContainerContent()
        {
            $content  = ZurmoHtml::link(Zurmo::t('Core', 'Close'), '#', array('id' => static::PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID));
            $content .= ZurmoHtml::tag('iframe', $this->resolvePreviewIFrameHtmlOptions(), '');
            $this->wrapContentInDiv($content, $this->resolvePreviewIFrameContainerHtmlOptions());
            return $content;
        }

        protected function resolvePreviewIFrameHtmlOptions()
        {
            return array('id' => static::PREVIEW_IFRAME_ID,
                // we set it to about:blank instead of preview url to save request and to also have some
                // sort of basic html structure there which we can replace.
                'src' => 'about:blank',
                'width' => '100%',
                'height'    => '100%',
                'frameborder' => 0);
        }

        protected function resolvePreviewIFrameContainerHtmlOptions()
        {
            return array('id'    => static::PREVIEW_IFRAME_CONTAINER_ID,
                         'title' => Zurmo::t('EmailTemplatesModule', 'Preview'),
                         'style' => 'display:none');
        }

        protected function resolveCanvasActionUrl()
        {
            return $this->resolveRelativeUrl('renderCanvas', array('id' => $this->model->id));
        }

        protected function resolvePreviewActionUrl()
        {
            return $this->resolveRelativeUrl('renderPreview');
        }

        protected function resolveElementEditableActionUrl()
        {
            return $this->resolveRelativeUrl('renderElementEditable');
        }

        protected function resolveElementNonEditableActionUrl()
        {
            return $this->resolveRelativeUrl('renderElementNonEditable');
        }

        protected function resolveRelativeUrl($action, $params = array())
        {
            return Yii::app()->createUrl($this->getModuleId() . '/' . $this->getControllerId() . '/' . $action, $params);
        }

        protected function registerScripts()
        {
            // TODO: @Shoaibi/@Sergio: Critical5: Did i miss any JS here?
            parent::registerScripts();
            $this->registerEmailTemplateEditorScriptFile();
            $this->registerInitializeEmailTemplateEditor();
            $this->registerRefreshCanvasFromSavedTemplateScript();
            $this->registerBindElementNonEditableActionsOverlayScript();
            $this->registerElementDragAndDropScript();
            $this->registerSerializedDataCompilationFunctionsScript();
            $this->registerCanvasSaveScript();
            $this->registerCanvasFinishScript();
            $this->registerCanvasChangedScript();
        }

        protected function registerEmailTemplateEditorScriptFile()
        {
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.emailTemplates.widgets.assets'));
            $cs            = Yii::app()->getClientScript();
            $cs->registerScriptFile($baseScriptUrl . '/EmailTemplateEditor.js', CClientScript::POS_HEAD);
        }

        protected function registerInitializeEmailTemplateEditor()
        {
            $elementsContainerId                = '#' . static::ELEMENTS_CONTAINER_ID;
            $elementsToPlaceSelector            = '#' . static::UL_ELEMENT_TO_PLACE_ID;
            $iframeSelector                     = '#' . static::CANVAS_IFRAME_ID;
            $editSelector                       = '#' . static::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID;
            $editActionSelector                 = 'span.' . BaseBuilderElement::OVERLAY_ACTION_EDIT;
            $moveActionSelector                 = 'span.' . BaseBuilderElement::OVERLAY_ACTION_MOVE;
            $deleteActionSelector               = 'span.' . BaseBuilderElement::OVERLAY_ACTION_DELETE;
            $iframeOverlaySelector              = '#' . static::ELEMENT_IFRAME_OVERLAY_ID;
            $cachedSerializedSelector           = static::resolveCachedSerializedDataHiddenInputJQuerySelector();
            $errorOnDeleteMessage               = Zurmo::t('EmailTemplatesModule', 'Cannot delete last row');
            $dropHereMessage                    = Zurmo::t('EmailTemplatesModule', 'Drop here');
            $csrfToken                          = Yii::app()->request->csrfToken;
            Yii::app()->getClientScript()->registerScript('initializeEmailTemplateEditor', "
                initEmailTemplateEditor = function () {
                    emailTemplateEditor.init(
                        '{$elementsContainerId}',
                        '{$elementsToPlaceSelector}',
                        '{$iframeSelector}',
                        '{$editSelector}',
                        '{$editActionSelector}',
                        '{$moveActionSelector}',
                        '{$deleteActionSelector}',
                        '{$iframeOverlaySelector}',
                        '{$cachedSerializedSelector}',
                        '{$this->resolveElementEditableActionUrl()}',
                        '{$this->resolveElementNonEditableActionUrl()}',
                        '{$errorOnDeleteMessage}',
                        '{$dropHereMessage}',
                        '{$csrfToken}'
                    );
                };
                ", CClientScript::POS_END);
        }


        protected function registerRefreshCanvasFromSavedTemplateScript()
        {
            Yii::app()->clientScript->registerScript('refreshCanvasFromSavedTemplateScript', "
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').unbind('click');
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').bind('click', function()
                {
                    emailTemplateEditor.reloadCanvas();
                    return false;
                });
                ", CClientScript::POS_READY);
        }

        protected function registerBindElementNonEditableActionsOverlayScript()
        {
            Yii::app()->clientScript->registerScript('bindElementNonEditableActionsOverlayScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS to bind element actions
                ", CClientScript::POS_READY);
        }

        protected function registerElementDragAndDropScript()
        {
            Yii::app()->clientScript->registerScript('elementDragAndDropScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                ", CClientScript::POS_READY);
        }

        protected function registerCanvasSaveScript()
        {
            $successMessage = Zurmo::t('EmailTemplatesModule',
                                           'EmailTemplatesModuleSingularLabel was successfully saved.',
                                           LabelUtil::getTranslationParamsForAllModules());
            $errorMessage   = Zurmo::t('EmailTemplatesModule',
                                       'There was an error saving EmailTemplatesModuleSingularLabel',
                                        LabelUtil::getTranslationParamsForAllModules());
            Yii::app()->clientScript->registerScript('canvasSaveScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: What to do about: BuilderEmailTemplateWizardView:111
                //TODO: @Sergio: This was moved to resolveAdditionalAjaxOptions is this still needed?
                ");
        }

        protected function registerCanvasFinishScript()
        {
            Yii::app()->clientScript->registerScript('canvasFinishScript', "
                $('#" . static::getFinishLinkId() . "').unbind('click.canvasFinishScript');
                $('#" . static::getFinishLinkId() . "').bind('click.canvasFinishScript', function()
                {
                    setIsDraftToZero();
                    $('#" . static::getNextPageLinkId() . "').click();

                });
                ", CClientScript::POS_END);
        }

        protected function registerSerializedDataCompilationFunctionsScript()
        {
            Yii::app()->clientScript->registerScript('serializedDataCompilationFunctionsScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Add JS
                ", CClientScript::POS_END);
        }


        protected function registerCanvasChangedScript()
        {
            Yii::app()->clientScript->registerScript('canvasChangedScript', "
                // TODO: @Sergio/@Shoaibi: Critical2: Attach an event(canvasChanged) to window object. register event handler that clears the cached serialized data from hidden input
                ");
        }

        public static function resolveAdditionalAjaxOptions($formName)
        {
            /*
             * For Save/Finish do this:
             * first event should compile serializedData if need and shove it into an html entity
             * second event(for finish), set isDraft to zero, we have this done.
             * third, change the $ajaxArray['data'] to jquery selector of val of the entity containing cached serialized data.
             */
            $successMessage = Zurmo::t('EmailTemplatesModule',
                                        'EmailTemplatesModuleSingularLabel was successfully saved.',
                                        LabelUtil::getTranslationParamsForAllModules());
            $errorMessage   = Zurmo::t('EmailTemplatesModule',
                                        'There was an error saving EmailTemplatesModuleSingularLabel',
                                        LabelUtil::getTranslationParamsForAllModules());
            $ajaxArray                  = array();
            $ajaxArray['success']       = "js:function(data)
                                            {
                                                $('#FlashMessageBar').jnotifyAddMessage({
                                                    text: '" . $successMessage . "',
                                                    permanent: true,
                                                    clickOverlay : true,
                                                    showIcon: false,
                                                });
                                                emailTemplateEditor.unfreezeLayoutEditor();
                                            }";
            $ajaxArray['error']       = "js:function(data)
                                            {
                                                $('#FlashMessageBar').jnotifyAddMessage({
                                                    text: '" . $errorMessage . "',
                                                    permanent: true,
                                                    clickOverlay : true,
                                                    showIcon: false,
                                                });
                                                emailTemplateEditor.unfreezeLayoutEditor();
                                            }";
            return $ajaxArray;
        }
    }
?>