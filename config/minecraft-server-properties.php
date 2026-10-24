<?php

return [
    // Properties users can see but not change, e.g. max-players when player slots are sold.
    'locked_properties' => array_values(array_filter(array_map('trim', explode(',', env('MINECRAFT_SERVER_PROPERTIES_LOCKED', ''))))),
];
