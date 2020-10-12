<?php

namespace PhlyBlog\Filter;

use Laminas\Filter\FilterInterface;

class Permalink implements FilterInterface
{
    public function filter($value)
    {
        $str = strtolower(trim($value));

        // replace all non valid characters and spaces with a dash
        $str = preg_replace('/[^a-z0-9_]/', '-', $str);
        $str = preg_replace('/-{2,}/', "-", $str);
        $str = trim($str, '-');
        $str = trim($str, '_');
        return $str;
    }
}
