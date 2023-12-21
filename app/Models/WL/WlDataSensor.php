<?php

namespace App\Models\WL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WlDataSensor extends Model
{
    use HasFactory;
    protected $fillable = ['sensor_id', 'data_type_id', 'si_value', 'si_unit', 'us_value', 'us_unit', 'scaled_value', 'scaled_unit', 'sinyal', 'volume', 'debit', 'level', 'timestamp'];

    protected $table = 'wl_data_sensors';

    public function sensor()
    {
        return $this->belongsTo(WlSensor::class, 'id');
    }
}
