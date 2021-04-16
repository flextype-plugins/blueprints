<?php

declare(strict_types=1);

/**
 * @link https://flextype.org
 *
 * For the full copyright and license inblueprintation, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Blueprints;

use Flextype\Plugin\Blueprints\Models\Blueprints;
use Flextype\Plugin\Blueprints\Twig\BlueprintsTwigExtension;
use Flextype\Plugin\Twig\Twig\FlextypeTwig;
use function array_merge;
use function strtolower;
use function substr;

/**
 * Add Blueprints Model to Flextype container
 */
flextype()->container()['blueprints'] = fn() => new Blueprints();

/**
 * Add Blueprints Twig extension
 */
FlextypeTwig::macro('blueprints', fn() => flextype('blueprints'));

/**
 * Add Assets
 */
$adminCSS = flextype('registry')->has('assets.admin.css') ? flextype('registry')->get('assets.admin.css') : [];
$siteCSS  = flextype('registry')->has('assets.site.css') ? flextype('registry')->get('assets.site.css') : [];

if (flextype('registry')->get('plugins.blueprints.settings.load_on_admin')) {
    flextype('registry')->set(
        'assets.admin.css',
        array_merge($adminCSS, [
            'project/plugins/blueprints/assets/dist/css/blueprints.min.css?v=' . filemtime('project/plugins/blueprints/assets/dist/css/blueprints.min.css'),
        ])
    );
}

if (flextype('registry')->get('plugins.blueprints.settings.load_on_site')) {
    flextype('registry')->set(
        'assets.site.css',
        array_merge($siteCSS, [
            'project/plugins/blueprints/assets/dist/css/blueprints.min.css?v=' . filemtime('project/plugins/blueprints/assets/dist/css/blueprints.min.css'),
        ])
    );
}

if (flextype('registry')->get('flextype.settings.locale') === 'en_US') {
    $locale = 'en';
} else {
    $locale = substr(strtolower(flextype('registry')->get('flextype.settings.locale')), 0, 2);
}

if ($locale !== 'en') {
    $trumbowygLocaleJS = 'project/plugins/blueprints/assets/dist/lang/trumbowyg/langs/' . $locale . '.min.js';
    $flatpickrLocaleJS = 'project/plugins/blueprints/assets/dist/lang/flatpickr/l10n/' . $locale . '.js';
} else {
    $trumbowygLocaleJS = '';
    $flatpickrLocaleJS = '';
}

$adminJS = flextype('registry')->has('assets.admin.js') ? flextype('registry')->get('assets.admin.js') : [];
$siteJS  = flextype('registry')->has('assets.site.js') ? flextype('registry')->get('assets.site.js') : [];

if (flextype('registry')->get('plugins.blueprints.settings.load_on_admin')) {
    flextype('registry')->set(
        'assets.admin.js',
        array_merge($adminJS, [
            'project/plugins/blueprints/assets/dist/js/blueprints.min.js?v=' . filemtime('project/plugins/blueprints/assets/dist/js/blueprints.min.js'),
            $trumbowygLocaleJS,
            $flatpickrLocaleJS,
        ])
    );
}

if (flextype('registry')->get('plugins.blueprints.settings.load_on_site')) {
    flextype('registry')->set(
        'assets.site.js',
        array_merge($siteJS, [
            'project/plugins/blueprints/assets/dist/js/blueprints.min.js?v=' . filemtime('project/plugins/blueprints/assets/dist/js/blueprints.min.js'),
            $trumbowygLocaleJS,
            $flatpickrLocaleJS,
        ])
    );
}
