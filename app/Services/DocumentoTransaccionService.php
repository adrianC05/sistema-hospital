<?php

namespace App\Services;

use App\Models\DocumentoTransaccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Carbon\Carbon;

/**
 * Servicio para manejar la lógica de negocio de los Documentos Transaccionales.
 * Contiene la lógica para los procesos de descargo y facturación. [cite: 9]
 */
class DocumentoTransaccionService
{
    /** @const string Estado inicial o pendiente. */
    const ESTADO_PENDIENTE = 'Pendiente';

    /** @const string Estado para un documento descargado y listo para facturar. */
    const ESTADO_DESCARGADO = 'Descargado';

    /** @const string Estado para un documento que ya ha sido facturado. */
    const ESTADO_FACTURADO = 'Facturado';

    /** @const string Tipo de documento para un descargo. */
    const TIPO_DESCARGO = 'Descargo';

    /** @const string Tipo de documento para una factura. */
    const TIPO_FACTURA = 'Factura';

    /**
     * Marca un documento de tipo 'Descargo' como 'Descargado'.
     * Cambia el estado del documento a "Descargado". [cite: 2]
     *
     * @param DocumentoTransaccion $documento El documento a marcar.
     * @return DocumentoTransaccion El documento actualizado.
     * @throws Exception Si el documento no puede ser marcado como descargado.
     */
    public function marcarComoDescargado(DocumentoTransaccion $documento): DocumentoTransaccion
    {
        if ($documento->tipo !== self::TIPO_DESCARGO) {
            throw new Exception("Solo los documentos de tipo 'Descargo' pueden ser marcados.");
        }

        // Solo se puede marcar si está 'Pendiente'
        if ($documento->estado !== self::ESTADO_PENDIENTE) {
            throw new Exception("El descargo debe estar en estado 'Pendiente' para ser marcado como 'Descargado'. Estado actual: '{$documento->estado}'.");
        }

        $documento->estado = self::ESTADO_DESCARGADO;
        $documento->save();

        return $documento;
    }

    /**
     * Procesa la facturación de un descargo.
     * Clona el descargo para crear una factura y actualiza los estados. [cite: 4, 5]
     *
     * @param DocumentoTransaccion $descargo El documento de descargo a facturar.
     * @return DocumentoTransaccion La nueva factura generada.
     * @throws Exception Si el documento no puede ser facturado.
     */
    public function facturarDescargo(DocumentoTransaccion $descargo): DocumentoTransaccion
    {
        // Validar que solo los descargos en estado "Descargado" puedan facturarse. [cite: 6]
        if ($descargo->tipo !== self::TIPO_DESCARGO || $descargo->estado !== self::ESTADO_DESCARGADO) {
            throw new Exception("Solo se pueden facturar descargos en estado 'Descargado'.");
        }

        // Usar transacción para asegurar la integridad de los datos. [cite: 3]
        return DB::transaction(function () use ($descargo) {

            // Clonar el documento transaccional asociado para generar la factura. [cite: 4]
            $factura = $descargo->replicate(['numero']);

            $factura->tipo = self::TIPO_FACTURA;
            $factura->estado = self::ESTADO_FACTURADO; // La nueva factura nace 'Facturada'. [cite: 5]
            $factura->numero = 'F-' . strtoupper(Str::random(8));
            $factura->fecha = Carbon::now();
            $factura->save();

            // Clonar las líneas.
            foreach ($descargo->lineas as $lineaOriginal) {
                $nuevaLinea = $lineaOriginal->replicate();
                $nuevaLinea->documento_transaccion_id = $factura->id;
                $nuevaLinea->save();
            }

            // Actualizar el estado del descargo original a "Facturado". [cite: 5]
            $descargo->estado = self::ESTADO_FACTURADO;
            $descargo->save();

            return $factura;
        });
    }

    /**
     * Calcula y actualiza el valor total de un DocumentoTransaccion.
     *
     * @param DocumentoTransaccion $documento
     * @return DocumentoTransaccion
     */
    public function recalcularTotal(DocumentoTransaccion $documento): DocumentoTransaccion
    {
        $total = $documento->lineas()->sum('subtotal');
        $documento->valor_total = $total;
        $documento->save();
        return $documento;
    }
}