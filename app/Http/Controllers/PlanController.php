<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Muestra la vista de planes.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('planes.index'); // Asegúrate de que existe un archivo planes.blade.php
    }
}
