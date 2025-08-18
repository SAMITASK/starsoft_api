<?php

namespace App\Models\Product;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyModel extends Model
{
    use HasFactory, HasDynamicConnection;
    
    protected $table = 'FAMILIA';
    protected $primaryKey = 'FAM_CODIGO';
    public $incrementing = false; // porque parece que el código no es autoincremental
    public $timestamps = false;
    protected $keyType = 'string';


    protected $fillable = [
        'FAM_CODIGO',
        'FAM_NOMBRE',
    ];
}
