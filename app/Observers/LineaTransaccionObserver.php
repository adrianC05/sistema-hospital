<?php

namespace App\Observers;

use App\Models\LineaTransaccion;
use App\Models\Medicamento; // Importa otros modelos con stock si los tienes
// use App\Models\OtroProductoConStock;

class LineaTransaccionObserver
{
    /**
     * Handle the LineaTransaccion "created" event.
     */
    public function created(LineaTransaccion $lineaTransaccion): void
    {
        $documento = $lineaTransaccion->documentoTransaccion;

        // Descargar stock si es Factura o Descargo y el item tiene stock
        if (in_array($documento->tipo, ['Factura', 'Descargo'])) {
            $serviceable = $lineaTransaccion->serviceable;

            if ($serviceable instanceof Medicamento && property_exists($serviceable, 'stock')) {
                $serviceable->decrement('stock', $lineaTransaccion->cantidad);
            }
            // Ejemplo para otro tipo de producto con stock:
            // else if ($serviceable instanceof OtroProductoConStock && property_exists($serviceable, 'stock')) {
            //     $serviceable->decrement('stock', $lineaTransaccion->cantidad);
            // }
        }
    }

    /**
     * Handle the LineaTransaccion "deleted" event.
     * Esto es importante si se elimina una línea o se anula un documento.
     */
    public function deleted(LineaTransaccion $lineaTransaccion): void
    {
        $documento = $lineaTransaccion->documentoTransaccion;

        // Reintegrar stock si el documento era Factura o Descargo
        // Y el documento no está marcado como 'Anulada' (asumiendo que 'Anulada' ya manejó el stock)
        // O si simplemente se borra una línea de un documento 'Pendiente'.
        if (in_array($documento->tipo, ['Factura', 'Descargo']) && $documento->estado !== 'Anulada') {
            $serviceable = $lineaTransaccion->serviceable;

            if ($serviceable instanceof Medicamento && property_exists($serviceable, 'stock')) {
                $serviceable->increment('stock', $lineaTransaccion->cantidad);
            }
            // else if ($serviceable instanceof OtroProductoConStock && property_exists($serviceable, 'stock')) {
            //     $serviceable->increment('stock', $lineaTransaccion->cantidad);
            // }
        }
    }

    /**
     * Handle the LineaTransaccion "updated" event.
     * Si cambia la cantidad en una línea existente.
     */
    public function updated(LineaTransaccion $lineaTransaccion): void
    {
        if ($lineaTransaccion->isDirty('cantidad')) {
            $documento = $lineaTransaccion->documentoTransaccion;
            if (in_array($documento->tipo, ['Factura', 'Descargo']) && $documento->estado !== 'Anulada') {
                $serviceable = $lineaTransaccion->serviceable;
                $diferenciaCantidad = $lineaTransaccion->cantidad - $lineaTransaccion->getOriginal('cantidad');

                if ($serviceable instanceof Medicamento && property_exists($serviceable, 'stock')) {
                    // Si la nueva cantidad es mayor, se decrementa más stock.
                    // Si es menor, se incrementa (devuelve) stock.
                    $serviceable->decrement('stock', $diferenciaCantidad);
                }
                // ... otros modelos con stock
            }
        }
    }
}