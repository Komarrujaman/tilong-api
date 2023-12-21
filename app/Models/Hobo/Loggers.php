<?php

namespace App\Models\Hobo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loggers extends Model
{
    use HasFactory;

    protected $fillable = ['sn', 'nama', 'lat', 'lng'];
    public function sensors()
    {
        return $this->hasMany(Sensors::class, 'logger_id');
    }
}
