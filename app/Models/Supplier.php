<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'MAEPROV';
    protected $primaryKey = 'PRVCCODIGO';
    protected $keyType = 'string';
    public $timestamps = false;

}
