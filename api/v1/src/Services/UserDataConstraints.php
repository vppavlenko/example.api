<?php

namespace nvs\api\v1\Services;

use Symfony\Component\Validator\Constraints as Assert;

class UserDataConstraints
{
    public function getConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'firstName' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'secondName' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'lastName' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email()
            ],
            'phone' => [
                new Assert\NotBlank(),
                new Assert\Regex([
                    "pattern" => '/^\d{11}$/',
                    'message' => "Phone number does not match the format"
                ]),
            ],
            'city' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'medicalInstitution' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'therapeuticArea' => [
                new Assert\NotBlank(),
                new Assert\Type("string")
            ],
            'agreement' => [
                new Assert\NotNull(),
                new Assert\Type("bool")
            ],
        ]);
    }
}
