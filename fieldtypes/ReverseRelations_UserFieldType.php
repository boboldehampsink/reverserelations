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

        $sources = $this->getSources();

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
     * Get Sources IDs (User Groups).
     *
     * @return Array of Relation User Group IDs
     */
    public function getSources()
    {
        // Get sources
        $sources = array();
        if (is_array($this->getSettings()->sources))
        {
            foreach ($this->getSettings()->sources as $source)
            {
                list($type, $id) = explode(':', $source);
                $sources[] = $id;
            }
        }
        return $sources;
    }


      /**
      * Fetch user IDs from related (target) element
      *
      * @param object $sourceElement
      * @param object $targetField
      *
      * @return array
      */
      public function getTargetUsers($sourceElement, $targetField)
      {
        // Get Applicable User Groups. Sources == User Groups
        $sourcesArray = $this->getSources();
        $sourceIds = array();

        foreach ($sourcesArray as $source)
        {
            // Find all users that belong to this User Group
            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->groupId = $source;
            $criteria->relatedTo = array(
                'field' => $targetField,
                'targetElement' => $sourceElement,
            );
            $criteria->limit = null;
            $criteria->find();

            // Get IDs for each user, add to array
            foreach ($criteria as $user) {
                $sourceIds[] = $user->id;
            }

        }

        return $sourceIds;
      }



    /**
     * Save relations on the other side.
     */
    public function onAfterElementSave()
    {
        // Get target field handle and fieldtype
        $targetField = $this->getSettings()->targetField;
        $fieldType = craft()->fields->getFieldByHandle($targetField)->fieldType;

        if ($fieldType instanceof MatrixFieldType) {
            return false;
        }

        //Get (before-save) relation target ids
        $oldTargetIds = $this->getTargetUsers($this->element, $targetField);

        // Get (after-save) relation target ids
        $newTargetIds = $this->element->getContent()->getAttribute($this->model->handle);

        // Make sure we have new targets
        if (is_array($newTargetIds)) {

            // Loop through targets
            foreach ($newTargetIds as $targetId) {

                // Find missing IDs after save
                $deleteIds = array_diff($oldTargetIds, $newTargetIds);

                // Get target
                $target = craft()->users->getUserById($targetId);

                // Set this element on that user
                $target->getContent()->{$targetField} = array_merge($target->{$targetField}->ids(), array($this->element->id));

                // Save target
                craft()->users->saveUser($target);
            }

            // Find missing IDs after save
            $deleteIds = array_diff($oldTargetIds, $newTargetIds);

        } else {

            // No New Target IDs
            $deleteIds = $oldTargetIds;
        }

        // Make sure we have users to remove from field
        if ($deleteIds)
        {
            foreach($deleteIds as $deletedId)
            {
                $target = craft()->users->getUserById($deletedId);
                $fieldIds = $target->{$targetField}->ids();

                // Remove the element ID
                $key = array_search($this->element->id, $fieldIds);
                if ($key !== false)
                {
                    unset($fieldIds[$key]);
                }

                $target->getContent()->{$targetField} = $fieldIds;

                // Save target
                craft()->users->saveUser($target);
            }
        }

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
