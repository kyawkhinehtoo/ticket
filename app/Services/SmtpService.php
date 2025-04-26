<?php

namespace App\Services;

use App\Models\SmtpSetting;

class SmtpService
{
    public static function updateMailConfig()
    {
        $activeSetting = SmtpSetting::where('is_active', true)->first();
        
        if ($activeSetting) {
            config([
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $activeSetting->host,
                    'port' => $activeSetting->port,
                    'encryption' => $activeSetting->encryption,
                    'username' => $activeSetting->username,
                    'password' => $activeSetting->password,
                ],
                'mail.from' => [
                    'address' => $activeSetting->from_address,
                    'name' => $activeSetting->from_name,
                ]
            ]);
        }
    }
}