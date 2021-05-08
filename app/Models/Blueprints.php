<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype\Plugin\Blueprints\Models;

use Atomastic\Arrays\Arrays;
use Atomastic\Macroable\Macroable;

class Blueprints
{
    use Macroable;

    /**
     * Blueprints Storage
     *
     * Used for storing current requested blueprints data
     * and allow to change them on fly.
     *
     * @var Arrays
     * @access private
     */
    private Arrays $storage;

    /**
     *  __construct
     */
    public function __construct()
    {
        $this->storage = arrays();
    }

    /**
     * Get Blueprints Storage
     *
     * @return Arrays
     */
    public function storage(): Arrays
    {
        return $this->storage;
    }

    /**
     * Fetch.
     *
     * @param string $id      Unique identifier of the blueprint.
     * @param array  $options Options array.
     *
     * @access public
     *
     * @return Arrays Returns instance of The Arrays class with items.
     */
    public function fetch(string $id, array $options = []): Arrays
    {
      // Store data
      $this->storage()->set('fetch.id', $id);
      $this->storage()->set('fetch.options', $options);
      $this->storage()->set('fetch.data', []);

      // Run event: onBlueprintsFetch
      flextype('emitter')->emit('onBlueprintsFetch');

      // Single fetch helper
      $single = function ($id, $options) {

          // Store data
          $this->storage()->set('fetch.id', $id);
          $this->storage()->set('fetch.options', $options);
          $this->storage()->set('fetch.data', []);

          // Run event: onBlueprintsFetchSingle
          flextype('emitter')->emit('onBlueprintsFetchSingle');

          // Get Cache ID for current requested blueprint
          $blueprintCacheID = $this->getCacheID($this->storage()->get('fetch.id'));

          // 1. Try to get current requested blueprint from cache
          if (flextype('cache')->has($blueprintCacheID)) {

              // Fetch blueprint from cache and Apply filter for fetch data
              $this->storage()->set('fetch.data', filter(flextype('cache')->get($blueprintCacheID),
                                                       $this->storage()->get('fetch.options.filter', [])));

              // Run event: onBlueprintsFetchSingleCacheHasResult
              flextype('emitter')->emit('onBlueprintsFetchSingleCacheHasResult');

              // Return blueprint from cache
              return arrays($this->storage()->get('fetch.data'));
          }

          // 2. Try to get current requested blueprint from filesystem
          if ($this->has($this->storage()->get('fetch.id'))) {
              // Get blueprint file location
              $blueprintFile = $this->getFileLocation($this->storage()->get('fetch.id'));

              // Try to get requested blueprint from the filesystem
              $blueprintFileContent = filesystem()->file($blueprintFile)->get();

              if ($blueprintFileContent === false) {
                  // Run event: onBlueprintsFetchSingleNoResult
                  flextype('emitter')->emit('onBlueprintsFetchSingleNoResult');
                  return arrays($this->storage()->get('fetch.data'));
              }

              // Decode blueprint file content
              $this->storage()->set('fetch.data', flextype('serializers')->yaml()->decode($blueprintFileContent));

              // Run event: onBlueprintsFetchSingleHasResult
              flextype('emitter')->emit('onBlueprintsFetchSingleHasResult');

              // Apply filter for fetch data
              $this->storage()->set('fetch.data', filter($this->storage()->get('fetch.data'),
                                                         $this->storage()->get('fetch.options.filter', [])));

              // Set cache state
              $cache = $this->storage()->get('fetch.data.cache.enabled',
                                             flextype('registry')->get('flextype.settings.cache.enabled'));

               // Save blueprint data to cache
              if ($cache) {
                  flextype('cache')->set($blueprintCacheID, $this->storage()->get('fetch.data'));
              }

              // Return blueprint data
              return arrays($this->storage()->get('fetch.data'));
          }

          // Run event: onBlueprintsFetchSingleNoResult
          flextype('emitter')->emit('onBlueprintsFetchSingleNoResult');

          // Return empty array if blueprint is not founded
          return arrays($this->storage()->get('fetch.data'));
      };

      if (isset($this->storage['fetch']['options']['collection']) &&
          strings($this->storage['fetch']['options']['collection'])->isTrue()) {

          // Run event: onBlueprintsFetchCollection
          flextype('emitter')->emit('onBlueprintsFetchCollection');

          if (! $this->getDirectoryLocation($id)) {
              // Run event: onBlueprintsFetchCollectionNoResult
              flextype('emitter')->emit('onBlueprintsFetchCollectionNoResult');

              // Return blueprintss array
              return arrays($this->storage()->get('fetch.data'));
          }

          // Find blueprints in the filesystem
          $blueprints = find($this->getDirectoryLocation($id),
                                                      isset($options['find']) ?
                                                            $options['find'] :
                                                            []);

          // Walk through blueprints results
          if ($blueprints->hasResults()) {

              $data = [];

              foreach ($blueprints as $currenBlueprint) {
                  if ($currenBlueprint->getType() !== 'file' || $currenBlueprint->getFilename() !== 'blueprint.yaml') {
                      continue;
                  }

                  $currentBlueprintID = strings($currenBlueprint->getPath())
                                          ->replace('\\', '/')
                                          ->replace(PATH['project'] . '/blueprints/', '')
                                          ->trim('/')
                                          ->toString();

                  $data[$currentBlueprintID] = $single($currentBlueprintID, [])->toArray();
              }

              $this->storage()->set('fetch.data', $data);

              // Run event: onBlueprintsFetchCollectionHasResult
              flextype('emitter')->emit('onBlueprintsFetchCollectionHasResult');

              // Apply filter for fetch data
              $this->storage()->set('fetch.data', filter($this->storage()->get('fetch.data'),
                                                       isset($options['filter']) ?
                                                             $options['filter'] :
                                                             []));
          }

          // Run event: onBlueprintsFetchCollectionNoResult
          flextype('emitter')->emit('onBlueprintsFetchCollectionNoResult');

          // Return blueprints array
          return arrays($this->storage()->get('fetch.data'));
      } else {
          return $single($this->storage['fetch']['id'],
                         $this->storage['fetch']['options']);
      }
    }

    /**
     * Move blueprint.
     *
     * @param string $id    Unique identifier of the blueprint.
     * @param string $newID New Unique identifier of the blueprint.
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function move(string $id, string $newID): bool
    {
        // Store data
        $this->storage()->set('move.id', $id);
        $this->storage()->set('move.newID', $newID);

        // Run event: onBlueprintsMove
        flextype('emitter')->emit('onBlueprintsMove');

        if (! $this->has($this->storage()->get('move.newID'))) {
            return filesystem()
                        ->directory($this->getDirectoryLocation($this->storage()->get('move.id')))
                        ->move($this->getDirectoryLocation($this->storage()->get('move.newID')));
        }

        return false;
    }

    /**
     * Update blueprint.
     *
     * @param string $id   Unique identifier of the blueprint.
     * @param array  $data Data to update for the blueprint.
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function update(string $id, array $data): bool
    {
        // Store data
        $this->storage()->set('update.id', $id);
        $this->storage()->set('update.data', $data);

        // Run event: onBlueprintsUpdate
        flextype('emitter')->emit('onBlueprintsUpdate');

        $blueprintFile = $this->getFileLocation($this->storage()->get('update.id'));

        if (filesystem()->file($blueprintFile)->exists()) {
            $body  = filesystem()->file($blueprintFile)->get();
            $blueprint = flextype('serializers')->yaml()->decode($body);

            return (bool) filesystem()->file($blueprintFile)->put(flextype('serializers')->yaml()->encode(array_merge($blueprint, $this->storage()->get('update.data'))));
        }

        return false;
    }

    /**
     * Create blueprint.
     *
     * @param string $id   Unique identifier of the blueprint.
     * @param array  $data Data to create for the blueprint.
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function create(string $id, array $data = []): bool
    {
        // Store data
        $this->storage()->set('create.id', $id);
        $this->storage()->set('create.data', $data);

        // Run event: onBlueprintsCreate
        flextype('emitter')->emit('onBlueprintsCreate');

        // Create blueprint directory first if it is not exists
        $blueprintDir = $this->getDirectoryLocation($this->storage()->get('create.id'));

        if (
            ! filesystem()->directory($blueprintDir)->exists() &&
            ! filesystem()->directory($blueprintDir)->create()
        ) {
            return false;
        }

        // Create blueprint file
        $blueprintFile = $blueprintDir . '/blueprint.yaml';
        if (! filesystem()->file($blueprintFile)->exists()) {
            return (bool) filesystem()->file($blueprintFile)->put(flextype('serializers')->yaml()->encode($this->storage()->get('create.data')));
        }

        return false;
    }

    /**
     * Delete blueprint.
     *
     * @param string $id Unique identifier of the blueprint.
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function delete(string $id): bool
    {
        // Store data
        $this->storage()->set('delete.id', $id);

        // Run event: onBlueprintsDelete
        flextype('emitter')->emit('onBlueprintsDelete');

        return filesystem()
                    ->directory($this->getDirectoryLocation($this->storage()->get('delete.id')))
                    ->delete();
    }

    /**
     * Copy blueprint.
     *
     * @param string $id    Unique identifier of the blueprint.
     * @param string $newID New Unique identifier of the blueprint.
     *
     * @return bool|null True on success, false on failure.
     *
     * @access public
     */
    public function copy(string $id, string $newID): ?bool
    {
        // Store data
        $this->storage()->set('copy.id', $id);
        $this->storage()->set('copy.newID', $newID);

        // Run event: onBlueprintsCopy
        flextype('emitter')->emit('onBlueprintsCopy');

        return filesystem()
                    ->directory($this->getDirectoryLocation($this->storage()->get('copy.id')))
                    ->copy($this->getDirectoryLocation($this->storage()->get('copy.newID')));
    }

    /**
     * Check whether blueprint exists.
     *
     * @param string $id Unique identifier of the blueprint(blueprints).
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function has(string $id): bool
    {
        // Store data
        $this->storage()->set('has.id', $id);

        // Run event: onBlueprintHas
        flextype('emitter')->emit('onBlueprintsHas');

        return filesystem()->file($this->getFileLocation($this->storage()->get('has.id')))->exists();
    }

    /**
     * Render blueprint.
     *
     * @param string $id     Blueprint unique identifier.
     * @param array  $values Blueprint values.
     * @param array  $vars   Blueprint variables.
     *
     * @return void
     *
     * @access public
     */
    public function render(string $id, array $values = [], array $vars = []): void
    {
        echo flextype('twig')
                ->getEnvironment()
                ->render(
                    'plugins/blueprints/blocks/base.html',
                    array_merge([
                        'blueprint' => $this->fetch($id)->toArray(),
                        'values'    => $values,
                        'query'     => $_GET,
                        'blocks'    => flextype('registry')->get('plugins.blueprints.settings.blocks'),
                    ], $vars));
    }

    /**
     * Render blueprint from array.
     *
     * @param array $blueprint Blueprint array.
     * @param array $values    Blueprint values.
     * @param array $vars      Blueprint variables.
     *
     * @return void
     *
     * @access public
     */
    public function renderFromArray(array $blueprint, array $values = [], array $vars = []): void
    {
        echo flextype('twig')
                ->getEnvironment()
                ->render(
                    'plugins/blueprints/blocks/base.html',
                    array_merge([
                        'blueprint' => $blueprint,
                        'values'    => $values,
                        'query'     => $_GET,
                        'blocks'    => flextype('registry')->get('plugins.blueprints.settings.blocks'),
                    ], $vars));
    }

    /**
     * Get blueprint block name.
     *
     * @param string $name Block name.
     *
     * @return string Returns blueprint block name.
     *
     * @access public
     */
    public function getBlockName(string $name) : string
    {
        $pos = strpos($name, '.');

        if ($pos === false) {
            $blockName = $name;
        } else {
            $blockName = str_replace('.', '][', "$name") . ']';
        }

        $pos = strpos($blockName, ']');

        if ($pos !== false) {
            $blockName = substr_replace($blockName, '', $pos, strlen(']'));
        }

        return $blockName;
    }

    /**
     * Get blueprint block ID.
     *
     * @param string $id Block ID.
     *
     * @return string Returns blueprint block ID.
     *
     * @access public
     */
    public function getBlockID(string $id) : string
    {
        $pos = strpos($id, '.');

        if ($pos === false) {
            $blockID = $id;
        } else {
            $blockID = str_replace('.', '_', "$id");
        }

        return $blockID;
    }

    /**
     * Get blueprint file location
     *
     * @param string $id Unique identifier of the blueprint(blueprints).
     *
     * @return string blueprint file location
     *
     * @access public
     */
    public function getFileLocation(string $id): string
    {
        return PATH['project'] . '/blueprints/' . $id . '/blueprint.yaml';
    }

    /**
     * Get blueprint directory location
     *
     * @param string $id Unique identifier of the blueprint(blueprints).
     *
     * @return string blueprint directory location
     *
     * @access public
     */
    public function getDirectoryLocation(string $id): string
    {
        return PATH['project'] . '/blueprints/' . $id;
    }

    /**
     * Get Cache ID for blueprint
     *
     * @param  string $id Unique identifier of the blueprint(blueprints).
     *
     * @return string Cache ID
     *
     * @access public
     */
    public function getCacheID(string $id): string
    {
        if (flextype('registry')->get('flextype.settings.cache.enabled') === false) {
            return '';
        }

        $blueprintFile = $this->getFileLocation($id);

        if (filesystem()->file($blueprintFile)->exists()) {
            return strings('blueprint' . $blueprintFile . (filesystem()->file($blueprintFile)->lastModified() ?: ''))->hash()->toString();
        }

        return strings('blueprint' . $blueprintFile)->hash()->toString();
    }
}
