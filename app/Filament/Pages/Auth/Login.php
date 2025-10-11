<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override the default forms to replace the email input with a single identifier field
     * that accepts either email or phone.
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getIdentifierFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Identifier (email or phone) input component.
     */
    protected function getIdentifierFormComponent(): Component
    {
        return TextInput::make('identifier')
            ->label('Email or Phone')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * Reuse the base password component.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * Reuse the base remember component.
     */
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label(__('filament-panels::pages/auth/login.form.remember.label'));
    }

    /**
     * Map the identifier to the correct auth credentials array.
     * If identifier looks like an email, authenticate with email; otherwise with phone.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $identifier = (string) ($data['identifier'] ?? '');

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return [
            $field => $identifier,
            'password' => $data['password'] ?? null,
        ];
    }

    /**
     * Point validation errors at the identifier field rather than the default email field.
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.identifier' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    /**
     * Override the brand logo to use FarmEx logo instead of Laravel text.
     */
    public function getBrandLogo(): string
    {
        return asset('images/farmex-logo-main-with-tagline.png');
    }
}


