<?php

namespace Parsidev\MaxSms\Models;

/**
 * Snake to camel
 * @param string $txt text to convert
 * @return string
 */
function snakeToCamel($txt)
{
    return lcfirst(str_replace('_', '', ucwords($txt, '_')));
}

/**
 * Snake to camel array keys
 * @param array $arr array to convert
 * @return array
 */
function snakeToCamelArray($arr)
{
    $transformed = array();

    foreach ($arr as $key => $value) {
        $transformed[snakeToCamel($key)] = $value;
    }

    return $transformed;
}

abstract class Base
{
    /**
     * Load class properties from json object
     * @param array $data associated list of properties
     */
    public function fromJSON($data)
    {
        foreach ($data as $key => $value) {
            $camelCased = snakeToCamel($key);

            if (property_exists($this, $camelCased)) {
                $this->$camelCased = $value;
            }
        }

        return $this;
    }
}