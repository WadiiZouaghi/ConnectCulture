<?php

namespace App\Tests\Controller;

use App\Entity\Evants;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EvantsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $evantRepository;
    private string $path = '/evants/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->evantRepository = $this->manager->getRepository(Evants::class);

        foreach ($this->evantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Evant index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'evant[name]' => 'Testing',
            'evant[date]' => 'Testing',
            'evant[eventType]' => 'Testing',
            'evant[destination]' => 'Testing',
            'evant[images]' => 'Testing',
            'evant[panniers]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->evantRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evants();
        $fixture->setName('My Title');
        $fixture->setDate('My Title');
        $fixture->setEventType('My Title');
        $fixture->setDestination('My Title');
        $fixture->setImages('My Title');
        $fixture->setPanniers('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Evant');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evants();
        $fixture->setName('Value');
        $fixture->setDate('Value');
        $fixture->setEventType('Value');
        $fixture->setDestination('Value');
        $fixture->setImages('Value');
        $fixture->setPanniers('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'evant[name]' => 'Something New',
            'evant[date]' => 'Something New',
            'evant[eventType]' => 'Something New',
            'evant[destination]' => 'Something New',
            'evant[images]' => 'Something New',
            'evant[panniers]' => 'Something New',
        ]);

        self::assertResponseRedirects('/evants/');

        $fixture = $this->evantRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDate());
        self::assertSame('Something New', $fixture[0]->getEventType());
        self::assertSame('Something New', $fixture[0]->getDestination());
        self::assertSame('Something New', $fixture[0]->getImages());
        self::assertSame('Something New', $fixture[0]->getPanniers());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Evants();
        $fixture->setName('Value');
        $fixture->setDate('Value');
        $fixture->setEventType('Value');
        $fixture->setDestination('Value');
        $fixture->setImages('Value');
        $fixture->setPanniers('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/evants/');
        self::assertSame(0, $this->evantRepository->count([]));
    }
}
