<?php

namespace PhlyBlog\Filter;

use Laminas\Validator\AbstractValidator;

use function is_array;
use function is_string;

class Tags extends AbstractValidator
{
    private const INVALID_TAG  = 'tagInvalid';
    private const INVALID_TAGS = 'tagsInvalid';

    protected $messageTemplates
        = [
            self::INVALID_TAG  => 'Invalid tag provided; expected a string, received "%value%".',
            self::INVALID_TAGS => 'Invalid tags provided; expected an array or string, received "%value%".',
        ];

    public function isValid($value): bool
    {
        $this->setValue($value);
        if (is_array($value)) {
            foreach ($value as $v) {
                if (! is_string($v)) {
                    $this->error(self::INVALID_TAG);
                    return false;
                }
            }
            return true;
        }
        if (is_string($value)) {
            return true;
        }
        $this->error(self::INVALID_TAGS);
        return false;
    }
}
