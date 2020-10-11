<?php

namespace PhlyBlog\Filter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;

class AuthorFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(
            [
                'name'       => 'id',
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    new AuthorIsValid(),
                ],
                'required'   => true,
            ]
        );

        $this->add(
            [
                'name'       => 'name',
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 1,
                        ],
                    ],
                ],
                'required'   => true,
            ]
        );

        $this->add(
            [
                'name'        => 'email',
                'filters'     => [
                    ['name' => StringTrim::class],
                ],
                'validators'  => [
                    ['name' => EmailAddress::class],
                ],
                'allow_empty' => true,
            ]
        );

        $this->add(
            [
                'name'        => 'url',
                'filters'     => [
                    ['name' => StringTrim::class],
                ],
                'validators'  => [
                    new Url(),
                ],
                'allow_empty' => true,
            ]
        );
    }
}
