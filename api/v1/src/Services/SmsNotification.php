<?php

namespace nvs\api\v1\Services;

use CIBlockElement;
use nvs\api\v1\Models as UserModel;
use SmscMed;
use Throwable;

class SmsNotification
{
    private $iblockId;
    public function __construct(Iblock $iblock)
    {
        $this->iblockId = $iblock->getIblockId();
    }
    public function userNotify(UserModel\User $user): void
    {
        $sms = new SmscMed();

        if (empty($user->phone)) {
            return;
        }

        $message = $this->getSmsText();

        if (!$message) {
            return;
        }

        try {
            $result = $sms->sendSms([$user->phone], $message);
            if (!empty($result)) {
                CIBlockElement::SetPropertyValuesEx(
                    $user->id,
                    $this->iblockId,
                    ['form_great_id_sms', $result['id']]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $user->id,
                    $this->iblockId,
                    ['form_great_id_sms_status', $result['status']]
                );
            }
        } catch (Throwable $exception) {
            AddMessage2Log(sprintf('Не удалось отправить смс на номер: %s — %s', $user->phone, $exception->getMessage()));
        }
    }

    private function getSmsText()
    {
        $text = null;

        $settings = get_hightload_row(
            'hg_smsc',
            'api',
            ['UF_ENABLE', 'UF_TEXT'],
            'UF_IBLOCK_CODE'
        );

        if ($settings && $settings['UF_ENABLE'] && !empty($settings['UF_TEXT'])) {
            $text = $settings['UF_TEXT'];
        }

        return $text;
    }
}
