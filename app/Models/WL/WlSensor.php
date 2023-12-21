<?php

namespace App\Models\WL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WlSensor extends Model
{
    use HasFactory;
    protected $fillable = ['logger_id', 'sensor_sn', 'sensor_key', 'jenis', 'measurement_type'];

    public function logger()
    {
        return $this->belongsTo(WlLogger::class, 'id');
    }

    public function dataSensor()
    {
        return $this->hasMany(WlDataSensor::class, 'sensor_id');
    }
}
