<?php

namespace App\Http\Controllers\ConfigurationController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigurationRequest\StoreProcedureConfigurationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use PDO;
use Illuminate\Support\Facades\File;

class ProcedureConfigurationController extends Controller
{
    public function store(StoreProcedureConfigurationRequest $request)
    {
        // Récupérer la valeur du header 'X-HOST-NAME'
        $nomDomaine = $request->header('X-HOST-NAME');

        // Vérification du nom de domaine
        if ($nomDomaine === "clesaas.amosag.local") {

            // Vérification que nom_dossier est bien défini et n'est pas vide
            if (empty($request->nom_dossier)) {
                return response()->json([
                    "code" => 400,
                    "message" => "Le nom du dossier ne peut pas être vide."
                ]);
            }

            // Vérifie si le dossier n'existe pas déjà
            if (!Storage::exists($request->nom_dossier)) {
                Storage::makeDirectory($request->nom_dossier);
                Storage::makeDirectory($request->nom_dossier . '/administration_' . $request->nom_dossier);
                Storage::makeDirectory($request->nom_dossier . '/eleves_' . $request->nom_dossier);
                Storage::makeDirectory($request->nom_dossier . '/enseignants_' . $request->nom_dossier);
                Storage::makeDirectory($request->nom_dossier . '/autres_' . $request->nom_dossier);
                Storage::makeDirectory($request->nom_dossier . '/telechargement_' . $request->nom_dossier);

                // Vérifier si le dossier a bien été créé
                if (Storage::exists($request->nom_dossier)) {
                    // Chemin vers le fichier .env
                    $envFilePath = base_path('.env');
                    $nomBaseDeDonneesMajuscule = strtoupper($request->db_database);

                    // Vérifier si le fichier .env existe
                    if (!File::exists($envFilePath)) {
                        // Créer le fichier .env si nécessaire
                        File::put($envFilePath, "");
                    }

                    // Ajouter les nouvelles variables dans le fichier .env
                    File::append($envFilePath, "\nDB_CONNECTION_$nomBaseDeDonneesMajuscule=connection_$request->db_database\n");
                    File::append($envFilePath, "DB_DATABASE_$nomBaseDeDonneesMajuscule=$request->db_database\n");

                    // Chemin vers le fichier config/database.php
                    $configPath = config_path('database.php');
                    $configFile = file_get_contents($configPath);

                    // Nouvelle configuration de connexion
                    $newConfig = "
                        // Connexion personnalisée pour la base de données $request->db_database
                        'connection_$request->db_database' => [
                            'driver' => 'mysql',
                            'host' => env('DB_HOST', '127.0.0.1'),
                            'port' => env('DB_PORT', '3306'),
                            'database' => env('DB_DATABASE_$nomBaseDeDonneesMajuscule', '$request->db_database'),
                            'username' => env('DB_USERNAME', 'forge'),
                            'password' => env('DB_PASSWORD', ''),
                            'unix_socket' => env('DB_SOCKET', ''),
                            'charset' => 'utf8mb4',
                            'collation' => 'utf8mb4_unicode_ci',
                            'prefix' => '',
                            'prefix_indexes' => true,
                            'strict' => true,
                            'engine' => null,
                            'options' => extension_loaded('pdo_mysql') ? array_filter([
                                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                            ]) : [],
                        ],
                    ";

                    // Insérer la nouvelle configuration dans le fichier
                    $configFile = preg_replace('/(\'connections\'\s*=>\s*\[\s*)/', "$1\n$newConfig", $configFile);
                    file_put_contents($configPath, $configFile);

                    //try {
                        // Chemin vers le fichier config/auth.php
                        $configPathAuth = config_path('auth.php');
                        $configFileAuth = file_get_contents($configPathAuth);

                        // Nouvelle configuration de garde
                        $newGuardConfig = "
                            // Guard pour l'institution scolaire du sous-domaine $request->db_database
                            'admin_$request->db_database' => [
                                'driver' => 'session',
                                'provider' => 'admins_$request->db_database',
                            ],
                        ";
                        $configFileAuth = preg_replace('/(\'guards\'\s*=>\s*\[\s*)/', "$1\n$newGuardConfig", $configFileAuth);

                        // Nouvelle configuration de fournisseur
                        $newProviderConfig = "
                            // Provider pour l'institution scolaire du sous-domaine $request->db_database
                            'admins_$request->db_database' => [
                                'driver' => 'eloquent',
                                'model' => App\Models\Administrateurs::class,
                                'connection' => 'connection_$request->db_database',
                            ],
                        ";
                        $configFileAuth = preg_replace('/(\'providers\'\s*=>\s*\[\s*)/', "$1\n$newProviderConfig", $configFileAuth);

                        // Sauvegarder les modifications dans le fichier
                        file_put_contents($configPathAuth, $configFileAuth);

                        // Vérifier si la base de données existe déjà
                        $databaseExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$request->db_database'");

                        if (empty($databaseExists)) {

                            try {

                                // Si la base de données n'existe pas, la créer
                                DB::statement("CREATE DATABASE $request->db_database");

                                // Configurer la connexion à la nouvelle base de données
                                $connection = config("database.connections.connection_$request->db_database");
                                $connection['database'] = $request->db_database;
                                config(["database.connections.connection_$request->db_database" => $connection]);
                
                                $migrationFiles = [
                                    '/database/migrations/0001_01_01_000001_create_cache_table.php', 
                                    '/database/migrations/0001_01_01_000002_create_jobs_table.php', 
                                    '/database/migrations/2024_11_03_144407_create_personal_access_tokens_table.php', 
                                    '/database/migrations/2024_11_03_153305_create_informations_table.php',
                                ];
                            
                                foreach ($migrationFiles as $migrationFile) {
                                    Artisan::call('migrate', [
                                        '--database' => "connection_$request->db_database",
                                        '--path' => $migrationFile,
                                    ]);
                                }

                                // Retourner une réponse JSON indiquant le succès de l'enregistrement
                                return response()->json([
                                    "code" => 200,
                                    "message" => "Vous venez d'enregistrer le client et effectuer la configuration automatique",
                                ]); 
                                    
                            } catch (\Exception $e) {

                                DB::statement("DROP DATABASE $request->db_database");
                                
                                // Retourner une réponse JSON indiquant
                                return response()->json([
                                    "code" => 500,
                                    "message" => "Erreur lors de l'exécution des migrations",
                                ]); 
                            }
                        } else {

                            // Retourner une réponse JSON indiquant
                            return response()->json([
                                "code" => 500,
                                "message" => "Erreur la base de donnée existe déja",
                            ]); 
                        }


                    /*} catch (\Exception $e) {
                        // Gérer l'exception
                        return response()->json([
                            "code" => 500,
                            "message" => "Erreur lors de la configuration: " . $e->getMessage()
                        ]);
                    }*/
                } else {
                    return response()->json([
                        "code" => 202,
                        "message" => "Une erreur s'est produite lors de la création des dossiers"
                    ]);
                }
            } else {
                return response()->json([
                    "code" => 202,
                    "message" => "Configuration déjà effectuée"
                ]);
            }
        }

        return response()->json([
            "code" => 401,
            "message" => "Vous n'avez pas l'autorisation requise pour cette action"
        ]);
    }
}
