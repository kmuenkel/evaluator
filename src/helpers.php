<?php

if (!function_exists('array_get_recursive')) {
    /**
     * @param array|object $items
     * @param string|int $key
     * @param mixed|null $default
     * @return mixed
     */
    function array_get_recursive($items, $key, $default = null)
    {
        return array_reduce(explode('.', $key), function ($items, $key) use ($default) {
            if (is_array($items)) {
                return array_key_exists($key, $items) ? $items[$key] : $default;
            } elseif (is_object($items)) {
                return property_exists($items, $key) ? $items->$key : $default;
            }

            return $default;
        }, $items);
    }
}

if (!function_exists('array_dot')) {
    /**
     * @param array $items
     * @return array
     */
    function array_dot(array $items)
    {
        $newItems = ($self = function (array $items, $prepend = '') use (&$self) {
            $newItems = [];

            foreach ($items as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $newItems += $self($value, "$prepend$key.");

                    continue;
                }

                $newItems[$prepend.$key] = $value;
                $prepend = '';
            }

            return $newItems;
        })($items);

        return $newItems;
    }
}

if (!function_exists('array_match_recursive')) {
    /**
     * @param array $items
     * @param string $search
     * @return array
     */
    function array_match_recursive(array $items, $search)
    {
        $items = array_depth($items) ? array_dot($items) : $items;
        $search .= '*';
        $results = [];

        foreach ($items as $key => $value) {
            $key = str_replace("\x00", '', $key);
            if (fnmatch($search, $key)) {
                $results[] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('array_depth')) {
    /**
     * @param array $array
     * @return int
     * @see https://stackoverflow.com/a/40725952/2137316
     */
    function array_depth(array $array)
    {
        $depth = 0;
        $iteIte = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        foreach ($iteIte as $ite) {
            $d = $iteIte->getDepth();
            $depth = $d > $depth ? $d : $depth;
        }

        return $depth;
    }
}
