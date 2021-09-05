<?php

declare(strict_types=1);

/**
 * @link https://flextype.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Blueprints;

use Flextype\Plugin\Blueprints\Blueprints;
use Flextype\Plugin\Twig\Extension\FlextypeTwig;
use Flextype\Plugin\Blueprints\Twig\BlueprintsTwig;
use function array_merge;
use function strtolower;
use function substr;
use function is_file;

/**
 * Ensure vendor libraries exist
 */
! is_file($blueprints_autoload = __DIR__ . '/vendor/autoload.php') and exit('Please run: <i>composer install</i> form plugin');

/**
 * Register The Auto Loader
 *
 * Composer provides a convenient, automatically generated class loader for
 * our application. We just need to utilize it! We'll simply require it
 * into the script here so that we don't have to worry about manual
 * loading any of our classes later on. It feels nice to relax.
 * Register The Auto Loader
 */
$blueprints_loader = require_once $blueprints_autoload;

/**
 * Add Blueprints Model to Flextype container
 */
container()->set('blueprints', new Blueprints(registry()->get('plugins.blueprints.settings.entries')));

/**
 * Add Blueprints Twig
 */
//FlextypeTwig::macro('blueprints', new BlueprintsTwig());

$blueprintsJS[]  = 'project/plugins/blueprints/assets/dist/js/blueprints.min.js';
$blueprintsCSS[] = 'project/plugins/blueprints/assets/dist/css/blueprints.min.css';

if (registry()->get('plugins.blueprints.settings.assetsLoadOnAdmin')) {
    registry()->set('assets.admin.js.blueprints', $blueprintsJS);
    registry()->set('assets.admin.css.blueprints', $blueprintsCSS);
}

if (registry()->get('plugins.blueprints.settings.assetsLoadOnSite')) {
    registry()->set('assets.site.js.blueprints', $blueprintsJS);
    registry()->set('assets.site.css.blueprints', $blueprintsCSS);
}
