<?php

namespace App\Tests;

use JsonException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class ProductWebTest extends WebTestCase
{

    private array $serverConfigs
        = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'  => 'application/json',
        ];

    /**
     * @throws JsonException
     */
    public function testCreateProduct(): void {
        $client = static::createClient();
        $client->request(
            method    : Request::METHOD_POST,
            uri       : '/product/create',
            parameters: [
                            'name'     => 'Pen',
                            'sku'      => 'pen1',
                            'price'    => 12.55,
                            'quantity' => 10,
                        ],
            server    : $this->serverConfigs,
        );

        $response = json_decode($client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Pen', $response->name);
        self::assertSame('pen1', $response->sku);
        self::assertSame(10, $response->quantity);
    }

    /**
     * @throws JsonException
     */
    #[Group('productInfo')]
    public function testProductList(): void {
        $client = static::createClient();
        $client->request(
            method: Request::METHOD_GET,
            uri   : '/product/list',
            server: $this->serverConfigs,
        );

        $response = json_decode(
            json       : $client->getResponse()->getContent(),
            associative: false,
            depth      : 512,
            flags      : JSON_THROW_ON_ERROR,
        );

        self::assertSame(expected: 'product 0', actual: $response[0]->name);
    }

    /**
     * @throws JsonException
     */
    #[Group('productInfo')]
    #[Depends('testProductList')]
    public function testProductInfo(): void {
        $client = static::createClient();
        // we have to query again because of DAMA package which wraps every test in DB transaction
        // this is not ideal but it works, there are better ways to solve double or more queries in one test though
        $client->request(
            method: Request::METHOD_GET,
            uri   : '/product/list',
            server: $this->serverConfigs,
        );

        $responseList = json_decode(
            json       : $client->getResponse()->getContent(),
            associative: false,
            depth      : 512,
            flags      : JSON_THROW_ON_ERROR,
        );
        $expectedID   = $responseList[0]->id;

        $client->request(
            method: Request::METHOD_GET,
            uri   : '/product/' . $expectedID,
            server: $this->serverConfigs,
        );

        $response = json_decode($client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);

        self::assertSame($expectedID, $response->id);
    }

}
