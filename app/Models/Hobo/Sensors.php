<?php

namespace App\Models\Hobo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensors extends Model
{
    use HasFactory;

    protected $fillable = ['logger_id', 'sensor_sn', 'sensor_key', 'measurement_type'];

    public function logger()
    {
        return $this->belongsTo(Loggers::class, 'id');
    }

    public function dataSensor()
    {
        return $this->hasMany(DataSensor::class, 'sensor_id');
    }
}
