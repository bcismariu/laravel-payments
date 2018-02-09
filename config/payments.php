<?php

return [

    /**
     * Identify the processor to be used from the list of the
     * available integrations below.
     */
    'processor' => 'konnektive',


    /**
     * Available drivers: konnektive
     */
    'integrations' => [
        'konnektive' => [
            'driver'    => 'konnektive',
            'loginId'   => env('KONNEKTIVE_LOGIN', 'konnekt'),
            'password'  => env('KONNEKTIVE_PASSWORD', 'konnekt'),
            'campaignId'    => env('KONNEKTIVE_CAMPAIGN_ID', '1'),
        ]
    ],


    /**
     * These transformers will be applied to the application models
     * so that we can use a standard approach
     */
    'transformers' => [
        /**
         * This transformer is applied to the Billable instance
         * and returns a populated Customer instance.
         */
        'customer'  => Bcismariu\Laravel\Payments\Transformers\Customer::class,
    ],

    /**
     * Parent class for the app Models
     */
    'models' => [
        'parent'    => Illuminate\Database\Eloquent\Model::class
    ],
];
