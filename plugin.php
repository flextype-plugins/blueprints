<?php

declare(strict_types=1);

namespace Flextype;

/**
 * Set base admin route
 */
$admin_route = flextype('registry')->get('plugins.admin.settings.route');

include __DIR__ . '/bootstrap.php';
include __DIR__ . '/routes/web.php';
