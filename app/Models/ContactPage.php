<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasFactory;

    protected $fillable = ['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat' ];
}
