<?php

namespace App\Services;

use GuzzleHttp\Client;

class CepService
{
    protected $client;
    protected $baseApiUrl = "viacep.com.br/ws/";
    protected $apiReturnType = "json";

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function validaCep($cep)
    {
        $url = $this->baseApiUrl . $cep . "/" . $this->apiReturnType;
        try {
            $response = $this->client->get($url);

            $body = $response->getBody();
            $data = json_decode($body, true);

            if (isset($data['cep'])) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
