<?php

namespace Inventory\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ExternalApiService
{
    private $client;
    private $mouserApiKey;
    private $digikeyApiKey;
    private $newarkApiKey;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false // Solo para desarrollo
        ]);
        
        $this->mouserApiKey = $_ENV['MOUSER_API_KEY'] ?? '';
        $this->digikeyApiKey = $_ENV['DIGIKEY_API_KEY'] ?? '';
        $this->newarkApiKey = $_ENV['NEWARK_API_KEY'] ?? '';
    }

    public function searchProduct($provider, $query)
    {
        switch (strtolower($provider)) {
            case 'mouser':
                return $this->searchMouser($query);
            case 'digikey':
                return $this->searchDigiKey($query);
            case 'newark':
                return $this->searchNewark($query);
            default:
                throw new \Exception('Proveedor no soportado');
        }
    }

    private function searchMouser($query)
    {
        if (empty($this->mouserApiKey)) {
            throw new \Exception('API Key de Mouser no configurada');
        }

        try {
            $response = $this->client->get('https://api.mouser.com/api/v1/search/partnumber', [
                'query' => [
                    'apiKey' => $this->mouserApiKey,
                    'partNumber' => $query,
                    'limit' => 10
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['SearchResults']['Parts'])) {
                return $this->formatMouserResults($data['SearchResults']['Parts']);
            }
            
            return [];
        } catch (RequestException $e) {
            throw new \Exception('Error al consultar Mouser API: ' . $e->getMessage());
        }
    }

    private function searchDigiKey($query)
    {
        if (empty($this->digikeyApiKey)) {
            throw new \Exception('API Key de DigiKey no configurada');
        }

        try {
            $response = $this->client->get('https://api.digikey.com/products/v4/search', [
                'headers' => [
                    'X-DIGIKEY-Client-Id' => $this->digikeyApiKey,
                    'X-DIGIKEY-Client-Secret' => $_ENV['DIGIKEY_CLIENT_SECRET'] ?? '',
                    'Content-Type' => 'application/json'
                ],
                'query' => [
                    'keywords' => $query,
                    'limit' => 10
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['Products'])) {
                return $this->formatDigiKeyResults($data['Products']);
            }
            
            return [];
        } catch (RequestException $e) {
            throw new \Exception('Error al consultar DigiKey API: ' . $e->getMessage());
        }
    }

    private function searchNewark($query)
    {
        if (empty($this->newarkApiKey)) {
            throw new \Exception('API Key de Newark no configurada');
        }

        try {
            $response = $this->client->get('https://api.newark.com/api/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->newarkApiKey,
                    'Content-Type' => 'application/json'
                ],
                'query' => [
                    'q' => $query,
                    'limit' => 10
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['products'])) {
                return $this->formatNewarkResults($data['products']);
            }
            
            return [];
        } catch (RequestException $e) {
            throw new \Exception('Error al consultar Newark API: ' . $e->getMessage());
        }
    }

    private function formatMouserResults($parts)
    {
        $formatted = [];
        
        foreach ($parts as $part) {
            $formatted[] = [
                'provider' => 'mouser',
                'part_number' => $part['MouserPartNumber'] ?? '',
                'manufacturer_part_number' => $part['ManufacturerPartNumber'] ?? '',
                'manufacturer' => $part['Manufacturer'] ?? '',
                'description' => $part['Description'] ?? '',
                'price' => $part['PriceBreaks'][0]['Price'] ?? 0,
                'currency' => $part['PriceBreaks'][0]['Currency'] ?? 'USD',
                'stock' => $part['Availability'] ?? 0,
                'datasheet_url' => $part['DataSheetUrl'] ?? '',
                'image_url' => $part['ImagePath'] ?? '',
                'category' => $part['Category'] ?? '',
                'specifications' => $part['ProductAttributes'] ?? []
            ];
        }
        
        return $formatted;
    }

    private function formatDigiKeyResults($products)
    {
        $formatted = [];
        
        foreach ($products as $product) {
            $formatted[] = [
                'provider' => 'digikey',
                'part_number' => $product['DigiKeyPartNumber'] ?? '',
                'manufacturer_part_number' => $product['ManufacturerPartNumber'] ?? '',
                'manufacturer' => $product['Manufacturer'] ?? '',
                'description' => $product['ProductDescription'] ?? '',
                'price' => $product['UnitPrice'] ?? 0,
                'currency' => 'USD',
                'stock' => $product['QuantityAvailable'] ?? 0,
                'datasheet_url' => $product['DataSheetUrl'] ?? '',
                'image_url' => $product['PrimaryPhoto'] ?? '',
                'category' => $product['ProductCategory'] ?? '',
                'specifications' => $product['Parameters'] ?? []
            ];
        }
        
        return $formatted;
    }

    private function formatNewarkResults($products)
    {
        $formatted = [];
        
        foreach ($products as $product) {
            $formatted[] = [
                'provider' => 'newark',
                'part_number' => $product['partNumber'] ?? '',
                'manufacturer_part_number' => $product['manufacturerPartNumber'] ?? '',
                'manufacturer' => $product['manufacturer'] ?? '',
                'description' => $product['description'] ?? '',
                'price' => $product['price'] ?? 0,
                'currency' => $product['currency'] ?? 'USD',
                'stock' => $product['stock'] ?? 0,
                'datasheet_url' => $product['datasheetUrl'] ?? '',
                'image_url' => $product['imageUrl'] ?? '',
                'category' => $product['category'] ?? '',
                'specifications' => $product['specifications'] ?? []
            ];
        }
        
        return $formatted;
    }

    public function syncProduct($provider, $sku)
    {
        $results = $this->searchProduct($provider, $sku);
        
        if (empty($results)) {
            throw new \Exception('Producto no encontrado en ' . $provider);
        }
        
        $product = $results[0]; // Tomar el primer resultado
        
        // Actualizar producto local con datos del proveedor
        $productModel = new \Inventory\Models\Product();
        $existingProduct = $productModel->getBySku($sku);
        
        if ($existingProduct) {
            $updateData = [
                'name' => $product['description'],
                'price' => $product['price'],
                'specifications' => json_encode($product['specifications']),
                'image_url' => $product['image_url']
            ];
            
            $productModel->update($existingProduct['id'], $updateData);
            
            return [
                'action' => 'updated',
                'product_id' => $existingProduct['id'],
                'data' => $product
            ];
        } else {
            // Crear nuevo producto
            $newProductData = [
                'name' => $product['description'],
                'sku' => $sku,
                'description' => $product['description'],
                'price' => $product['price'],
                'cost' => $product['price'] * 0.7, // Asumir 30% de margen
                'stock_quantity' => 0,
                'min_stock_level' => 10,
                'specifications' => json_encode($product['specifications']),
                'image_url' => $product['image_url']
            ];
            
            $productId = $productModel->create($newProductData);
            
            return [
                'action' => 'created',
                'product_id' => $productId,
                'data' => $product
            ];
        }
    }

    public function getPriceComparison($sku)
    {
        $providers = ['mouser', 'digikey', 'newark'];
        $results = [];
        
        foreach ($providers as $provider) {
            try {
                $products = $this->searchProduct($provider, $sku);
                if (!empty($products)) {
                    $results[$provider] = $products[0];
                }
            } catch (\Exception $e) {
                $results[$provider] = ['error' => $e->getMessage()];
            }
        }
        
        return $results;
    }
}
