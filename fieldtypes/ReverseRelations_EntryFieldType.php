<?php

namespace Craft;

class ReverseRelations_EntryFieldType extends BaseElementFieldType
{
    // Element Type Entry
    protected $elementType = 'Entry';

    // Dont allow a limit to be set
    protected $allowLimit = false;

    // Reverse Entry Relations name
    public function getName()
    {
        return Craft::t('Reverse Entry Relations');
    }

    // Set button label
    protected function getAddButtonLabel()
    {
        return Craft::t('Add an entry');
    }

    // Set settings html
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
            'targetField' => $this->getSettings()->targetField,
        ));

        // Return both
        return $settings.$fieldSelectTemplate;
    }

    // Prep value for output
    public function prepValue($value)
    {

        // Get parent criteria
        $criteria = parent::prepValue($value);

        // Reverse the criteria
        $criteria->relatedTo = array(
            'targetElement' => $this->element,
            'field' => $this->getSettings()->targetField,
        );

        // Return criteria
        return $criteria;
    }

    // Set input html
    public function getInputHtml($name, $criteria)
    {

        // Reverse the criteria
        $criteria->relatedTo = array(
            'targetElement' => $this->element,
            'field' => $this->getSettings()->targetField,
        );

        // Get variables
        $variables = $this->getInputTemplateVariables($name, $criteria);

        // Return input template
        return craft()->templates->render('reverserelations/_field', $variables);
    }

    // Set settings html
    protected function defineSettings()
    {

        // Default settings
        $settings['sources'] = AttributeType::Mixed;
        $settings['targetLocale'] = AttributeType::String;

        // Target field setting
        $settings['targetField'] = AttributeType::String;

        // Return settings
        return $settings;
    }
}
