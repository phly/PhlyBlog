<?php
namespace PhlyBlog\Filter;

use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Laminas\Validator\AbstractValidator;

class Url extends AbstractValidator
{
    const INVALID_URL  = 'urlInvalid';

    protected $messageTemplates = array(
        self::INVALID_URL  => 'Invalid url provided; received "%value%".',
    );

    public function isValid($value)
    {
        $this->setValue($value);

        if (!$value instanceof Uri) {
            $value = UriFactory::factory($value);
        }

        if (!$value->isValid()) {
            $this->error(self::INVALID_URL);
            return false;
        }

        return true;
    }
}
