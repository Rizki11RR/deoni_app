<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['document_type', 'last_number'])]
class DocumentSequence extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['last_number' => 'integer'];
    }
}
