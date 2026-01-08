<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ColombiaApiController extends Controller
{
    /**
     * Get all departments from Colombia API
     */
    public function getDepartments()
    {
        try {
            $response = Http::get('https://api-places-colombia.herokuapp.com/api/departments');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar departamentos'], 500);
        }
    }

    /**
     * Get cities by department code
     */
    public function getCitiesByDepartment($departmentCode)
    {
        try {
            $response = Http::get("https://api-places-colombia.herokuapp.com/api/cities/{$departmentCode}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar ciudades'], 500);
        }
    }
}
