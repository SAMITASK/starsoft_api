<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OCModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'COMOVC';

    public $timestamps = false;


    public function products()
    {
        return $this->hasMany(OCDModel::class, 'OC_CNUMORD', 'OC_CNUMORD');
    }


    public static function getOrderWithProducts($connectionName, $orderId)
    {
        $order = self::on($connectionName)->where('OC_CNUMORD', $orderId)->first();

        if ($order) {
            $order->setRelation('products', OCDModel::on($connectionName)
                ->where('OC_CNUMORD', $order->OC_CNUMORD)
                ->get());
        }

        return $order;
    }

    public static function getAllOrdersWithProducts(string $connectionName)
    {
        // Cambiamos la conexión por defecto del modelo relacionado antes de la consulta
        (new OCDModel)->setConnection($connectionName);

        return self::on($connectionName)
            ->with('products') // Ya no necesitas pasarle nada
            ->get();
    }

    public function getAllOC()
    {
        return $this->setConnection($this->connection)
            ->select('OC_CNUMORD', 'OC_CCODPRO', 'OC_CRAZSOC', 'OC_COBSERV', 'OC_CSOLICT', 'OC_CSITORD', 'OC_DFECENT','TipoDocumento')
            ->get();
    }
}
