<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DrivingSchool1Controller extends Controller
{
    public function index()
    {
        return redirect()->route('drivingschool1.login');
    }
}