<?php

namespace nvs\api\v1\Services;

use CIBlock;

class Iblock
{
    public function getIblockId(): ?int
    {
        $res = CIBlock::GetList(
            [],
            [
                'TYPE' => 'forms',
                "=CODE" => 'apiIblock'
            ]
        );

        if ($arRes = $res->Fetch()) {
            return $arRes['ID'];
        }

        return null;
    }
}
