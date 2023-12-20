<?php

namespace App\Models\Hobo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loggers extends Model
{
    use HasFactory;

    protected $fillable = ['sn', 'nama', 'lat', 'lng'];
}
