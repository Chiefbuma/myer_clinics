<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnosis extends Model
{
    protected $table = 'patient_diagnosis';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'patient_id',
        'diagnosis_id',
    ];

    protected $casts = [
        'patient_id' => 'string',
        'diagnosis_id' => 'string',
    ];

 
}
