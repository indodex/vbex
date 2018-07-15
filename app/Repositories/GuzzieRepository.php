<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use \Exception;

abstract class GuzzieRepository
{
	protected $client;

	protected function getCurlClient($uri)
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => $uri]);
    }

    public function sendPost($url, $params = [], $format = 'array')
    {
        $response = $this->client->request('POST', $url, ['form_params' => $params]);
        return json_decode($response->getBody(), true);
    }

    public function sendGet($url, $params = [], $format = 'array')
    {
        try {
            $response = $this->client->request('GET', $url, ['query' => $params]);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (Exception $e) {
            return null;
        }

    }
}