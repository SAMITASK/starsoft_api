<?php

namespace App\Traits;

trait HasDynamicConnection
{
    public function setConnectionName(string $connectionName): static
    {

        /**
         * Cambia dinámicamente la conexión del modelo.
         *
         * @param string $connectionName
         * @return static
         */
        $this->setConnection($connectionName);
        return $this;
    }
}
