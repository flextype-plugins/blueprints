<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype\Plugin\Blueprints\Models;

use Atomastic\Arrays\Arrays;
use Atomastic\Macroable\Macroable;
use function Flextype\Component\I18n\__;

class Form
{
    use Macroable;

    /**
     * Form storage.
     *
     * @var array
     * @access private
     */
    private ?Arrays $storage = null;

    /**
     * Create a new Form object.
     *
     * Initializes a Form object and assigns $data the supplied values.
     *
     * @param array $data Form data.
     * @param array $vars Form vars.
     */
    public function __construct(array $data, array $vars = []) 
    {
        $this->storage = arrays();

        foreach($data as $processForm => $processStatements) {
            if (strings($processForm)->contains('__form_process')) { 
                $this->storage->set('properties.process', arrays(flextype('serializers')->json()->decode($processStatements))); 
            }

            if (strings($processForm)->contains('__form_vars')) { 
                $this->storage->set('vars', arrays(flextype('serializers')->json()->decode($processStatements))->merge($vars)->merge(['_form' => $this])); 
            }
        } 

        $this->storage->set('data', arrays($data)); 
    }

    /**
     * Get form storage.
     *
     * @return Arrays Form storage.
     *
     * @access public
     */
    public function storage(): Arrays
    {
        return $this->storage;
    }

    /**
     * Get form process.
     *
     * @return Arrays Form process.
     *
     * @access public
     */
    public function process(): Arrays
    {
        // Process form actions.
        $this->processActions();

        // Get form process fields, messages and redirect.
        return arrays(['fields' => $this->processFields(),
                       'messages' => $this->processMessages(),
                       'redirect' => $this->processRedirect()]);
    }

    /**
     * Get form redirect statament.
     * 
     * @return string Redirect statament.
     *
     * @access private
     */
    private function processRedirect(): string
    {
        $redirect = '';

        if ($this->storage['properties']['process']->has('redirect') ) {

            if ($this->storage['properties']['process']->has('redirect.route')) {
                $redirect .= flextype('router')->pathFor(strings(flextype('twig')->fetchFromString(strings($this->storage['properties']['process']->get('redirect.route'))->trim(), $this->storage['properties']['process']->has('redirect.data') ? $this->storage['properties']['process']->get('redirect.data') : $this->storage['vars']->toArray()))->trim());
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }

            if ($this->storage['properties']['process']->has('redirect.url')) {
                $redirect .= strings(flextype('twig')->fetchFromString(strings($this->storage['properties']['process']->get('redirect.url'))->trim(), $this->storage['properties']['process']->has('redirect.data') ? $this->storage['properties']['process']->get('redirect.data') : $this->storage['vars']->toArray()))->trim();
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }

            if (count($args) > 0) {
                foreach($args as $key => $value) {
                    $key === array_key_first($args) and $redirect .= '?';

                    $redirect .=  $key . '=' . strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->toArray()))->trim();

                    $key != array_key_last($args) and $redirect .= '&'; 
                }
            }
            
        }

        return $redirect;
    }

    /**
     * Get form fields statement.
     * 
     * @return array Fields statement.
     *
     * @access private
     */
    private function processFields(): array 
    {
        $data = [];

        if ($this->storage['properties']['process']->has('fields')) {
            
            flextype('emitter')->emit('onBlueprintsFormBeforeProcessedFields');

            foreach($this->storage['properties']['process']['fields'] as $field) {
                
                // Get field vars
                $fieldVars = [];
                if (isset($field['properties']['vars'])) {
                    foreach ($field['properties']['vars'] as $key => $var) {
                        $varType = isset($var['type']) ? $var['type'] : 'string';
                        switch ($varType) {
                            case 'array':
                                if (is_iterable($var['value'])) {

                                    array_walk_recursive($var['value'], function(&$value, $key) {
                                        $value = strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->toArray()))->trim()->toString();
                                    });

                                    $fieldVars[$var['name']] = $var['value'];
                                    
                                } else {
                                    $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($var['value']), $this->storage['vars']->toArray()));
                                    $fieldVars[$var['name']] = flextype('serializers')->json()->decode($value);
                                }
                                break;
                            case 'bool':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->toArray()))->trim()->toBoolean();
                                break;
                            case 'float':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->toArray()))->trim()->toFloat();
                                break;    
                            case 'int':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->toArray()))->trim()->toInteger();
                                break;
                            case 'string':
                            default:
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->toArray()))->trim()->toString();
                                break;
                        }
                    }
                }

                $vars = $this->storage['vars']->merge($fieldVars)->toArray();

                // Get field type 
                $type = isset($field['properties']['type']) ? $field['properties']['type'] : 'string';

                // Get field ignore true/false
                $ignore = isset($field['properties']['ignore']) && strings(flextype('twig')->fetchFromString($field['properties']['ignore']))->toBoolean() == true ?: false;
                
                switch ($type) {
                    case 'array':
                        if (!$ignore) {
                            if (isset($field['properties']['value'])) {
                                if (is_iterable($field['properties']['value'])) {
                                    
                                    array_walk_recursive($field['properties']['value'], function(&$value, $key) {
                                        $value = strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->toArray()))->trim()->toString();
                                    });

                                    $data = arrays($data)->set($field['name'], $field['properties']['value'])->toArray();
                                } else {
                                    $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($field['properties']['value']), $vars));
                                    $data = arrays($data)->set($field['name'], flextype('serializers')->json()->decode($value))->toArray();
                                }
                            } else {
                                $data = arrays($data)->set($field['name'], $this->storage['data']->get($field['name']))->toArray();
                            }
                        }
                        break;
                    case 'bool':
                        if (!$ignore) {
                            if (isset($field['properties']['value'])) {
                                $data = arrays($data)->set($field['name'], strings(flextype('twig')->fetchFromString($field['properties']['value'], $vars))->trim()->toBoolean())->toArray();
                            } else {
                                $data = arrays($data)->set($field['name'], strings($this->storage['data']->get($field['name']))->trim()->toBoolean())->toArray();
                            }
                        }
                        break;
                    case 'float':
                        if (!$ignore) {
                            if (isset($field['properties']['value'])) {
                                $data = arrays($data)->set($field['name'], strings(flextype('twig')->fetchFromString($field['properties']['value'], $vars))->trim()->toFloat())->toArray();
                            } else {
                                $data = arrays($data)->set($field['name'], strings($this->storage['data']->get($field['name']))->trim()->toFloat())->toArray();
                            }
                        }
                        break;
                    case 'int':
                        if (!$ignore) {
                            if (isset($field['properties']['value'])) {
                                $data = arrays($data)->set($field['name'], strings(flextype('twig')->fetchFromString($field['properties']['value'], $vars))->trim()->toInteger())->toArray();
                            } else {
                                $data = arrays($data)->set($field['name'], strings($this->storage['data']->get($field['name']))->trim()->toInteger())->toArray();
                            }
                        }
                        break;
                    default:
                    case 'string':
                        if (!$ignore) {
                            if (isset($field['properties']['value'])) {
                                $data = arrays($data)->set($field['name'], strings(flextype('twig')->fetchFromString($field['properties']['value'], $vars))->trim()->toString())->toArray();
                            } else {
                                $data = arrays($data)->set($field['name'], strings($this->storage['data']->get($field['name']))->trim()->toString())->toArray();
                            }
                        }
                        break;
                }
            }

            flextype('emitter')->emit('onBlueprintsFormAfterProcessedFields');
        }

        return $data;
    }

    /**
     * Get form process messages statament.
     *
     * @return string Message statament.
     *
     * @access private
     */
    private function processMessages(): array 
    {
        $data = [];

        if ($this->storage['properties']['process']->has('messages')) {
            foreach($this->storage['properties']['process']['messages'] as $key => $value) {
                $data[$key] = strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->toArray()))->trim()->toString();;
            }
        }

        return $data;
    }

    /**
     * Process form actions statement.
     * 
     * @return void
     *
     * @access private
     */
    private function processActions(): void
    {
        if ($this->storage['properties']['process']->has('actions')) {

            flextype('emitter')->emit('onBlueprintsFormBeforeProcessedActions');
            
            foreach($this->storage['properties']['process']->get('actions') as $action) {
                if (flextype('actions')->has($action['name'])) {
                    if (isset($action['properties']['vars']) && is_array($action['properties']['vars'])) {
                        $properties = array_values($action['properties']['vars']);
                        foreach ($properties as $key => $var) {
                            $type = isset($var['type']) ? $var['type'] : 'string';
                            $vars = $this->storage['vars']->toArray();
                            switch ($type) {
                                case 'array':
                                    if (is_iterable($var['value'])) {
                                    
                                        array_walk_recursive($var['value'], function(&$value, $key) {
                                            $value = strings(flextype('twig')->fetchFromString($value, $vars))->trim()->toString();
                                        });
    
                                        $properties[$key] = $var['value'];
                                    } else {
                                        $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($var['value']), $vars));
                                        $properties[$key] = flextype('serializers')->json()->decode($value);
                                    }
                                    break;
                                case 'int':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($var['value']), $vars))->toInteger();
                                    break;
                                case 'float':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($var['value']), $vars))->toFloat();
                                    break;
                                case 'bool':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($var['value']), $vars))->toBoolean();
                                    break;
                                default:
                                case 'string':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($var['value']), $vars))->toString();
                                    break;
                            }
                        }
                        flextype('actions')->get($action['name'])(...$properties);
                    } else {
                        flextype('actions')->get($action['name'])();
                    }
                }
            }

            flextype('emitter')->emit('onBlueprintsFormAfterProcessedActions');
        }
    }
}