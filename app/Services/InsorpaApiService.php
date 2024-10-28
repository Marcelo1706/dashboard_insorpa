<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class InsorpaApiService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = env("API_URL");
        $this->username = env("API_USER");
        $this->password = env("API_PASSWORD");
    }

    public function authenticate()
    {
        $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->post($this->baseUrl . '/auth', [
                'username' => $this->username,
                'password' => $this->password
            ]);


        if($response->successful()){
            $token = $response->json('access_token');
            Cache::put("access_token", $token, now()->addMinutes(55));
            return $token;
        }
    }

    public function get($url)
    {
        $token = Cache::get("access_token") ?? $this->authenticate();
        $response = Http::withToken($token)->get($this->baseUrl . $url);

        return $response->json();
    }
}