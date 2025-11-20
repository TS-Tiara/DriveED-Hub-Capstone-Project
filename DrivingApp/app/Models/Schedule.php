<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Instructor;
use App\Models\Admin;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules'; // Add this line

    protected $fillable = [
        'school_id',
        'instructor_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'created_by',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}