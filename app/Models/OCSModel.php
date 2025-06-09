<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OCSModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'COMOVC_S';

    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(OCDModel::class, 'OC_CNUMORD', 'OC_CNUMORD');
    }

        public function getAllOCS()
    {
        return $this->setConnection($this->connection)
            ->select('OC_CNUMORD', 'OC_CCODPRO', 'OC_CRAZSOC', 'OC_COBSERV', 'OC_CSOLICT', 'OC_CSITORD', 'OC_DFECENT','TipoDocumento')
            ->get();
    }
}
