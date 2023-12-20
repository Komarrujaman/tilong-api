<?php

namespace App\Models\Hobo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hobo\Sensors;

class DataSensor extends Model
{
    use HasFactory;
    protected $fillable = ['sensor_id', 'data_type_id', 'si_value', 'si_unit', 'us_value', 'us_unit', 'scaled_value', 'scaled_unit', 'sinyal', 'timestamp'];

    protected $table = 'data_sensors';

    public function sensor()
    {
        return $this->belongsTo(Sensors::class, 'id');
    }
}
