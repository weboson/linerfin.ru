<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 
 * @OA\Info(title="Linerfin",version="1.0.0"),
 * @OA\SecurityScheme(
 *      @OA\Server(
 *          url="https://auth.linerfin.ru",
 *      ),
 *      securityScheme="bearer",   
 *      type="http",
 *      scheme="bearer",
 * ),
 * @OA\PathItem(path="/api/")
 */
class BankController extends Controller
{
    //
}
