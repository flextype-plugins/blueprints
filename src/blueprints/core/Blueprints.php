<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype\Plugin\Blueprints;

use Flextype\Entries\Entries;
use Atomastic\Arrays\Arrays;
use Atomastic\Macroable\Macroable;
use function filterCollection;
use function arrays;

class Blueprints
{
    use Macroable;

    /**
     * Create a new blueprints object.
     * 
     * @param array $options  Blueprints entries options.
     * @param array $registry Blueprints entries registry.
     */
    public function __construct(array $options = [], array $registry = [])
    {
        $this->entries = new Entries($options, $registry);
    }

    /**
     * Fetch.
     *
     * @param string $id      Unique identifier of the blueprint.
     * @param array  $options Options array.
     *
     * @return Arrays Returns instance of The Arrays class with items.
     *
     * @access public
     */
    public function fetch(string $id, array $options = []): Arrays
    {
        return $this->entries->fetch($id, $options);
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
        return $this->entries->move($id, $newID);
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
        return $this->entries->create($id, $data);
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
        return $this->entries->delete($id);
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
        return $this->entries->copy($id, $newID);
    }

    /**
     * Check whether blueprint exists.
     *
     * @param string $id Unique identifier of the blueprint.
     *
     * @return bool True on success, false on failure.
     *
     * @access public
     */
    public function has(string $id): bool
    {   
        return $this->entries->has($id);
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
        return $this->entries->update($id, $data);
    }

    /**
     * Get blueprint file location.
     *
     * @param string $id Unique identifier of the blueprint.
     *
     * @return string Blueprint file location.
     *
     * @access public
     */
    public function getFileLocation(string $id): string
    {
        return $this->entries->getFileLocation($id);
    }

    /**
     * Get blueprint directory location.
     *
     * @param string $id Unique identifier of the blueprint.
     *
     * @return string Blueprint directory location.
     *
     * @access public
     */
    public function getDirectoryLocation(string $id): string
    {
        return $this->entries->getDirectoryLocation($id);
    }

    /**
     * Get Cache ID for blueprint.
     *
     * @param  string $id Unique identifier of the blueprint.
     *
     * @return string Cache ID.
     *
     * @access public
     */
    public function getCacheID(string $id): string
    {   
        return $this->entries->getCacheID($id);
    }

    /**
     * Get Blueprints Registry.
     *
     * @return Arrays Returns blueprints registry.
     *
     * @access public
     */
    public function registry(): Arrays
    {
        return $this->entries->registry();
    } 

    /**
     * Set Blueprints registry.
     *
     * @return void
     *
     * @access public
     */
    public function setRegistry(array $registry = [])
    {
        $this->entries->setRegistry($registry);
    }

    /**
     * Set Blueprints options.
     *
     * @return void
     *
     * @access public
     */
    public function setOptions(array $options = []): void 
    {
        $this->entries->setOptions($options);
    } 

    /**
     * Get Blueprints options.
     *
     * @return array Returns blueprints options.
     *
     * @access public
     */
    public function getOptions(): array 
    {
        return $this->entries->getOptions();
    }

    /**
     * Render blueprint.
     *
     * @param string $id     Blueprint unique identifier.
     * @param array  $values Blueprint values.
     * @param array  $vars   Blueprint variables.
     *
     * @return string Rendered blueprint.
     *
     * @access public
     */
    public function render(string $id, array $values = [], array $vars = []): string
    {
        $blueprint = container()->get('blueprints')->fetch($id)->toArray();

        $vars = $this->processVars($blueprint['vars'] ?? [], $vars);
        $this->processEmitter($blueprint['emitter'] ?? [], $vars);
        $this->processActions($blueprint['actions'] ?? [], $vars);  
        $blueprint = $this->processDirectives($blueprint, $vars);

        dd(arrays($blueprint)->dot());

        return twig()
                ->getEnvironment()
                ->render(
                    'plugins/blueprints/blocks/base.html',
                    array_merge([
                        'blueprint' => $blueprint,
                        'values'    => $values,
                        'blocks'    => registry()->get('plugins.blueprints.settings.blocks'),
                    ], $vars));
    }

    /**
     * Render blueprint from array.
     *
     * @param array $blueprint Blueprint array.
     * @param array $values    Blueprint values.
     * @param array $vars      Blueprint variables.
     *
     * @return string Rendered blueprint.
     *
     * @access public
     */
    public function renderFromArray(array $blueprint, array $values = [], array $vars = []): string
    {   
        $vars = $this->processVars($blueprint['vars'] ?? [], $vars);
        $this->processEmitter($blueprint['emitter'] ?? [], $vars);
        $this->processActions($blueprint['actions'] ?? [], $vars);  
        $blueprint = $this->processDirectives($blueprint, $vars);

        return twig()
                ->getEnvironment()
                ->render(
                    'plugins/blueprints/blocks/base.html',
                    array_merge([
                        'blueprint' => $blueprint,
                        'values'    => $values,
                        'blocks'    => registry()->get('plugins.blueprints.settings.blocks'),
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
     * Get instance of The Form class.
     *
     * @param array $data Form data.
     *
     * @return Returns instance of The Form class.
     *
     * @access public
     */   
    public function form(array $data): Form
    {
        return new Form($data);
    }

    /**
     * Process directives for blueprint field values.
     *
     * @param array $blueprintDirectives Blueprint array directives.
     * @param array $vars                Blueprint variables.
     * 
     * @return array Bluprint with processed directives.
     *
     * @access private
     */
    private function processDirectives(array $blueprintDirectives, array $vars = []): array 
    {
        emitter()->emit('onBlueprintsBeforeProcessedDirectives');

        $flatBlueprint      = arrays($blueprintDirectives)->dot();
        $processedBlueprint = [];

        foreach($flatBlueprint as $key => $value) {
            if (strings($value)->startsWith('@parsers:')) {
                $parsers = strings($value)->after('@parsers:')->toString();
                $arguments = strings(strtok($parsers, ';'))->toArray(',');

                foreach ($arguments as $argument) {
                    switch ($argument) {
                        case 'twig':
                            $value = twig()->fetchFromString($value, $vars);
                            break;
                        case 'shortcode':
                            $value = parsers()->shortcodes()->parse($value);
                            break;
                        case 'markdown':
                            $value = parsers()->markdown()->parse($value);
                            break;           
                        default:
                            $value = parsers()->{$argument}()->parse($value);
                            break;
                    }

                    $processedBlueprint[$key] = $value;
                }

                $processedBlueprint[$key] = strings($processedBlueprint[$key])->replace('@parsers:' . implode(',', $arguments) . ';', '')->trim()->toString();
            } else {
                $processedBlueprint[$key] = $value;
            }
        }

        emitter()->emit('onBlueprintsAfterProcessedDirectives');

        return arrays($processedBlueprint)->undot()->toArray();
    }

    /**
     * Process emitter for blueprint
     *
     * @param array blueprintEmitter Blueprint array with emitter.
     * 
     * @return void
     *
     * @access private
     */
    private function processEmitter(array $blueprintEmitter, array $vars = []): void
    {
        emitter()->emit('onBlueprintsBeforeProcessedEmitter');

        // Emmit events
        if (isset($blueprintEmitter['emit']) && is_array($blueprintEmitter['emit'])) {
            foreach ($blueprintEmitter['emit'] as $key => $event) {
                emitter()->emit($event['name']);
            }
        }

        // Register listeners 
        if (isset($blueprintEmitter['addListener']) && is_array($blueprintEmitter['addListener'])) {
            foreach ($blueprintEmitter['addListener'] as $key => $event) {
                emitter()->addListener($event['name'], function() use ($event, $vars) {
                    
                    // Get event vars
                    $eventVars = [];
                    if (isset($event['properties']['vars'])) {
                        $eventVars = $this->processVars($event['properties']['vars'] ?? [], $vars);
                    }
                    
                    // Get event value
                    if (isset($event['properties']['value'])) {
                        strings($this->processDirectives(['value' => $event['properties']['value']], arrays($eventVars)->merge($vars)->toArray())['value'])->trim()->echo();
                    }

                });
            }
        }

        emitter()->emit('onBlueprintsAfterProcessedEmitter');
    }

    /**
     * Process actions for blueprint
     *
     * @param array $blueprintActions Blueprint array with actions.
     * 
     * @return void
     *
     * @access private
     */
    private function processActions(array $blueprintActions, array $vars = []): void
    {
        emitter()->emit('onBlueprintsBeforeProcessedActions');
        
        if (isset($blueprintActions['get']) && is_array($blueprintActions['get'])) {
            foreach($blueprintActions['get'] as $action) {
                if (actions()->has($action['name'])) {
                    if (isset($action['properties']['vars']) && is_array($action['properties']['vars'])) {
                        $properties = array_values($this->processVars($action['properties']['vars'] ?? [], $vars));
                        actions()->get($action['name'])(...$properties);
                    } else {
                        actions()->get($action['name'])();
                    }
                }
            }
        }

        emitter()->emit('onBlueprintsAfterProcessedActions');
    }

    /**
     * Process vars for blueprint
     *
     * @param array $blueprintVars Blueprint vars array.
     * 
     * @return void
     *
     * @access private
     */
    private function processVars(array $blueprintVars, array $vars): array
    {
        emitter()->emit('onBlueprintsBeforeProcessedVars');

        $blueprintVars = $this->processDirectives($blueprintVars, $vars);
        $processVars = [];

        foreach ($blueprintVars as $key => $var) {
            $varType = isset($var['type']) ? $var['type'] : 'string';
            switch ($varType) {
                case 'array':
                    if (is_iterable($var['value'])) {

                        array_walk_recursive($var['value'], function(&$value, $key) {
                            $value = strings($value)->trim()->toString();
                        });

                        $processVars[$var['name']] = $var['value'];
                        
                    } else {
                        $value = htmlspecialchars_decode($var['value']);
                        $processVars[$var['name']] = serializers()->json()->decode($value);
                    }
                    break;
                case 'bool':
                    $processVars[$var['name']] = strings($var['value'])->trim()->toBoolean();
                    break;
                case 'float':
                    $processVars[$var['name']] = strings($var['value'])->trim()->toFloat();
                    break;    
                case 'int':
                    $processVars[$var['name']] = strings($var['value'])->trim()->toInteger();
                    break;
                case 'string':
                default:
                    $processVars[$var['name']] = strings($var['value'])->trim()->toString();
                    break;
            }
        }

        emitter()->emit('onBlueprintsAfterProcessedVars');

        return arrays($vars)->merge($processVars)->toArray();
    }
}
