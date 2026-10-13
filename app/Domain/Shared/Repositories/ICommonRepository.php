<?php

namespace App\Domain\Shared\Repositories;

interface ICommonRepository
{
    /**
     * Obtiene las categorías de cultivo.
     */
    public function getCategoriasCultivo(): array;

    /**
     * Obtiene los tipos de ciclo de cultivo.
     */
    public function getTiposCicloCultivo(): array;

    /**
     * Obtiene el catálogo de zonas.
     */
    public function getZonas(): array;

    /**
     * Obtiene el catálogo de territorios para una zona.
     */
    public function getTerritorios(string $zonaCodigo): array;

    /**
     * Obtiene el catálogo de productos.
     */
    public function getProductos(): array;
}
