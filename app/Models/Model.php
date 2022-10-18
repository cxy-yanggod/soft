<?php

namespace App\Models;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->format('Y-m-d H:i:s');
    }
}