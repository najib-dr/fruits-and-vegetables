<?php

namespace App\Tests\UserInterface\Controller;

use App\Application\Dto\ItemDto;
use App\Application\Dto\QueryParamsDto;
use App\Application\Reader\FileReader;
use App\Application\Service\StorageService;
use App\Domain\Enum\Type;
use App\Domain\Model\FruitCollection;
use App\Domain\Model\VegetableCollection;
use App\Tests\Builder\FruitModelBuilder;
use App\Tests\Builder\VegetableModelBuilder;
use App\UserInterface\Controller\FruitsAndVegetablesController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FruitsAndVegetablesControllerTest extends TestCase
{
    private FruitsAndVegetablesController $unit;
    private SerializerInterface|MockObject $serializer;
    private ValidatorInterface|MockObject $validator;
    private StorageService|MockObject $storageService;
    private FileReader|MockObject $fileReader;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->storageService = $this->createMock(StorageService::class);
        $this->fileReader = $this->createMock(FileReader::class);
        $container = $this->createMock(ContainerInterface::class);

        $this->unit = new FruitsAndVegetablesController(
            $this->serializer,
            $this->validator,
            $this->storageService,
            $this->fileReader,
        );

        $this->unit->setContainer($container);
    }

    public function testProcessWithValidItems(): void
    {
        // arrange
        $jsonPayload = '[{"id":1,"name":"Apple","type":"fruit","quantity":1000,"unit":"gram"}]';
        $items = [new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram')];
        $errors = new ConstraintViolationList();

        $this->fileReader
            ->expects($this->once())
            ->method('read')
            ->with($this->stringContains('request.json'))
            ->willReturn($jsonPayload);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonPayload, ItemDto::class.'[]', 'json')
            ->willReturn($items);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($items)
            ->willReturn($errors);

        $this->storageService
            ->expects($this->once())
            ->method('existingIds')
            ->with($items)
            ->willReturn([]);

        $this->storageService
            ->expects($this->once())
            ->method('storeItems')
            ->with($items);

        // act
        $response = $this->unit->process();

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertStringContainsString('created', $response->getContent());
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testProcessWithExistingItems(): void
    {
        // arrange
        $jsonPayload = '[{"id":1,"name":"Apple","type":"fruit","quantity":1000,"unit":"gram"}]';
        $items = [new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram')];
        $errors = new ConstraintViolationList();
        $existingIds = [1];

        $this->fileReader
            ->expects($this->once())
            ->method('read')
            ->with($this->stringContains('request.json'))
            ->willReturn($jsonPayload);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonPayload, ItemDto::class.'[]', 'json')
            ->willReturn($items);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($items)
            ->willReturn($errors);

        $this->storageService
            ->expects($this->once())
            ->method('existingIds')
            ->with($items)
            ->willReturn($existingIds);

        // act
        $response = $this->unit->process();

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertStringContainsString('Items with the following IDs already exist: 1', $response->getContent());
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
    }

    public function testProcessWithValidationErrors(): void
    {
        // arrange
        $jsonPayload = '[{"id":1,"name":"Apple","type":"fruit","quantity":1000,"unit":"gram"}]';
        $items = [new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram')];
        $errors = $this->createMock(ConstraintViolationList::class);
        $errors->method('count')->willReturn(1);

        $this->fileReader
            ->expects($this->once())
            ->method('read')
            ->with($this->stringContains('request.json'))
            ->willReturn($jsonPayload);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonPayload, ItemDto::class.'[]', 'json')
            ->willReturn($items);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($items)
            ->willReturn($errors);

        // act
        $response = $this->unit->process();

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testListWithFruits(): void
    {
        // arrange
        $request = new Request(['type' => Type::Fruit->value, 'name' => 'Apple', 'unit' => 'kg']);
        $queryParams = new QueryParamsDto('Apple', 'kg');

        $fruitCollection = new FruitCollection();
        $fruitCollection->add(FruitModelBuilder::create()->build());

        $this->storageService
            ->expects($this->once())
            ->method('getFruitCollection')
            ->with($queryParams)
            ->willReturn($fruitCollection);

        // act
        $response = $this->unit->list($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('fruits', json_decode($response->getContent(), true));
        $this->assertArrayNotHasKey('vegetables', json_decode($response->getContent(), true));
    }

    public function testListWithVegetables(): void
    {
        // arrange
        $request = new Request(['type' => Type::Vegetable->value]);
        $queryParams = new QueryParamsDto('', '');

        $vegetableCollection = new VegetableCollection();
        $vegetableCollection->add(VegetableModelBuilder::create()->build());

        $this->storageService
            ->expects($this->once())
            ->method('getVegetableCollection')
            ->with($queryParams)
            ->willReturn($vegetableCollection);

        // act
        $response = $this->unit->list($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('vegetables', json_decode($response->getContent(), true));
        $this->assertArrayNotHasKey('fruits', json_decode($response->getContent(), true));
    }

    public function testListWithFruitsAndVegetables(): void
    {
        // arrange
        $request = new Request();
        $queryParams = new QueryParamsDto('', '');

        $fruitCollection = new FruitCollection();
        $fruitCollection->add(FruitModelBuilder::create()->build());

        $vegetableCollection = new VegetableCollection();
        $vegetableCollection->add(VegetableModelBuilder::create()->build());

        $this->storageService
            ->expects($this->once())
            ->method('getFruitCollection')
            ->with($queryParams)
            ->willReturn($fruitCollection);

        $this->storageService
            ->expects($this->once())
            ->method('getVegetableCollection')
            ->with($queryParams)
            ->willReturn($vegetableCollection);

        // act
        $response = $this->unit->list($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('fruits', json_decode($response->getContent(), true));
        $this->assertArrayHasKey('vegetables', json_decode($response->getContent(), true));
    }

    public function testListWithUnknownType(): void
    {
        // arrange
        $request = new Request(['type' => 'unknown']);

        // act
        $response = $this->unit->list($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEmpty(json_decode($response->getContent(), true));
    }

    public function testAddWithValidInput(): void
    {
        // arrange
        $itemDto = new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram');
        $request = new Request([], [], [], [], [], [], json_encode($itemDto));
        $errors = new ConstraintViolationList();

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($request->getContent(), ItemDto::class, 'json')
            ->willReturn($itemDto);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($itemDto)
            ->willReturn($errors);

        $this->storageService
            ->expects($this->once())
            ->method('itemExists')
            ->with($itemDto)
            ->willReturn(false);

        $this->storageService
            ->expects($this->once())
            ->method('storeItem')
            ->with($itemDto);

        // act
        $response = $this->unit->add($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertStringContainsString('created', $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAddWithInvalidInput(): void
    {
        // arrange
        $request = new Request([], [], [], [], [], [], 'invalid json');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($request->getContent(), ItemDto::class, 'json')
            ->willThrowException(new \Exception());

        // act
        $response = $this->unit->add($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertStringContainsString('Invalid input data', $response->getContent());
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testAddWithValidationErrors(): void
    {
        // arrange
        $itemDto = new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram');
        $request = new Request([], [], [], [], [], [], json_encode($itemDto));
        $errors = $this->createMock(ConstraintViolationList::class);
        $errors->method('count')->willReturn(1);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($request->getContent(), ItemDto::class, 'json')
            ->willReturn($itemDto);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($itemDto)
            ->willReturn($errors);

        // act
        $response = $this->unit->add($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testAddWithExistingItem(): void
    {
        // arrange
        $itemDto = new ItemDto(1, 'Apple', Type::Fruit->value, 1000, 'gram');
        $request = new Request([], [], [], [], [], [], json_encode($itemDto));
        $errors = new ConstraintViolationList();

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($request->getContent(), ItemDto::class, 'json')
            ->willReturn($itemDto);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($itemDto)
            ->willReturn($errors);

        $this->storageService
            ->expects($this->once())
            ->method('itemExists')
            ->with($itemDto)
            ->willReturn(true);

        // act
        $response = $this->unit->add($request);

        // assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertStringContainsString('Item with the same ID already exists', $response->getContent());
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
    }
}
