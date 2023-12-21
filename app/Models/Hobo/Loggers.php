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

    public static function allLogger()
    {
        $logger = self::all();
        $formatted = $logger->map(function ($storedData) {
            return [
                'id' => $storedData['id'],
                'logger_sn' => $storedData['sn'],
                'nama_loger' => $storedData['nama'],
                'lat' => $storedData['lat'],
                'lng' => $storedData['lng'],
            ];
        });

        return $formatted->toArray();
    }
}
