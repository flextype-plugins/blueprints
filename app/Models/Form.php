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
                $redirect .= flextype('router')->pathFor($this->storage['properties']['process']->get('redirect.route'));
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }

            if ($this->storage['properties']['process']->has('redirect.url')) {
                $redirect .= $this->storage['properties']['process']->get('redirect.url');
                $args = $this->storage['properties']['process']->has('redirect.args') ? $this->storage['properties']['process']->get('redirect.args') : [];
            }
            
            if (count($args) > 0) {
                foreach($args as $key => $value) {
                    $key === array_key_first($args) and $redirect .= '?';

                    $redirect .=  $key . '=' . flextype('twig')->fetchFromString((empty($values) ? $value : strtr($value, $values)), $this->storage['vars']->toArray());

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

            $_form = ['_form' => $this];

            foreach($this->storage['properties']['process']['fields'] as $field) {
                $type = isset($field['type']) ? $field['type'] : 'string';
                $ignore = isset($field['ignore']) && strings(flextype('twig')->fetchFromString($field['ignore']))->toBoolean() == true ?: false;
                switch ($type) {
                    case 'bool':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], isset($field['data']) ? $field['data'] : $this->storage['vars']->merge($_form)->toArray()))->trim()->toBoolean();
                            } else {
                                $data[$field['name']] = strings(strings($this->storage['data']->get($field['name']))->trim())->toBoolean();
                            }
                        }
                        break;
                    case 'float':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], isset($field['data']) ? $field['data'] : $this->storage['vars']->merge($_form)->toArray()))->trim()->toFloat();
                            } else {
                                $data[$field['name']] = strings(strings($this->storage['data']->get($field['name']))->trim())->toFloat();
                            }
                        }
                        break;
                    case 'int':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], isset($field['data']) ? $field['data'] : $this->storage['vars']->merge($_form)->toArray()))->trim()->toInteger();
                            } else {
                                $data[$field['name']] = strings(strings($this->storage['data']->get($field['name']))->trim())->toInteger();
                            }
                        }
                        break;
                    default:
                    case 'string':
                        if (!$ignore) {
                            if (isset($field['value'])) {
                                $data[$field['name']] = strings(flextype('twig')->fetchFromString($field['value'], isset($field['data']) ? $field['data'] : $this->storage['vars']->merge($_form)->toArray()))->trim()->toString();
                            } else {
                                $data[$field['name']] = strings(strings($this->storage['data']->get($field['name']))->trim())->toString();
                            }
                        }
                        break;
                }
            }
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
        return $this->storage['properties']['process']->has('messages' . $type) ? flextype('twig')->fetchFromString($this->storage['properties']['process']->get('messages' . $type), (count($data) > 0 ? $data : $this->storage['vars']->toArray())) : '';
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
        $_form = ['_form' => $this];

        if ($this->storage['properties']['process']->has('actions')) {

            flextype('emitter')->emit('onBlueprintsFormBeforeProcessedActions');

            foreach($this->storage['properties']['process']->get('actions') as $action) {
                if (flextype('actions')->has($action['name'])) {
                    if (isset($action['properties']) && is_array($action['properties'])) {
                        $properties = array_values($action['properties']);
                        foreach ($properties as $key => $field) {
                            $type = isset($field['type']) ? $field['type'] : 'string';
                            switch ($type) {
                                case 'array':
                                    if (is_iterable($field['value'])) {
                                        $properties[$key] = $field['value'];
                                    } else {
                                        $value = htmlspecialchars_decode(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge($_form)->toArray()));
                                        $properties[$key] = flextype('serializers')->json()->decode($value);
                                    }
                                    break;
                                case 'int':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge($_form)->toArray()))->toInteger();
                                    break;
                                case 'float':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge($_form)->toArray()))->toFloat();
                                    break;
                                case 'bool':
                                    $properties[$key] = strings(flextype('twig')->fetchFromString(trim($field['value']), $this->storage['vars']->merge($_form)->toArray()))->toBoolean();
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