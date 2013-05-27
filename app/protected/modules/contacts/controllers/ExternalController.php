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

    class ContactsExternalController extends ZurmoModuleController
    {
        public function filters()
        {
            return array();
        }

        public function actionForm($id)
        {
            Yii::app()->user->userModel = ContactWebFormsUserConfigUtil::getUserToRunAs();
            Yii::app()->getClientScript()->setIsolationMode();
            $contactWebForm           = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            $viewClassName            = 'ContactExternalEditAndDetailsView';
            $moduleClassName          = 'ContactsModule';
            $modelClassName           = $moduleClassName::getPrimaryModelName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                    $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter  = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter($attributeCollection,
                                                                                                 $designerRules,
                                                                                                 $editableMetadata);
            $layoutMetadataAdapter    = new LayoutMetadataAdapter(
                                                    $viewClassName,
                                                    $moduleClassName,
                                                    $editableMetadata,
                                                    $designerRules,
                                                    $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                                                    $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes());
            $metadata                 = $layoutMetadataAdapter->resolveMetadataFromSelectedListAttributes($viewClassName,
                                                                                                          $contactWebFormAttributes);
            if (is_string($contactWebForm->submitButtonLabel) && !empty($contactWebForm->submitButtonLabel))
            {
                $metadata['global']['toolbar']['elements'][0]['label'] = $contactWebForm->submitButtonLabel;
            }
            $contact                  = new Contact();
            $contact->state           = $contactWebForm->defaultState;
            $contact->owner           = $contactWebForm->defaultOwner;
            $postVariableName         = get_class($contact);
            $containedView            = new ContactExternalEditAndDetailsView('Edit',
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            $this->attemptToSaveModelFromPost($contact, null, false),
                                            $metadata);
            $view = new ContactWebFormsExternalPageView(ZurmoExternalViewUtil::
                                                        makeExternalViewForCurrentUser($containedView));
            if (isset($_POST[$postVariableName]))
            {
                if ($contact->validate())
                {
                    $contactWebFormEntryStatus       = ContactWebFormEntry::STATUS_SUCCESS;
                    $contactWebFormEntryMessage      = ContactWebFormEntry::STATUS_SUCCESS_MESSAGE;
                    $contactWebFormEntryContact      = $contact;
                }
                else
                {
                    $contactWebFormEntryStatus       = ContactWebFormEntry::STATUS_ERROR;
                    $contactWebFormEntryMessage      = ContactWebFormEntry::STATUS_ERROR_MESSAGE;
                    $contactWebFormEntryContact      = null;
                }
                $contactFormAttributes               = $_POST[$postVariableName];
                $contactFormAttributes['owner']      = $contact->owner->id;
                $contactFormAttributes['state']      = $contact->state->id;
                $hashIndex                           = Yii::app()->getRequest()->getPost(ContactWebFormEntry::HIDDEN_FIELD);
                $contactWebFormEntry                 = ContactWebFormEntry::getByHashIndex($hashIndex);
                if ($contactWebFormEntry === null)
                {
                    $contactWebFormEntry             = new ContactWebFormEntry();
                }
                $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
                $contactWebFormEntry->status         = $contactWebFormEntryStatus;
                $contactWebFormEntry->message        = $contactWebFormEntryMessage;
                $contactWebFormEntry->contactWebForm = $contactWebForm;
                $contactWebFormEntry->contact        = $contactWebFormEntryContact;
                $contactWebFormEntry->hashIndex      = $_POST['hashIndex'];
                $contactWebFormEntry->save();
                if (isset($contact->id) && intval($contact->id) > 0)
                {
                    $containedView                   = new ContactExternalEditAndDetailsView('Details',
                                                       $this->getId(),
                                                       $this->getModule()->getId(),
                                                       $this->attemptToSaveModelFromPost($contact, null, false),
                                                       $metadata);
                    $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent')));
                    echo $containedView->render();
                    Yii::app()->end(0, false);
                }
            }
            $this->attemptToValidateAjaxFromPost($contact, 'Contact');
            $rawXHtml                                = $view->render();
            $rawXHtml                                = ZurmoExternalViewUtil::resolveAndCombineScripts($rawXHtml);
            $combinedHtml                            = array();
            $combinedHtml['head']                    = ZurmoExternalViewUtil::resolveHeadTag($rawXHtml);
            $combinedHtml['body']                    = ZurmoExternalViewUtil::resolveHtmlAndScriptInBody($rawXHtml);
            header("content-type: application/json");
            echo 'renderFormCallback('. CJSON::encode($combinedHtml) . ');';
            Yii::app()->end(0, false);
        }

        public function actionSourceFiles($webFormId)
        {
            Yii::app()->user->userModel = ContactWebFormsUserConfigUtil::getUserToRunAs();
            $formContentUrl          = Yii::app()->createAbsoluteUrl('contacts/external/form/', array('id' => $webFormId));
            $renderFormFileUrl       = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias('application.core.views.assets') .
                                       DIRECTORY_SEPARATOR . 'renderExternalForm.js');
            if ($renderFormFileUrl === false || file_exists($renderFormFileUrl) === false)
            {
                $renderFormFileUrl   = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.views.assets') .
                                       DIRECTORY_SEPARATOR . 'renderExternalForm.js');
            }
            $renderFormFileUrl      = Yii::app()->getRequest()->getHostInfo() . $renderFormFileUrl;

            $jsOutput               = "var formContentUrl = '" . $formContentUrl . "';";
            $jsOutput              .= "var externalFormScriptElement = document.createElement('script');
                                       externalFormScriptElement.src  = '" . $renderFormFileUrl . "';
                                       document.getElementsByTagName('head')[0].appendChild(externalFormScriptElement);";
            header("content-type: application/javascript");
            echo $jsOutput;
            Yii::app()->end(0, false);
        }
    }
?>