<?php

namespace Craft;

/**
 * Reverse Relations Service for Fieldtype.
 */

class ReverseRelationsService extends BaseApplicationComponent
{

  private $oldSourceElements = array();

  public function onBeforeSaveElement($sourceFieldHandle, $parentElement)
  {
    // Store elements selected in Relation field in private variable
    $this->oldSourceElements[$sourceFieldHandle] = $parentElement->{$sourceFieldHandle};
  }

  public function addToTarget($element, $targetField, $newSourceIds, $elementType)
  {
    // If within Matrix, let's stop here
    $fieldType = craft()->fields->getFieldByHandle($targetField)->fieldType;
    if ($fieldType instanceof MatrixFieldType) {
      return false;
    }

    // Make sure we have new sources in field
    if (is_array($newSourceIds))
    {
      // Loop through field sources
      foreach ($newSourceIds as $sourceId)
      {
        // Get source
        $source = craft()->{$elementType[0]}->{'get'.$elementType[1].'ById'}($sourceId);

        // Set this source element on that target element
        $source->getContent()->{$targetField} = array_merge($source->{$targetField}->ids(), array($element->element->id));

        // Save target
        craft()->{$elementType[0]}->{'save'.$elementType[1]}($source);
      }

      // Continue on to check which elements we'll need to remove
      $this->checkForAndDelete($element, $targetField, $newSourceIds, $elementType, $removeAll = false);

    } else {
      // Continue on to remove all elements (if any) in our reverse relations field
      $this->checkForAndDelete($element, $targetField, $newSourceIds, $elementType, $removeAll = true);
    }
  }

  public function checkForAndDelete($element, $targetField, $newSourceIds, $elementType, $removeAll)
  {
    $oldSourceIds = array();
    foreach($this->oldSourceElements[$element->model->handle] as $oldSourceRelation) {
      $oldSourceIds[] = $oldSourceRelation->id;
    }

    // If field was populated before
    if (count($oldSourceIds))
    {
      if ($removeAll != true)
      {
        // Figure out sources to remove from reverse relationship field
        $deleteIds = array_diff($oldSourceIds, $newSourceIds);
      } else {
        // Remove all sources from reverse relationship field
        $deleteIds = $oldSourceIds;
      }

      foreach($deleteIds as $deletedId)
      {
        // Get target
        $target = craft()->{$elementType[0]}->{'get'.$elementType[1].'ById'}($deletedId);

        // Get elementIDs that currently populate target field
        $fieldIds = $target->{$targetField}->ids();

        // If source elementID is found in target's field, remove it
        $key = array_search($element->element->id, $fieldIds);
        if ($key !== false)
        {
          unset($fieldIds[$key]);
        }

        // Set the target field again with new list of source element(s)
        $target->getContent()->{$targetField} = $fieldIds;

        // Save target element
        craft()->{$elementType[0]}->saveUser($target);
      }
    }
  }
}
