<?php

namespace App\Core;

use App\Core\Interfaces\DatabaseInterface;
use App\Core\Interfaces\LoggerInterface;

/**
 * Abstract base class for all models, providing common functionalities.
 */
abstract class Model
{
    /**
     * Model attributes corresponding to table columns.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Stores validation errors.
     *
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Sets model attributes from an array.
     *
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Magic getter for model attributes.
     *
     * @param string $name Attribute name.
     * @return mixed|null Attribute value or null if not set.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic setter for model attributes.
     *
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Checks if an attribute is set.
     *
     * @param string $name Attribute name.
     * @return bool True if set, false otherwise.
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Validates model attributes.
     *
     * Should be overridden in child classes to implement specific validations.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        return [];
    }

    /**
     * Returns the model attributes.
     *
     * @return array Model attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
