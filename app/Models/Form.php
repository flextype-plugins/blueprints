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
     * Form process statements.
     *
     * @var array
     * @access private
     */
    private $process = [];

    /**
     * Form raw data.
     *
     * @var array
     * @access private
     */
    private $data = [];

    /**
     * Create a new Form object.
     *
     * Initializes a Form object and assigns $data the supplied values.
     *
     * @param array $data Form data.
     */
    public function __construct(array $data) 
    {
        foreach($data as $processForm => $processStatements) {
            if (strings($processForm)->contains('__process_form')) { 
                $this->process = json_decode($processStatements, true);    
                $this->data    = $data; 
            }
        } 
    }

    /**
     * Get form redirect statament.
     *
     * @param array $data Form data.
     * 
     * @return string Redirect statament.
     *
     * @access public
     */
    public function getRedirect(array $values = []): string
    {
        $redirect = '';

        if (isset($this->process['redirect'])) {
            if (isset($this->process['redirect']['route'])) {
                $redirect .= flextype('router')->pathFor($this->getRedirectArgs($this->process['redirect']['route'])['redirect']);
                $args = $this->getRedirectArgs($this->process['redirect']['route'])['args'];
            }

            if (isset($this->process['redirect']['url'])) {
                $redirect .= $this->getRedirectArgs($this->process['redirect']['url'])['redirect'];
                $args = $this->getRedirectArgs($this->process['redirect']['url'])['args'];
            }
            
            if (count($args) > 0) {
                foreach($args as $key => $value) {
                    $key === array_key_first($args) and $redirect .= '?';

                    $redirect .=  $value . '=' . (empty($values) ? $value : strtr($value, $values));

                    $key != array_key_last($args) and $redirect .= '&'; 
                }
            }
            
        }

        return $redirect;
    }

    /**
     * Get form fields statament.
     * 
     * @return string Fields statament.
     *
     * @access public
     */
    public function getFields(): array 
    {
        $data = [];

        if (isset($this->process['fields'])) {
            foreach($this->process['fields'] as $field) {
                if (is_array($field)) {
                    foreach($field as $name => $property) {
                        if (isset($property['type'])) {
                            switch ($property['type']) {
                                case 'bool':
                                    if (isset($property['value'])) {
                                        $data[$name] = strings(flextype('twig')->fetchFromString(strings($property['value'])->replace('_value', "'". arrays($this->data)->get($name) . "'"), isset($property['data']) ? $property['data'] : []))->toBoolean();
                                    } else {
                                        $data[$name] = strings(arrays($this->data)->get($name))->toBoolean();
                                    }
                                    break;
                                case 'float':
                                    if (isset($property['value'])) {
                                        $data[$name] = strings(flextype('twig')->fetchFromString(strings($property['value'])->replace('_value', "'". arrays($this->data)->get($name) . "'"), isset($property['data']) ? $property['data'] : []))->toFloat();
                                    } else {
                                        $data[$name] = strings(arrays($this->data)->get($name))->toFloat();
                                    }
                                    break;
                                case 'int':
                                    if (isset($property['value'])) {
                                        $data[$name] = strings(flextype('twig')->fetchFromString(strings($property['value'])->replace('_value', "'". arrays($this->data)->get($name) . "'"), isset($property['data']) ? $property['data'] : []))->toInteger();
                                    } else {
                                        $data[$name] = strings(arrays($this->data)->get($name))->toInteger();
                                    }
                                    break;
                                default:
                                case 'string':
                                    if (isset($property['value'])) {
                                        $data[$name] = strings(flextype('twig')->fetchFromString(strings($property['value'])->replace('_value', "'". arrays($this->data)->get($name) . "'"), isset($property['data']) ? $property['data'] : []))->toString();
                                    } else {
                                        $data[$name] = strings(arrays($this->data)->get($name))->toString();
                                    }
                                    break;
                            }
                        } else {
                            if (isset($property['value'])) {
                                $data[$name] = flextype('twig')->fetchFromString(strings($property['value'])->replace('_value', "'". arrays($this->data)->get($name) . "'"), isset($property['data']) ? $property['data'] : []);
                            } else {
                                $data[$name] = arrays($this->data)->get($name);
                            }
                        }
                    }
                } else {
                    $data[$field] = arrays($this->data)->get($field);
                }
            }
        }

        return $data;
    }

    /**
     * Get form messages statament.
     * 
     * @param string $type Message type.
     * @param array  $data Associative array of template variables.
     * 
     * @return string Message statament.
     *
     * @access public
     */
    public function getMessages(string $type, array $data = []): string 
    {
        return isset($this->process['messages'][$type]) ? flextype('twig')->fetchFromString($this->process['messages'][$type], $data) : '';
    }

    /**
     * Get form redirect args.
     * 
     * @param string $type   Message type.
     * @param array  $values Values to replace in the translated text.
     * 
     * @return string Message statament.
     *
     * @access public
     */
    private function getRedirectArgs(string $string): array
    {
        $result = [];

        if (strings($string)->contains('args:')) {
            $result['redirect'] = strings($string)->before('args:')->trim()->toString();
            $result['args']     = strings($string)->after('args:')->trim()->toArray(',');
        }
        
        return $result;
    }
}