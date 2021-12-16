<?php

namespace PhlyBlog\Filter;

use Laminas\Filter\Boolean;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use PhlyCommon\Filter\Timezone as TimezoneValidator;

use function is_array;
use function is_object;
use function trim;

class EntryFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(
            [
                'name'     => 'id',
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
                'required' => true,
            ]
        );

        $this->add(
            [
                'name'       => 'title',
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 3,
                        ],
                    ],
                ],
                'required'   => true,
            ]
        );

        $this->add(
            [
                'name'        => 'body',
                'filters'     => [
                    ['name' => StringTrim::class],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'extended',
                'filters'     => [
                    ['name' => StringTrim::class],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'author',
                'filters'     => [
                    static function ($value) {
                        if (is_array($value) || is_object($value)) {
                            return $value;
                        }
                        return trim($value);
                    },
                ],
                'validators'  => [
                    new AuthorIsValid(),
                ],
                'required'    => true,
                'allow_empty' => false,
            ]
        );

        $this->add(
            [
                'name'        => 'created',
                'validators'  => [
                    ['name' => IsInt::class],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'updated',
                'validators'  => [
                    ['name' => IsInt::class],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'is_draft',
                'filters'     => [
                    ['name' => Boolean::class],
                ],
                'validators'  => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => [true, false],
                            'strict'   => true,
                        ],
                    ],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'is_public',
                'filters'     => [
                    ['name' => Boolean::class],
                ],
                'validators'  => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => [true, false],
                            'strict'   => true,
                        ],
                    ],
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'       => 'timezone',
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    new TimezoneValidator(),
                ],
                'required'   => true,
            ]
        );

        $this->add(
            [
                'name'        => 'tags',
                'validators'  => [
                    new Tags(),
                ],
                'required'    => false,
                'allow_empty' => true,
            ]
        );
    }
}
