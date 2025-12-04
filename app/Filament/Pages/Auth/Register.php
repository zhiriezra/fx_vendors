<?php

namespace App\Filament\Pages\Auth;

use App\Models\Bank;
use App\Models\Country;
use App\Models\Lga;
use App\Models\State;
use App\Models\Ward;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Support\Enums\MaxWidth;


class Register extends BaseRegister
{
    //increasing the width of the form
    protected ?string $maxWidth = '4xl';
    /**
     * Build multi-step registration form.
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Wizard::make([
                            Wizard\Step::make('Select Country')
                                ->schema([
                                    Select::make('country_id')
                                        ->label('Country')
                                        ->options(fn () => Country::query()->pluck('name', 'id'))
                                        ->required()
                                        ->reactive(),
                                    TextInput::make('email')
                                        ->label('Email (if any)')
                                        ->email()
                                        ->maxLength(255)
                                        ->unique(User::class, 'email'),
                                    TextInput::make('phone')
                                        ->label('Main Phone Number')
                                        ->required()
                                        ->rule('digits_between:10,11')
                                        ->unique(User::class, 'phone'),
                                    TextInput::make('password')
                                        ->label(__('filament-panels::pages/auth/register.form.password.label'))
                                        ->password()
                                        ->revealable(filament()->arePasswordsRevealable())
                                        ->required()
                                        ->rule(Password::default())
                                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                        ->same('passwordConfirmation')
                                        ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
                                    TextInput::make('passwordConfirmation')
                                        ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                                        ->password()
                                        ->revealable(filament()->arePasswordsRevealable())
                                        ->required()
                                        ->dehydrated(false),
                                ]),

                            Wizard\Step::make('Update Bio')
                                ->schema([
                                    FileUpload::make('profile_image')
                                        ->label('Profile Image')
                                        ->image()
                                        ->directory('profile-images')
                                        ->imageEditor()
                                        ->downloadable()
                                        ->required(),
                                    TextInput::make('firstname')
                                        ->label('First Name')
                                        ->required(),
                                    TextInput::make('middlename')
                                        ->label('Middle Name')
                                        ->nullable(),
                                    TextInput::make('lastname')
                                        ->label('Last Name')
                                        ->required(),
                                    Select::make('gender')
                                        ->options([
                                            'male' => 'Male',
                                            'female' => 'Female',
                                        ])
                                        ->required(),
                                    Select::make('marital_status')
                                        ->options([
                                            'single' => 'Single',
                                            'married' => 'Married',
                                            'divorced' => 'Divorced',
                                        ])
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    DatePicker::make('dob')
                                        ->label('Date of Birth')
                                        ->required(),
                                    TextInput::make('national_id')
                                        ->label('National ID')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 2),
                                    TextInput::make('secondary_phone')
                                        ->label('Secondary Phone')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 2),
                                    TextInput::make('nin')
                                        ->label('NIN')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    TextInput::make('bvn')
                                        ->label('BVN')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                ]),

                            Wizard\Step::make('Location')
                                ->schema([
                                    Select::make('state_id')
                                        ->label(fn ($get) => (int) ($get('country_id') ?? 0) === 2 ? 'County' : 'State')
                                        ->options(fn ($get) => State::query()
                                            ->when($get('country_id'), fn ($q, $countryId) => $q->where('country_id', $countryId))
                                            ->pluck('name', 'id'))
                                        ->reactive()
                                        ->required(),
                                    Select::make('lga_id')
                                        ->label(fn ($get) => (int) ($get('country_id') ?? 0) === 2 ? 'Sub County' : 'LGA')
                                        ->options(fn ($get) => Lga::query()
                                            ->when($get('state_id'), fn ($q, $stateId) => $q->where('state_id', $stateId))
                                            ->pluck('name', 'id'))
                                        ->reactive()
                                        ->required(),
                                    Select::make('ward_id')
                                        ->label('Ward')
                                        ->options(fn ($get) => Ward::query()
                                            ->when($get('lga_id'), fn ($q, $lgaId) => $q->where('lga_id', $lgaId))
                                            ->pluck('name', 'id'))
                                        ->reactive()
                                        ->searchable()
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 2)
                                        ->required(),
                                    TextInput::make('permanent_address')
                                        ->label('Permanent Address')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    TextInput::make('current_address')
                                        ->label('Current Address')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                ]),

                            Wizard\Step::make('Update Business')
                                ->schema([
                                    TextInput::make('business_email')
                                        ->label('Business Email')
                                        ->email()
                                        ->nullable(),
                                    TextInput::make('business_mobile')
                                        ->label('Business Mobile')
                                        ->nullable(),
                                    TextInput::make('business_name')
                                        ->label('Business Name')
                                        ->required(),
                                    TextInput::make('business_address')
                                        ->label('Business Address')
                                        ->required(),
                                    TextInput::make('registration_no')
                                        ->label('Registration No')
                                        ->nullable(),
                                    TextInput::make('tin')
                                        ->label('TIN')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    Select::make('business_type')
                                        ->label('Business Type')
                                        ->options([
                                            'sole proprietorship' => 'Sole Proprietorship',
                                            'partnership' => 'Partnership',
                                            'limited liability' => 'Limited Liability',
                                        ])
                                        ->required(),
                                    Select::make('bank')
                                        ->options(fn () => Bank::pluck('name', 'name'))
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    TextInput::make('account_no')
                                        ->label('Account Number')
                                        ->required()
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    TextInput::make('account_name')
                                        ->label('Account Name')
                                        ->required()
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 1),
                                    TextInput::make('kra_pin')
                                        ->label('KRA PIN')
                                        ->visible(fn ($get) => (int) ($get('country_id') ?? 0) === 2),
                                ]),

                            Wizard\Step::make('Privacy Policy')
                                ->schema([
                                    Section::make('Privacy Policy')
                                        ->schema([
                                            \Filament\Forms\Components\View::make('auth.privacy-policy-text'),

                                            \Filament\Forms\Components\Checkbox::make('privacy_accepted')
                                                ->label('I have read and agree to the Privacy Policy')
                                                ->rules(['accepted']) 
                                                ->required() 
                                                ->dehydrated(false) 
                                                ->validationMessages([
                                                    'privacy_accepted.accepted' => 'You must accept the privacy policy before continuing.',
                                                ]),
                                        ])
                                        ->columnSpanFull()
                                ])
                               // ->skippable(false)
                        ])
                            ->columnSpanFull(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Optionally mutate data before registration.
     * Ensure defaults required by your app.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        // Set vendor default and mark profile as completed on registration
        $data['user_type_id'] = 2;
        $data['profile_completed'] = 1;

        return $data;
    }

    /**
     * Persist the user and related business info.
     *
     * @param array<string, mixed> $data
     */
    protected function handleRegistration(array $data): Model
    {
        // Create the user (user table fields only)
        /** @var User $user */
        $user = User::create([
            'firstname' => $data['firstname'] ?? null,
            'middlename' => $data['middlename'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'password' => $data['password'],
            'user_type_id' => 2,
            'profile_completed' => 1,
            'profile_image' => $data['profile_image'] ?? null,
        ]);

        // Create the vendor profile (vendor table fields)
        \App\Models\Vendor::create([
            'user_id' => $user->id,
            'country_id' => $data['country_id'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'lga_id' => $data['lga_id'] ?? null,
            'ward_id' => $data['ward_id'] ?? null,
            'permanent_address' => $data['permanent_address'] ?? null,
            'current_location' => $data['current_location'] ?? null,
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'dob' => $data['dob'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'kra_pin' => $data['kra_pin'] ?? null,
            'secondary_phone' => $data['secondary_phone'] ?? null,
            'nin' => $data['nin'] ?? null,
            'bvn' => $data['bvn'] ?? null,
            'business_email' => $data['business_email'] ?? null,
            'business_mobile' => $data['business_mobile'] ?? null,
            'business_name' => $data['business_name'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'registration_no' => $data['registration_no'] ?? null,
            'tin' => $data['tin'] ?? null,
            'business_type' => $data['business_type'] ?? null,
            'bank' => $data['bank'] ?? null,
            'account_no' => $data['account_no'] ?? null,
            'account_name' => $data['account_name'] ?? null,
        ]);

        return $user;
    }

    /**
     * Override the brand logo to use FarmEx logo instead of Laravel text.
     */
    public function getBrandLogo(): string
    {
        return asset('images/farmex-logo-main-with-tagline.png');
    }
}

