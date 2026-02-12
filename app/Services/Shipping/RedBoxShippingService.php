<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;

class RedBoxShippingService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.redbox.base_url');
        $this->token = config('services.redbox.token');
    }

    protected function request($method, $endpoint, $data = [])
    {

        return Http::withToken($this->token)
            ->{$method}("{$this->baseUrl}{$endpoint}", $data)
            ->json();
    }

    public function getPointsByCity($city_code, $type)
    {

        return $this->request('get', "/cities/$city_code/points", compact('city_code', 'type'));
    }

    public function getCitiesByCountry($country_code)
    {

        return $this->request('get', "/countries/{$country_code}/cities", compact('country_code'));
    }

    public function searchNearbyPoints($lat, $lng, $radius, $type)
    {
        return $this->request('get', '/points/search/nearby', compact('lat', 'lng', 'radius', 'type'));
    }

    public function getPointDetails($point_id)
    {
        return $this->request('get', "points/{$point_id}");
    }

    public function createShipment(array $payload)
    {
        return $this->request('post', '/shipments', $payload);
    }

    public function getShipmentDetails($id)
    {
        return $this->request('get', "/shipments/{$id}");
    }

    public function getShipmentActivities($id)
    {
        return $this->request('get', "/shipments/{$id}/activities");
    }

    public function cancelShipment($id)
    {
        return $this->request('post', "/shipments/{$id}/cancel");
    }

    public function getTrackingPage($id)
    {
        return $this->request('get', "/shipments/{$id}/tracking-page");
    }

    public function createPickupLocation(array $payload)
    {
        return $this->request('post', '/pickup-locations', $payload);
    }
    public function createPickupLocationRequest(array $payload)
    {

        return $this->request('post', '/pickup-requests', $payload);
    }
}
