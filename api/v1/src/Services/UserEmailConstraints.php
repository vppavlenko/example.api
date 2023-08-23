<?php

namespace nvs\api\v1\Services;

use Symfony\Component\Validator\Constraints as Assert;

class UserEmailConstraints
{
    public function getConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email()
            ]
        ]);
    }
}
