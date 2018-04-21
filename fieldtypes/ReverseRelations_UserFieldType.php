<?php

namespace Craft;

/**
 * Reverse Relations User Fieldtype.
 *
 * Fieldtype that allows you to show reverse relations for users.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@itmundi.nl>
 * @copyright Copyright (c) 2015, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class ReverseRelations_UserFieldType extends BaseElementFieldType
{
    /**
     * Element Type User.
     *
     * @var string
     */
    protected $elementType = ElementType::User;

    /**
     * Dont allow a limit to be set.
     *
     * @var bool
     */
    protected $allowLimit = false;

    /**
     * Whether the elements have a custom sort order.
     *
     * @var bool $sortable
     */
    protected $sortable = false;

    /**
     * Reverse User Relations name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Reverse User Relations');
    }

    /**
     * Set settings html.
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        // Get parent settings
        $settings = parent::getSettingsHtml();

        // Get available fields
        $fields = array();
        foreach (craft()->fields->getAllFields() as $field) {
            $fields[$field->handle] = $field->name;
        }

        // Add "field" select template
        $fieldSelectTemplate = craft()->templates->render('reverserelations/_settings', array(
            'fields' => $fields,
            'settings' => $this->getSettings(),
        ));

        // Return both
        return $settings.$fieldSelectTemplate;
    }

    /**
     * Prep value for output.
     *
     * @param string $value
     *
     * @return string
     */
    public function prepValue($value)
    {
        // Get parent criteria
        $criteria = parent::prepValue($value);

        // Get sources
        $sources = array();
        if (is_array($this->getSettings()->sources)) {
            foreach ($this->getSettings()->sources as $source) {
                list($type, $id) = explode(':', $source);
                $sources[] = $id;
            }
        }

        // Reverse the criteria
        if (count($sources)) {
            $criteria->groupId = $sources;
        }

        $criteria->relatedTo = array(
            'targetElement' => $this->element,
            'field' => $this->getSettings()->targetField,
        );

        // Return criteria
        return $criteria;
    }

    /**
     * Save relations on the other side.
     */
    public function onAfterElementSave()
    {
        // Get new User ids in source field
        $newSourceIds = $this->element->getContent()->getAttribute($this->model->handle);

        // Get target field handle
        $targetField = $this->getSettings()->targetField;

        $elementType = ['users', 'User'];
        craft()->reverseRelations->addToTarget($this, $targetField, $newSourceIds, $elementType);
    }

    /**
     * Set input html.
     *
     * @param string $name
     * @param array  $criteria
     *
     * @return string
     */
    public function getInputHtml($name, $criteria)
    {
        // Reverse the criteria
        $criteria->relatedTo = array(
            'targetElement' => $this->element,
            'field' => $this->getSettings()->targetField,
        );

        // Get variables
        $variables = $this->getInputTemplateVariables($name, $criteria);

        // Disable adding if we can't save a reverse relation
        $variables['readOnly'] = $this->getSettings()->readOnly || !$this->canSaveReverseRelation($this->getSettings()->targetField);

        // Return input template (local override if exists)
        $template = 'reverserelations/' . $this->inputTemplate;
        $template = craft()->templates->doesTemplateExist($template) ? $template : $this->inputTemplate;

        return craft()->templates->render($template, $variables);
    }

    /**
     * Set settings html.
     *
     * @return array
     */
    protected function defineSettings()
    {
        // Default settings
        $settings = parent::defineSettings();

        // Target field setting
        $settings['targetField'] = AttributeType::String;

        // Read-only setting
        $settings['readOnly'] = AttributeType::Bool;

        // Return settings
        return $settings;
    }

    /**
     * Determine if a field can save a reverse relatoin
     *
     * @return bool
     */
    private function canSaveReverseRelation($fieldHandle)
    {
        $fieldType = craft()->fields->getFieldByHandle($fieldHandle)->fieldType;

        if ($fieldType instanceof MatrixFieldType) {
            return false;
        }

        return true;
    }
}
