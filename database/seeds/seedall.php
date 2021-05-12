<?php

use Illuminate\Database\Seeder;

class seedall extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        DB::table('settings')->insert([
            'key' => 'ads',
            'value' => '{"enabled":"0","header_ads":null,"footer_ads":null}',
        ]);

        DB::table('settings')->insert([
            'key' => 'email',
            'value' => 'admin@ecom.me',
        ]);

        DB::table('settings')->insert([
            'key' => 'email_activation',
            'value' => '0',
        ]);

        DB::table('settings')->insert([
            'key' => 'logo',
            'value' => 'logo.png',
        ]);

        DB::table('settings')->insert([
            'key' => 'favicon',
            'value' => 'logo.png',
        ]);

        DB::table('settings')->insert([
            'key' => 'timezone',
            'value' => 'Africa/lagos',
        ]);

        DB::table('settings')->insert([
            'key' => 'registration',
            'value' => '1',
        ]);

        DB::table('settings')->insert([
            'key' => 'custom_home',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'privacy',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'terms',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'package_free',
            'value' => '{"id":"free","name":"Free","slug":"free","status":"1","price":{"month":"FREE","quarter":"FREE","annual":"FREE"},"settings":{"ads":true,"custom_branding":true,"statistics":true,"verified":true,"social":true,"custom_background":true,"domains":true,"google_analytics":true,"facebook_pixel":true,"blogs":true,"blogs_limits":true,"products_limit":"-1"},"domains":"\"1\""}',
        ]);
        
        DB::table('settings')->insert([
            'key' => 'package_trial',
            'value' => '{"id":"trial","name":"Trial","slug":"trial","status":"1","price":{"month":"FREE","quarter":"FREE","annual":"FREE","expiry":"7"},"settings":{"expiry":true,"ads":true,"custom_branding":true,"statistics":true,"verified":true,"social":true,"custom_background":true,"domains":true,"google_analytics":true,"facebook_pixel":true,"blogs":true,"blogs_limits":true,"products_limit":"-1","trial":true},"domains":"\"1\""}',
        ]);
        
        DB::table('settings')->insert([
            'key' => 'business',
            'value' => '{"enabled":"1","name":null,"address":null,"city":null,"county":null,"zip":null,"country":null,"email":null,"phone":null,"tax_type":null,"tax_id":null,"custom_key_one":null,"custom_value_one":null,"custom_key_two":null,"custom_value_two":null}',
        ]);
        
        DB::table('settings')->insert([
            'key' => 'captcha',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'social',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'custom_code',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'currency',
            'value' => '',
        ]);

        DB::table('settings')->insert([
            'key' => 'email_notify',
            'value' => '',
        ]);
        
        DB::table('settings')->insert([
            'key' => 'location',
            'value' => '8560 Magnolia Street Laredo, TX 78043',
        ]);

        DB::table('settings')->insert([
            'key' => 'contact',
            'value' => '',
        ]);
        
        DB::table('settings')->insert([
            'key' => 'payment_system',
            'value' => '1',
        ]);
    }
}
