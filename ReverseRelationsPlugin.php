<?php

namespace Craft;

class ReverseRelationsPlugin extends BasePlugin
{
    public function getName()
    {
        return Craft::t('Reverse Relations');
    }

    public function getVersion()
    {
        return '0.2';
    }

    public function getDeveloper()
    {
        return 'Bob Olde Hampsink';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.itmundi.nl';
    }
}
