<?php

namespace App\Http\Requests\ConfigurationRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;


class StoreProcedureConfigurationRequest extends FormRequest
{
   /**
     * Détermine si l'utilisateur est autorisé à faire cette demande.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Renvoie les règles de validation qui s'appliquent à la demande.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'token_communication' => 'required|string|max:300',
            'cle_cryptage' => 'required|string|max:300',
            'nom_dossier' => 'required|string|max:300',
            'db_database' => 'required|string|regex:/^[a-z_]+$/|min:20|max:100'
        ];
    }

    /**
     * Renvoie les messages de validation personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
       
            'token_communication.required' => 'Le token de communication est obligatoire.',
            'token_communication.string' => 'Le token de communication doit être une chaîne de caractères.',
            'token_communication.max' => 'Le token de communication ne peut pas dépasser 300 caractères.',
            
            'cle_cryptage_generer.required' => 'Le champ clé de cryptage est obligatoire.',
            'cle_cryptage_generer.string' => 'Le champ clé de cryptage doit être une chaîne de caractères.',
            'cle_cryptage_generer.max' => 'Le champ clé de cryptage ne peut pas dépasser 300 caractères.',
    
            'db_database.required' => 'Le champ de la base de données est obligatoire.',
            'db_database.string' => 'Le champ de la base de données doit être une chaîne de caractères.',
            'db_database.regex' => 'Le champ de la base de données ne doit contenir que des lettres minuscules et des underscores.',
            'db_database.min' => 'Le champ de la base de données doit comporter au moins 20 caractères.',
            'db_database.max' => 'Le champ de la base de données ne peut pas dépasser 100 caractères.',
                
            'nom_dossier.required' => 'Le champ du nom du dossier est obligatoire.',
            'nom_dossier.string' => 'Le champ du nom du dossier doit être une chaîne de caractères.',
            'nom_dossier.regex' => 'Le champ du nom du dossier ne doit contenir que des lettres (majuscules et minuscules).',
            'nom_dossier.max' => 'Le champ du nom du dossier ne peut pas dépasser 50 caractères.',
            
        ];
    }

    /**
     * Gère une tentative de validation échouée.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = new JsonResponse([
            'code' => 422,
            'success' => false,
            'error' => true,
            'message' => "Erreur de validation",
            'errors' => $validator->errors(),
        ]);

        throw new HttpResponseException($response);
    }
}
