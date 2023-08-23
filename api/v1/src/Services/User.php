<?php

namespace nvs\api\v1\Services;

use Bitrix\Main\Security\Random;
use CIBlockElement;
use CIBlockPropertyEnum;
use nvs\api\v1\Models as UserModel;

class User
{
    private $iblockId;

    public function __construct(Iblock $iblock)
    {
        $this->iblockId = $iblock->getIblockId();
    }

    public function getActiveUser(array $data): ?UserModel\User
    {
        return $this->getLastActiveUserByEmail($data['email']);
    }

    public function register(array $data): ?UserModel\User
    {
        $fields = $this->getFields($data);

        if ($userId = $this->createUser($fields)) {
            return $this->getById($userId);
        }

        return null;
    }

    public function update(array $data): ?UserModel\User
    {
        $fields = $this->getFields($data, true);

        if ($userId = $this->createUser($fields)) {
            $this->deactivateOtherUsers($userId, $data['email']);
            return $this->getById($userId);
        }

        return null;
    }

    public function getUserAllFields(array $data): ?UserModel\User
    {
        $filter = [
            'PROPERTY_api_fname' => trim($data['firstName']),
            'PROPERTY_api_lname' => trim($data['lastName']),
            'PROPERTY_api_mname' => trim($data['secondName']),
            'PROPERTY_api_city' => trim($data['city']),
            'PROPERTY_api_region' => $this->getTherapeuticAreaId(trim($data['therapeuticArea'])),
            'PROPERTY_api_email' => trim($data['email']),
            'PROPERTY_api_phone' => trim($data['phone']),
            'PROPERTY_api_lpu' => trim($data['medicalInstitution']),
            'PROPERTY_api_agree_data' => $data['agreement'] ? $this->getAgreementId() : false,
        ];

        return $this->getUser($filter);
    }

    public function confirm(UserModel\User $user): ?UserModel\User
    {
        if ($activeUser = $this->activate($user)) {
            $this->deactivateOtherUsers($activeUser->id, $user->email);
            return $activeUser;
        }

        return null;
    }

    public function get(array $data): ?UserModel\User
    {
        return $this->getLastActiveUserByEmail($data['email']);
    }

    private function createUser(array $fields)
    {
        $element = new CIBlockElement();
        $elementId = $element->Add($fields);

        return ($elementId > 0) ? $elementId : null;
    }

    private function getFields(array $data, bool $isActive = false): array
    {
        $fields = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $data['email'] . $data['therapeuticArea'] . Random::getString(5),
            'ACTIVE' => $isActive ? 'Y' : 'N',
        ];

        $fields['PROPERTY_VALUES'] = [
            'api_fname' => $data['firstName'],
            'api_lname' => $data['lastName'],
            'api_mname' => $data['secondName'],
            'api_city' => $data['city'],
            'api_region' => $this->getTherapeuticAreaId($data['therapeuticArea']),
            'api_email' => $data['email'],
            'api_phone' => $data['phone'],
            'api_lpu' => $data['medicalInstitution'],
            'api_agree_data' => $data['agreement'] ? $this->getAgreementId() : false,
        ];

        return $fields;
    }

    private function getTherapeuticAreaId($therapeuticArea)
    {
        $hlTherapeuticArea = get_hightload_row('hg_regions', $therapeuticArea);

        return $hlTherapeuticArea ? $hlTherapeuticArea['UF_XML_ID'] : null;
    }

    private function getAgreementId()
    {
        $propertyEnum = CIBlockPropertyEnum::GetList(
            ['SORT' => 'ASC'],
            ['CODE' => 'api_agree_data', 'IBLOCK_ID' => $this->iblockId, 'XML_ID' => 'Y']
        );

        if ($enumFields = $propertyEnum->GetNext()) {
            return $enumFields['ID'];
        }

        return null;
    }

    private function getById(int $userId): ?UserModel\User
    {
        $filter = ['ID' => $userId];
        return $this->getUser($filter);
    }

    private function getByEmail(string $email): ?UserModel\User
    {
        $filter = ['PROPERTY_api_email' => $email];
        return $this->getUser($filter);
    }

    private function getLastActiveUserByEmail(string $email): ?UserModel\User
    {
        $filter = [
            'ACTIVE' => 'Y',
            'PROPERTY_api_email' => $email
        ];

        return $this->getUser($filter);
    }

    private function getUser(array $filter): ?UserModel\User
    {
        $user = null;

        $rsData = CIBlockElement::GetList(
            ['created' => 'desc'],
            array_merge($filter, ['IBLOCK_ID' => $this->iblockId]),
            false, false,
            ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE']
        );

        if ($arRes = $rsData->GetNextElement()) {
            $fields = $arRes->GetFields();
            $properties = $arRes->GetProperties();

            $user = new UserModel\User();
            $user->id = $fields['ID'];
            $user->s_status = ($fields['ACTIVE'] == 'Y') ? 'Active' : 'Inactive';

            foreach ($properties as $property) {
                if ($property['CODE'] == 'api_fname') {
                    $user->firstName = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_lname') {
                    $user->lastName = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_mname') {
                    $user->secondName = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_city') {
                    $user->city = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_region') {
                    $user->therapeuticArea = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_email') {
                    $user->email = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_phone') {
                    $user->phone = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_lpu') {
                    $user->medicalInstitution = $property['VALUE'];
                }
                if ($property['CODE'] == 'api_agree_data') {
                    $user->agreement = (bool)$property['VALUE'];
                }
            }
        }

        return $user;
    }

    private function activate(UserModel\User $user): ?UserModel\User
    {
        $el = new CIBlockElement;
        $res = $el->Update($user->id, ['ACTIVE' => 'Y']);

        if ($res) {
            return $this->getById($user->id);
        }

        return null;
    }

    private function deactivateOtherUsers(int $userId, string $email): void
    {
        $filter = [
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
            'PROPERTY_api_email' => $email,
            '!ID' => $userId
        ];

        $rsData = CIBlockElement::GetList(
            ['created' => 'desc'],
            $filter,
            false, false,
            ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE']
        );

        while ($arRes = $rsData->Fetch()) {
            $el = new CIBlockElement;
            $el->Update($arRes['ID'], ['ACTIVE' => 'N']);
        }
    }
}
