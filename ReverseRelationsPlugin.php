<?php

namespace Craft;

/**
 * Reverse Relations Plugin.
 *
 * Plugin that allows you to show reverse relations in both the CP and the Site.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@itmundi.nl>
 * @copyright Copyright (c) 2015, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class ReverseRelationsPlugin extends BasePlugin
{
    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Reverse Relations');
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '0.4.0';
    }

    /**
     * Get plugin developer name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Bob Olde Hampsink';
    }

    /**
     * Get plugin developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://github.com/boboldehampsink';
    }

    public function init()
    {
        craft()->on('elements.onBeforeSaveElement', function(Event $event)
        {
            // $event gives us POST(post-populated) data
            // Let's find pre-populated data
            $eventElement = $event->params['element'];
            $element = craft()->elements->getElementById($eventElement->id);

            // If we've found an element
            if ($element)
            {
                foreach ($element->fieldLayout->fields as $field)
                {
                    // Make sure this is a ReverseRelations field
                    if ( ($field->field->type == 'ReverseRelations_User') || ($field->field->type == 'ReverseRelations_Entry') )
                    {
                        craft()->reverseRelations->onBeforeSaveElement($field->field->handle, $element);
                    }
                }
            }
        });
    }
}
