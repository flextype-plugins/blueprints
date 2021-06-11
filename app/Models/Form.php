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
     */
    public function __construct(array $data) 
    {
        $this->storage = arrays();

        foreach($data as $processForm => $processStatements) {
            if (strings($processForm)->contains('__form_process')) { 
                $this->storage->set('properties.process', arrays(flextype('serializers')->json()->decode($processStatements))); 
            }

            if (strings($processForm)->contains('__form_vars')) { 
                $this->storage->set('vars', arrays(flextype('serializers')->json()->decode($processStatements))); 
            }
        } 

        $this->storage->set('data', arrays($data)); 
        $this->processActions();
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
     * Get form redirect statament.
     *
     * @param array $values Values to replace for redirect arguments.
     * 
     * @return string Redirect statament.
     *
     * @access public
     */
    public function getProcessRedirect(array $values = []): string
    {
        $redirect = '';

        if ($this->storage['properties']['process']->has('redirect') ) {

            if ($this->storage['properties']['process']->has('redirect.route')) {
                $redirect .= flextype('router')->pathFor(strings(flextype('twig')->fetchFromString(strings($this->storage['properties']['process']->get('redirect.route'))->trim(), $this->storage['properties']['process']->has('redirect.data') ? $this->storage['properties']['process']->get('redirect.data') : $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim());
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }

            if ($this->storage['properties']['process']->has('redirect.url')) {
                $redirect .= strings(flextype('twig')->fetchFromString(strings($this->storage['properties']['process']->get('redirect.url'))->trim(), $this->storage['properties']['process']->has('redirect.data') ? $this->storage['properties']['process']->get('redirect.data') : $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim();
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }

            if (count($args) > 0) {
                foreach($args as $key => $value) {
                    $key === array_key_first($args) and $redirect .= '?';

                    $redirect .=  $key . '=' . strings(flextype('twig')->fetchFromString((empty($values) ? $value : strtr(trim($value), $values)), $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim();

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
     * @access public
     */
    public function getProcessFields(): array 
    {
        $data = [];

        if ($this->storage['properties']['process']->has('fields')) {
            
            flextype('emitter')->emit('onBlueprintsFormBeforeProcessedFields');

            foreach($this->storage['properties']['process']['fields'] as $field) {
                
                // Get field vars
                $fieldVars = [];
                if (isset($field['vars'])) {
                    foreach ($field['vars'] as $key => $var) {
                        $varType = isset($var['type']) ? $var['type'] : 'string';
                        switch ($varType) {
                            case 'array':
                                if (is_iterable($var['value'])) {

                                    array_walk_recursive($var['value'], function(&$value, $key) {
                                        $value = strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toString();
                                    });

                                    $fieldVars[$var['name']] = $var['value'];
                                    
                                } else {
                                    $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($var['value']), $this->storage['vars']->merge(['_form' => $this])->toArray()));
                                    $fieldVars[$var['name']] = flextype('serializers')->json()->decode($value);
                                }
                                break;
                            case 'bool':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toBoolean();
                                break;
                            case 'float':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toFloat();
                                break;    
                            case 'int':
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toInteger();
                                break;
                            case 'string':
                            default:
                                $fieldVars[$var['name']] = strings(flextype('twig')->fetchFromString($var['value'], $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toString();
                                break;
                        }
                    }
                }

                $vars = $this->storage['vars']
                            ->merge(['_form' => $this])
                            ->merge($fieldVars)
                            ->toArray();
            

                // Get field type 
                $type = isset($field['type']) ? $field['type'] : 'string';

                // Get field ignore true/false
                $ignore = isset($field['ignore']) && strings(flextype('twig')->fetchFromString($field['ignore']))->toBoolean() == true ?: false;
                
                switch ($type) {
                    case 'array':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                if (is_iterable($field['value'])) {
                                    
                                    array_walk_recursive($field['value'], function(&$value, $key) {
                                        $value = strings(flextype('twig')->fetchFromString($value, $this->storage['vars']->merge(['_form' => $this])->toArray()))->trim()->toString();
                                    });

                                    $data[$field['name']] = $field['value'];
                                } else {
                                    $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($field['value']), $vars));
                                    $data[$field['name']] = flextype('serializers')->json()->decode($value);
                                }
                            } else {
                                $data[$field['name']] = $this->storage['data']->get($field['name']);
                            }
                        }
                        break;
                    case 'bool':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], $vars))->trim()->toBoolean();
                            } else {
                                $data[$field['name']] = strings($this->storage['data']->get($field['name']))->trim()->toBoolean();
                            }
                        }
                        break;
                    case 'float':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], $vars))->trim()->toFloat();
                            } else {
                                $data[$field['name']] = strings($this->storage['data']->get($field['name']))->trim()->toFloat();
                            }
                        }
                        break;
                    case 'int':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], $vars))->trim()->toInteger();
                            } else {
                                $data[$field['name']] = strings($this->storage['data']->get($field['name']))->trim()->toInteger();
                            }
                        }
                        break;
                    default:
                    case 'string':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], $vars))->trim()->toString();
                            } else {
                                $data[$field['name']] = strings($this->storage['data']->get($field['name']))->trim()->toString();
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
     * @param string $type Message type.
     * @param array  $data Associative array of template variables.
     * 
     * @return string Message statament.
     *
     * @access public
     */
    public function getProcessMessages(string $type, array $data = []): string 
    {
        return $this->storage['properties']['process']->has('messages.' . $type) ? strings(flextype('twig')->fetchFromString($this->storage['properties']['process']->get('messages.' . $type), (count($data) > 0 ? $data : $this->storage['vars']->toArray())))->trim()->toString() : '';
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
                        foreach ($properties as $key => $field) {
                            $type = isset($field['type']) ? $field['type'] : 'string';
                            switch ($type) {
                                case 'array':
                                    if (is_iterable($field['value'])) {
                                        $properties[$key] = $field['value'];
                                    } else {
                                        $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge(['_form' => $this])->toArray()));
                                        $properties[$key] = flextype('serializers')->json()->decode($value);
                                    }
                                    break;
                                case 'int':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge(['_form' => $this])->toArray()))->toInteger();
                                    break;
                                case 'float':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge(['_form' => $this])->toArray()))->toFloat();
                                    break;
                                case 'bool':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge(['_form' => $this])->toArray()))->toBoolean();
                                    break;
                                default:
                                case 'string':
                                    $properties[$key] = $this->getProcessFields();
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