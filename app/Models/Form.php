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
     * Processed form data.
     *
     * @var array
     * @access private
     */
    private $form = [];

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
                $this->form = json_decode($processStatements, true);    
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

        if (isset($this->form['redirect'])) {
            if (isset($this->form['redirect']['route'])) {
                $redirect .= flextype('router')->pathFor($this->getRedirectArgs($this->form['redirect']['route'])['redirect']);
                $args = $this->getRedirectArgs($this->form['redirect']['route'])['args'];
            }

            if (isset($this->form['redirect']['url'])) {
                $redirect .= $this->getRedirectArgs($this->form['redirect']['url'])['redirect'];
                $args = $this->getRedirectArgs($this->form['redirect']['url'])['args'];
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

        if (isset($this->form['fields'])) {
            foreach($this->form['fields'] as $key => $value) {
                $data[$value] = arrays($data)->get($value);
            }
        }

        return $data;
    }

    /**
     * Get form message statament.
     * 
     * @param string $type   Message type.
     * @param array  $values Values to replace in the translated text.
     * 
     * @return string Message statament.
     *
     * @access public
     */
    public function getMessage(string $type, array $values = []): string 
    {
        return isset($this->form['messages'][$type]) ? __($this->form['messages'][$type], $values) : '';
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