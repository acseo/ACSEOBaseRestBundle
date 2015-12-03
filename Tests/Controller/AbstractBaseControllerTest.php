<?php

namespace ACSEO\Bundle\BaseRestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractBaseControllerTest extends WebTestCase
{
    abstract protected function getUri();

    abstract protected function getDatasetCreate();

    abstract protected function getDatasetUpdate();

    public function testCGet()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUri());

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testPost()
    {
        $client = static::createClient();

        $data = $this->getDatasetCreate();

        $crawler = $client->request('POST', $this->getUri(), $data);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testPut()
    {
        $client = static::createClient();

        $data = $this->getDatasetUpdate();

        $crawler = $client->request('PUT', $this->getUri().'/'.$this->getLastId(), $data);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testGet()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUri().'/'.$this->getLastId());

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request('DELETE', $this->getUri().'/'.$this->getLastId());

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    protected function getLastId()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $this->getUri().'?_per_page=1&_sort=id&_sort_order=DESC');
        $coll = json_decode($client->getResponse()->getContent(), true);
        $item = $coll[0];

        return $item['id'];
    }
}
