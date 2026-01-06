<?php

namespace App\Http\Controllers;

/**
 * @OA\PathItem(path="/api/v1")
 *
 * @OA\Info(
 *      version="1.0.0",
 *      title="SDJR API",
 *      description="API documentation for SDJR application",
 *
 *      @OA\Contact(
 *          email="admin@sdjr.com"
 *      ),
 *
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 */
abstract class Controller
{
    //
}
