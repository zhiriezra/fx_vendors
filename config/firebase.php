<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Projects Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for multiple Firebase projects.
    |
    */
    'projects' => [
        'app' => [
            'credentials' => [
                'file' => env('FIREBASE_CREDENTIALS', storage_path('firebase/firebase-service.json')),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | The Firebase Realtime Database URL.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | The Firebase project ID.
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Storage Bucket
    |--------------------------------------------------------------------------
    |
    | The Firebase Storage bucket name.
    |
    */
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase API Key
    |--------------------------------------------------------------------------
    |
    | The Firebase API key.
    |
    */
    'api_key' => env('FIREBASE_API_KEY', ''),
]; 