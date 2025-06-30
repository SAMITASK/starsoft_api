<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OCSDModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'COMOVD_S';
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(OCSModel::class, 'OC_CNUMORD', 'OC_CNUMORD');
    }
}
