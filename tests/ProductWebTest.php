<?php

namespace App\Tests;

use App\Domain\Model\Product\ProductTypeEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductWebTest extends WebTestCase
{

    private array $serverConfigs
        = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'  => 'application/json',
        ];

    public function testCreateProduct(): void {
        $client = static::createClient();
        $client->request(
            method    : Request::METHOD_POST,
            uri       : '/product/create',
            parameters: [
                            'type'     => ProductTypeEnum::pen->name,
                            'name'     => 'Pen',
                            'SKU'      => 'pen1',
                            'price'    => 12.55,
                            'quantity' => 10,
                        ],
            server    : $this->serverConfigs,
        );

        $this->assertTrue($client->getResponse()->getContent());
    }
    public function testValidationErrorSKUTaken(): void {
        $client = static::createClient();
        $client->request(
            method    : Request::METHOD_POST,
            uri       : '/product/create',
            parameters: [
                            'type'     => ProductTypeEnum::pen->value,
                            'name'     => 'Pen',
                            'SKU'      => 'sku1',
                            'price'    => 12.55,
                            'quantity' => 10,
                        ],
            server    : $this->serverConfigs,
        );

        $this->assertTrue($client->getResponse()->getContent());
    }

}
