<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * SCRUM-375/376: excepción base de cualquier motivo por el que un ítem de
 * una orden no se puede vender — clasificación fiscal pendiente, producto
 * despublicado/inactivo/vencido, o sin stock suficiente.
 * OrderController::store() captura esta clase una sola vez y devuelve 422
 * con el mensaje ya traducido, sin importar cuál de los motivos concretos
 * aplicó. Antes de SCRUM-375 el contrato estaba partido: unos motivos
 * lanzaban excepción y otros devolvían `false` desde
 * ProductService::validateProductAvailability(), obligando al controlador a
 * manejar dos caminos distintos para la misma respuesta 422.
 */
abstract class OrderItemRejectedException extends RuntimeException {}
