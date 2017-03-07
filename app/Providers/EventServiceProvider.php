<?php

namespace Zync\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Zync\Events\SomeEvent' => [
            'Zync\Listeners\EventListener',
        ],
    ];
}
