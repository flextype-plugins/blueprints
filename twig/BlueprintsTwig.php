<?php

declare(strict_types=1);

/**
 * Flextype (https://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype\Plugin\Blueprints\Twig;

use Atomastic\Macroable\Macroable;
use Atomastic\Arrays\Arrays;

class BlueprintsTwig
{
    use Macroable;

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
        return flextype('blueprints')->fetch($id, $options);
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
        return flextype('blueprints')->has($id);
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
        flextype('blueprints')->render($id, $value, $vars);
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
        flextype('blueprints')->renderFromArray($blueprint, $values, $vars);
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
        return flextype('blueprints')->getBlockName($name);
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
        return flextype('blueprints')->getBlockID($id);
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
        return flextype('blueprints')->form($data);
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
        return flextype('blueprints')->getFileLocation($id);
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
        return flextype('blueprints')->getDirectoryLocation($id);
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
        return flextype('blueprints')->getCacheID($id);
    }
}
