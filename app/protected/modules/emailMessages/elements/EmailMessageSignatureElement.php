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
     * Element to edit and display email message signature content.
     */
    class EmailMessageSignatureElement extends Element
    {
        protected function renderControlNonEditable()
        {
            /*
            assert('$this->model->{$this->attribute} instanceof EmailMessageSignature');
            $emailMessageSignature = $this->model->{$this->attribute};
            if ($emailMessageSignature->htmlContent != null)
            {
                return Yii::app()->format->html($emailMessageSignature->htmlContent);
            }
            elseif ($emailMessageSignature->textContent != null)
            {
                return Yii::app()->format->text($emailMessageSignature->textContent);
            }*/
        }

        protected function renderControlEditable()
        {
            $emailMessageSignature   = $this->model;
            $attribute               = 'htmlContent';
            $id                      = $this->getEditableInputId  ();
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['rows']     = 3;
            $htmlOptions['cols']     = 10;
            $content  = $this->form->textArea($emailMessageSignature, $attribute, $htmlOptions);
            $content .= $this->renderSaveButton();
            return $content;
        }

        protected function renderLabel()
        {
            $label = Yii::t('Default', 'Email Signature');
            if ($this->form === null)
            {
                return $label;
            }
            else
            {
                return null;
            }
        }

        protected function renderSaveButton()
        {
            $content  = '<span>';
            $content .= ZurmoHtml::ajaxButton(Yii::t('Default', 'Save'),
                Yii::app()->createUrl('emailMessages/default/saveEmailSignature/'),
                    static::resolveAjaxOptionsForSaveEmailSignature($this->form->getId()),
                    array('id' => 'SaveEmailSignature')
            );
            $content .= '</span>';
            return $content;
        }

        protected static function resolveAjaxOptionsForSaveEmailSignature($formId)
        {
            assert('is_string($formId)');
            $ajaxOptions = array();
            $ajaxOptions['type'] = 'POST';
            $ajaxOptions['data'] = 'js:$("#' . $formId . '").serialize()';
            return $ajaxOptions;
        }
    }
?>