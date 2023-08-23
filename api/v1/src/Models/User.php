<?php

namespace nvs\api\v1\Models;

class User
{
    public $id;

    public $firstName;

    public $secondName;

    public $lastName;

    public $email;

    public $phone;

    public $city;

    public $medicalInstitution;

    public $therapeuticArea;

    public $agreement;

    public $s_status;

    public function getValueMap(): array
    {
        $vars = get_object_vars($this);
        unset($vars['id']);

        return $vars;
    }
}
