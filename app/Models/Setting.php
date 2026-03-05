<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public function getSetting($key)
    {
        $setting = $this->where(['key' => $key])->first();
        if (isset($setting->value)) {
            return $setting->value;
        }

        return null;
    }

    public function setSetting($key, $value)
    {
        $setting = $this->where(['key' => $key])->first();
        if (isset($setting->key)) {
            $setting->value = $value;

            return $setting->save();
        } else {
            return $this->create(['key' => $key, 'value' => $value]);
        }
    }
}
