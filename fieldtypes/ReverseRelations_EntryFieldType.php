<?php

namespace Craft;

/**
 * Reverse Relations Entry Fieldtype.
 *
 * Fieldtype that allows you to show reverse relations for entries.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@itmundi.nl>
 * @copyright Copyright (c) 2015, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class ReverseRelations_EntryFieldType extends BaseElementFieldType
{
    /**
     * Element Type Entry.
     *
     * @var string
     */
    protected $elementType = ElementType::Entry;

    /**
     * Dont allow a limit to be set.
     *
     * @var bool
     */
    protected $allowLimit = false;

    /**
     * Reverse Entry Relations name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Reverse Entry Relations');
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
            'targetField' => $this->getSettings()->targetField,
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
        foreach ($this->getSettings()->sources as $source) {
            list($type, $id) = explode(':', $source);
            $sources[] = $id;
        }

        // Reverse the criteria
        $criteria->sectionId = $sources;
        $criteria->relatedTo = array(
            'targetElement' => $this->element,
            'field' => $this->getSettings()->targetField,
        );

        // Return criteria
        return $criteria;
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

        // Return input template
        return craft()->templates->render('reverserelations/_field', $variables);
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

        // Return settings
        return $settings;
    }
}
