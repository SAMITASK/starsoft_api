<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
    protected $connection = 'bdwenco';
    protected $table = 'EMPRESA';
    protected $primaryKey = 'EMP_CODIGO';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['EMP_CODIGO', 'EMP_RAZON_NOMBRE'];


    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'company_user',
            'company_id',
            'user_id'
        );
    }
}
