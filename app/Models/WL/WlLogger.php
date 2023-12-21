<?php

namespace App\Models\WL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WlLogger extends Model
{
    use HasFactory;
    protected $fillable = ['sn', 'nama', 'lat', 'lng'];

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
