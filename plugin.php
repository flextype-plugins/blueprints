<?php

declare(strict_types=1);

/**
 * @link https://flextype.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Blueprints;

use Flextype\Plugin\Blueprints\Models\Blueprints;
use Flextype\Plugin\Twig\Twig\FlextypeTwig;
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
flextype()->container()['blueprints'] = fn() => new Blueprints();

/**
 * Add Blueprints Twig extension
 */
FlextypeTwig::macro('blueprints', fn() => flextype('blueprints'));

$blueprintsJS[]  = 'project/plugins/blueprints/assets/dist/js/blueprints.min.js';
$blueprintsCSS[] = 'project/plugins/blueprints/assets/dist/css/blueprints.min.css';

if (flextype('registry')->get('flextype.settings.locale') == 'en_US') {
    $locale = 'en';
} else {
    $locale = strings(flextype('registry')->get('flextype.settings.locale'))->lower()->substr(0, 2)->toString();
}

if ($locale !== 'en') {
    $blueprintsJS[] = 'project/plugins/blueprints/assets/dist/lang/flatpickr/l10n/' . $locale . '.js';          
} 

if (flextype('registry')->get('plugins.trumbowyg.settings.assetsLoadOnAdmin')) {
    flextype('registry')->set('assets.admin.js.blueprints', $blueprintsJS);
    flextype('registry')->set('assets.admin.css.blueprints', $blueprintsCSS);
}

if (flextype('registry')->get('plugins.trumbowyg.settings.assetsLoadOnSite')) {
    flextype('registry')->set('assets.site.js.blueprints', $blueprintsJS);
    flextype('registry')->set('assets.site.css.blueprints', $blueprintsCSS);
}
