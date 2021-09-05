<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

if (! function_exists('blueprints')) {
    /**
     * Get Flextype Blueprints Service.
     */
    function blueprints()
    {
        return container()->get('blueprints');
    }
}