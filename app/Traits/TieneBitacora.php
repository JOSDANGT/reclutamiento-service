<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TieneBitacora
{
    public function bitacoras(): MorphMany
    {
        return $this->morphMany(Bitacora::class, 'model');
    }
}
