<?php

namespace BobOldeHampsink\ReverseRelations;

use yii\base\Event;
use craft\services\Fields;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use BobOldeHampsink\ReverseRelations\fields\ReverseRelations;

/**
 * Reverse Relations Plugin.
 *
 * Plugin that allows you to show reverse relations in both the CP and the site.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@itmundi.nl>
 * @copyright Copyright (c) 2015, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class Plugin extends BasePlugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ReverseRelations::class;
            }
        );
    }
}
