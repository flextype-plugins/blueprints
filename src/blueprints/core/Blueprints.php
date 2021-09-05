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
     * @return void
     *
     * @access public
     */
    public function render(string $id, array $values = [], array $vars = []): void
    {
        $blueprint = container()->get('blueprints')->fetch($id)->toArray();

        $vars      = $this->processVars($blueprint, $vars);
        $blueprint = $this->processDirectives($blueprint, $vars);
        $this->processEmitter($blueprint, $vars);
        $this->processActions($blueprint, $vars);  

  
        echo container()->get('twig')
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
     * @return void
     *
     * @access public
     */
    public function renderFromArray(array $blueprint, array $values = [], array $vars = []): void
    {   
        $vars      = $this->processVars($blueprint, $vars);
        $blueprint = $this->processDirectives($blueprint, $vars);
        $this->processEmitter($blueprint, $vars);
        $this->processActions($blueprint, $vars);

        echo container()->get('twig')
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
        if (registry()->get('flextype.settings.cache.enabled') === false) {
            return '';
        }

        $blueprintFile = $this->getFileLocation($id);

        if (filesystem()->file($blueprintFile)->exists()) {
            return strings('blueprint' . $blueprintFile . (filesystem()->file($blueprintFile)->lastModified() ?: ''))->hash()->toString();
        }

        return strings('blueprint' . $blueprintFile)->hash()->toString();
    }

   /**
     * Process directives for blueprint field values.
     *
     * @param array $blueprint Blueprint array.
     * @param array $vars      Blueprint variables.
     * 
     * @return void
     *
     * @access private
     */
    private function processDirectives(array $blueprint, array $vars): array 
    {
        $flatBlueprint   = arrays($blueprint)->dot();
        $parsedBlueprint = [];

        foreach($flatBlueprint as $key => $value) {
            if (strings($value)->startsWith('@parsers:')) {
                $parsers = strings($value)->after('@parsers:')->toString();
                $arguments = strings(strtok($parsers, ';'))->toArray(',');

                foreach ($arguments as $argument) {
                    switch ($argument) {
                        case 'twig':
                            $value = container()->get('twig')->fetchFromString($value, $vars);
                            break;
                        case 'shortcode':
                            $value = parsers()->shortcodes()->process($value);
                            break;
                        case 'markdown':
                            $value = parsers()->markdown()->parse($value);
                            break;           
                        default:
                            $value = parsers()->{$argument}()->parse($value);
                            break;
                    }

                    $parsedBlueprint[$key] = $value;
                }

               $parsedBlueprint[$key] = strings($parsedBlueprint[$key])->replace('@parsers:' . implode(',', $arguments) . ';', '')->trim()->toString();
            } else {
                $parsedBlueprint[$key] = $value;
            }
        }
        return arrays($parsedBlueprint)->undot()->toArray();
    }

    /**
     * Process emitter for blueprint
     *
     * @param array $blueprint Blueprint array.
     * 
     * @return void
     *
     * @access private
     */
    private function processEmitter(array $blueprint, array $vars = []): void
    {
        // Emmit events
        if (isset($blueprint['emitter']['emit'])) {
            foreach ($blueprint['emitter']['emit'] as $key => $event) {
                emitter()->emit($event['name']);
            }
        }

        // Register listeners 
        if (isset($blueprint['emitter']['addListener'])) {
            foreach ($blueprint['emitter']['addListener'] as $key => $event) {
                emitter()->addListener($event['name'], function() use ($event, $vars) {
                    
                    // Get event vars
                    $eventVars = [];
                    if (isset($event['properties']['vars'])) {
                        foreach ($event['properties']['vars'] as $key => $var) {
                            $varType = isset($var['type']) ? $var['type'] : 'string';
                            switch ($varType) {
                                case 'array':
                                    if (is_iterable($var['value'])) {

                                        array_walk_recursive($var['value'], function(&$value, $key) {
                                            $value = strings(container()->get('twig')->fetchFromString($value, $vars))->trim()->toString();
                                        });

                                        $eventVars[$var['name']] = $var['value'];
                                        
                                    } else {
                                        $value = htmlspecialchars_decode(container()->get('twig')->fetchFromString(trim($var['value']), $vars));
                                        $eventVars[$var['name']] = serializers()->json()->decode($value);
                                    }
                                    break;
                                case 'bool':
                                    $eventVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toBoolean();
                                    break;
                                case 'float':
                                    $eventVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toFloat();
                                    break;    
                                case 'int':
                                    $eventVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toInteger();
                                    break;
                                case 'string':
                                default:
                                    $eventVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toString();
                                    break;
                            }
                        }
                    }
                    if (isset($event['properties']['value'])) {
                        strings(container()->get('twig')->fetchFromString($event['properties']['value'], arrays($eventVars)->merge($vars)->toArray()))->trim()->echo();
                    }
                });
            }
        }
    }

    /**
     * Process actions for blueprint
     *
     * @param array $blueprint Blueprint array.
     * 
     * @return void
     *
     * @access private
     */
    private function processActions(array $blueprint, array $vars = []): void
    {
        if (isset($blueprint['actions'])) {

            emitter()->emit('onBlueprintsBeforeProcessedActions');
            
            foreach($blueprint['actions'] as $action) {
                if (flextype('actions')->has($action['name'])) {
                    if (isset($action['properties']['vars']) && is_array($action['properties']['vars'])) {
                        $properties = array_values($action['properties']['vars']);
                        foreach ($properties as $key => $var) {
                            $type = isset($var['type']) ? $var['type'] : 'string';
                            switch ($type) {
                                case 'array':
                                    if (is_iterable($var['value'])) {
                                    
                                        array_walk_recursive($var['value'], function(&$value, $key) {
                                            $value = strings(container()->get('twig')->fetchFromString($value, $vars))->trim()->toString();
                                        });
    
                                        $properties[$key] = $var['value'];
                                    } else {
                                        $value = htmlspecialchars_decode(container()->get('twig')->fetchFromString(trim($var['value']), $vars));
                                        $properties[$key] = serializers()->json()->decode($value);
                                    }
                                    break;
                                case 'int':
                                    $properties[$key] = strings(container()->get('twig')->fetchFromString(trim($var['value']), $vars))->toInteger();
                                    break;
                                case 'float':
                                    $properties[$key] = strings(container()->get('twig')->fetchFromString(trim($var['value']), $vars))->toFloat();
                                    break;
                                case 'bool':
                                    $properties[$key] = strings(container()->get('twig')->fetchFromString(trim($var['value']), $vars))->toBoolean();
                                    break;
                                default:
                                case 'string':
                                    $properties[$key] = strings(container()->get('twig')->fetchFromString(trim($var['value']), $vars))->toString();
                                    break;
                            }
                        }
                        flextype('actions')->get($action['name'])(...$properties);
                    } else {
                        flextype('actions')->get($action['name'])();
                    }
                }
            }

            emitter()->emit('onBlueprintsAfterProcessedActions');
        }
    }

    /**
     * Process vars for blueprint
     *
     * @param array $blueprint Blueprint array.
     * 
     * @return void
     *
     * @access private
     */
    private function processVars(array $blueprint, array $vars): array
    {
        if (isset($blueprint['vars'])) {
            $processVars = [];
            foreach ($blueprint['vars'] as $key => $var) {
                $varType = isset($var['type']) ? $var['type'] : 'string';
                switch ($varType) {
                    case 'array':
                        if (is_iterable($var['value'])) {

                            array_walk_recursive($var['value'], function(&$value, $key) {
                                $value = strings(container()->get('twig')->fetchFromString($value, $vars))->trim()->toString();
                            });

                            $processVars[$var['name']] = $var['value'];
                            
                        } else {
                            $value = htmlspecialchars_decode(container()->get('twig')->fetchFromString(trim($var['value']), $vars));
                            $processVars[$var['name']] = serializers()->json()->decode($value);
                        }
                        break;
                    case 'bool':
                        $processVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toBoolean();
                        break;
                    case 'float':
                        $processVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toFloat();
                        break;    
                    case 'int':
                        $processVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toInteger();
                        break;
                    case 'string':
                    default:
                        $processVars[$var['name']] = strings(container()->get('twig')->fetchFromString($var['value'], $vars))->trim()->toString();
                        break;
                }
            }
            return arrays($vars)->merge($processVars)->toArray();
        } else {
            return $vars;
        }
    }
}
