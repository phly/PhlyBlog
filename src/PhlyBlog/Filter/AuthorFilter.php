<?php
namespace PhlyBlog\Filter;

use Zend\Filter\InputFilter;

class AuthorFilter extends InputFilter
{
    public function __construct()
    {
        $filterRules = array(
            'id'    => 'string_trim',
            'name'  => array('string_trim', 'strip_tags'),
            'email' => 'string_trim',
            'url'   => 'string_trim',
        );

        $validatorRules = array(
            'id'        => array(new AuthorIsValid(), 'message' => 'Missing identifier (short name).'),
            'name'      => array(array('string_length', 1), 'message' => 'Name must be at least 1 characters in length, and non-empty.', 'required' => true),
            'email'     => array('emailaddress', 'message' => 'Invalid email address provided', 'allowEmpty' => true),
            'url'       => array(new Url(), 'message' => 'Invalid url provided', 'allowEmpty' => true),
        );

        $options = array(
            'escapeFilter' => 'string_trim',
        );

        parent::__construct($filterRules, $validatorRules, null, $options);
    }
}
