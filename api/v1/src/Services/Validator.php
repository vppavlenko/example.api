<?php

namespace nvs\api\v1\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private $isValid = true;

    private $errors = [];

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($constraints, $data): Validator
    {
        $violations = $this->validator->validate(
            $data,
            $constraints
        );

        if (count($violations) > 0) {

            $this->isValid = false;

            foreach ($violations as $violation) {
                $this->errors[] = $violation->getPropertyPath() . ' : ' . $violation->getMessage();
            }

        }

        return $this;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
