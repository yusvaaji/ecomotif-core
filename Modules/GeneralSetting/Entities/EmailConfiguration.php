<?php

namespace Modules\GeneralSetting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_host',
        'mail_port', 
        'mail_encryption',
        'smtp_username',
        'smtp_password',
        'mail_from_name',
        'mail_from_address'
    ];
    
    protected static function newFactory()
    {
        return \Modules\GeneralSetting\Database\factories\EmailConfigurationFactory::new();
    }
}
