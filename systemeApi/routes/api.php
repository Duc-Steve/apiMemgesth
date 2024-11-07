<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConfigurationController\ProcedureConfigurationController;

// Route pour l'API api.memgesth.com
// Utilisé pour l'interaction avec votre API
// Exemple : https://api.memgesth.com/

// Route::domain('api.memgesth.com')->group(function () {
    Route::get('/', function () {
        return "L'URL est invalide. Voici ce que nous savons.";
    });

    //route d'Enregistrer un nouveau propriétaire (configuration)
    Route::post('configuration', [ProcedureConfigurationController::class, 'store']);

//});