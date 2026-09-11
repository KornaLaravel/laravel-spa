<?php

declare(strict_types=1);

namespace App\Models;

use QCod\Settings\Setting\Setting as OriginalSetting;

class Setting extends OriginalSetting
{
    protected $table = 'settings';
}
