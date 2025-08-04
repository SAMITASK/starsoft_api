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
        return $this->hasMany(OCSDModel::class, 'OC_CNUMORD', 'OC_CNUMORD');
    }

    public function responsible()
    {
        return $this->belongsTo(Responsible::class, 'OC_CSOLICT', 'RESPONSABLE_CODIGO');
    }

    public function required()
    {
        return $this->belongsTo(Required::class, 'OC_SOLICITA', 'TCLAVE');
    }

    public function getAllOCS()
    {
        return $this->setConnection($this->connection)
            ->select('OC_CNUMORD', 'OC_CCODPRO', 'OC_CRAZSOC', 'OC_COBSERV', 'OC_CSOLICT', 'OC_CSITORD', 'OC_DFECENT', 'TipoDocumento')
            ->get();
    }

    public static function getOrderWithProducts($connectionName, $orderId)
    {
        $order = self::on($connectionName)->where('OC_CNUMORD', $orderId)->with('responsible')->with('required')->first();

        if ($order) {
            $order->setRelation('products', OCSDModel::on($connectionName)
                ->where('OC_CNUMORD', $order->OC_CNUMORD)
                ->get());
        }

        return $order;
    }
}
