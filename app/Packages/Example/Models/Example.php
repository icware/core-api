<?php

namespace App\Packages\Example\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Example extends Model
{
    use HasFactory;

    // Especifique o nome da tabela, se for diferente do padrão (opcional)
    protected $table = 'examples';

    // Defina os campos que podem ser preenchidos em massa (opcional)
    protected $fillable = ['name'];

    // Defina os campos que devem ser ocultos ao serializar o modelo para JSON (opcional)
    protected $hidden = [];

    // Defina os campos que devem ser convertidos para tipos nativos (opcional)
    protected $casts = [];
}