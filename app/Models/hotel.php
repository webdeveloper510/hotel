<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hotel extends Model
{
    use HasFactory;
    protected $table="hotels";

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    
}
