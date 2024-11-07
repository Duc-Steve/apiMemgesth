<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Informations extends Model
{
    // Utilise les traits nécessaires
    use HasUuids;

    protected $table = 'informations';
    protected $primaryKey = 'id_information';
    protected $fillable = [
      'db_host',
      'db_port',
      'db_database',
      'db_username',
      'db_password',
      'token_communication',
      'cle_authentification',
      'cle_cryptage',
      'nom_dossier_storage',
    ];
}
