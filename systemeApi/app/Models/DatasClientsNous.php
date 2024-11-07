<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DatasClientsNous extends Model
{
    //    // Utilise les traits nécessaires
    use HasUuids;

    protected $table = 'datas_clients_nous';
    protected $primaryKey = 'id_datas_clients_nous';
    protected $fillable = [
      'db_host',
      'db_port',
      'db_database',
      'db_username',
      'db_password',
      'token_communication',
      'cle_identification',
      'cle_cryptage',
      'nom_dossier_storage',
    ];
}
