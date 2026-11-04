<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Thread route
    |--------------------------------------------------------------------------
    |
    | The host's named route for a thread page, taking the thread as its one
    | parameter. The staff screens link to it; with no such route they show no
    | link. This package has no public pages of its own.
    |
    */
    'thread_route' => 'threads.show',

    /*
    |--------------------------------------------------------------------------
    | User attribute
    |--------------------------------------------------------------------------
    |
    | The attribute that names a person on a bug report. Null uses core's
    | `filagora.user_title_attribute`.
    |
    */
    'user_attribute' => null,

];
