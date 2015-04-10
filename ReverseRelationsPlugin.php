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
        return '0.2';
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
}
