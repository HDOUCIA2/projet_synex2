<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'synex:admin:create';

    protected $description = 'Create an administrator account for Synex';

    public function handle(): int
    {
        $attributes = [
            'name' => trim((string) $this->ask('Nom complet')),
            'username' => mb_strtolower(trim((string) $this->ask('Identifiant'))),
            'email' => mb_strtolower(trim((string) $this->ask('Adresse e-mail'))),
        ];

        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:30', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Mot de passe (12 caractères minimum)');
        $confirmation = $this->secret('Confirmer le mot de passe');

        if (!is_string($password) || strlen($password) < 12) {
            $this->error('Le mot de passe doit contenir au moins 12 caractères.');

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Les mots de passe ne correspondent pas.');

            return self::FAILURE;
        }

        User::create([
            ...$attributes,
            'password' => $password,
            'role' => 'admin',
        ]);

        $this->info("Compte administrateur créé. Identifiant : {$attributes['username']}");

        return self::SUCCESS;
    }
}
