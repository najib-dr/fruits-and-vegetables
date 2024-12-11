<?php

namespace App\Tests\Application\Service;

use App\Application\Adapter\DataGateway\FruitDataGatewayInterface;
use App\Application\Adapter\DataGateway\VegetableDataGatewayInterface;
use App\Application\Dto\ItemDto;
use App\Application\Dto\QueryParamsDto;
use App\Application\Mapper\ModelMapper;
use App\Application\Service\StorageService;
use App\Domain\Enum\Type;
use App\Domain\Enum\Unit;
use App\Domain\Model\FruitCollection;
use App\Domain\Model\VegetableCollection;
use App\Tests\Builder\FruitModelBuilder;
use App\Tests\Builder\ItemDtoBuilder;
use App\Tests\Builder\VegetableModelBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageServiceTest extends TestCase
{
    private StorageService $unit;
    private FruitDataGatewayInterface|MockObject $fruitDataGateway;
    private VegetableDataGatewayInterface|MockObject $vegetableDataGateway;
    private ModelMapper|MockObject $mapper;

    protected function setUp(): void
    {
        $this->fruitDataGateway = $this->createMock(FruitDataGatewayInterface::class);
        $this->vegetableDataGateway = $this->createMock(VegetableDataGatewayInterface::class);
        $this->mapper = $this->createMock(ModelMapper::class);

        $this->unit = new StorageService(
            $this->fruitDataGateway,
            $this->vegetableDataGateway,
            $this->mapper,
        );
    }

    public function testStoreItems(): void
    {
        // Arrange & Assert
        $fruitDto = ItemDtoBuilder::create()->withId(1)->withType(Type::Fruit->value)->build();
        $vegetableDto = ItemDtoBuilder::create()->withId(2)->withType(Type::Vegetable->value)->build();

        $fruit = FruitModelBuilder::create()->withId(1)->build();
        $vegetable = VegetableModelBuilder::create()->withId(2)->build();

        $this->mapper
            ->method('mapToFruit')
            ->willReturn($fruit);

        $this->mapper
            ->method('mapToVegetable')
            ->willReturn($vegetable);

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('batchSave')
            ->with($this->callback(function (FruitCollection $collection) use ($fruit) {
                return 1 === $collection->count() && $collection->list()[0] === $fruit;
            }));

        $this->vegetableDataGateway
            ->expects($this->once())
            ->method('batchSave')
            ->with($this->callback(function (VegetableCollection $collection) use ($vegetable) {
                return 1 === $collection->count() && $collection->list()[0] === $vegetable;
            }));

        // Act
        $this->unit->storeItems([$fruitDto, $vegetableDto]);
    }

    public function testStoreItemsWithUnsupportedType(): void
    {
        // Arrange
        $unsupportedDto = ItemDtoBuilder::create()->withType('unsupported')->build();

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported type.');

        // Act
        $this->unit->storeItems([$unsupportedDto]);
    }

    public function testStoreItemWithFruit(): void
    {
        // Arrange & Assert
        $fruitDto = ItemDtoBuilder::create()->withType(Type::Fruit->value)->build();
        $fruit = FruitModelBuilder::create()->build();

        $this->mapper
            ->method('mapToFruit')
            ->willReturn($fruit);

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('save')
            ->with($fruit);

        // Act
        $this->unit->storeItem($fruitDto);
    }

    public function testStoreItemWithVegetable(): void
    {
        // Arrange & Assert
        $vegetableDto = ItemDtoBuilder::create()->withType(Type::Vegetable->value)->build();
        $vegetable = VegetableModelBuilder::create()->build();

        $this->mapper
            ->method('mapToVegetable')
            ->willReturn($vegetable);

        $this->vegetableDataGateway
            ->expects($this->once())
            ->method('save')
            ->with($vegetable);

        // Act
        $this->unit->storeItem($vegetableDto);
    }

    public function testStoreItemWithUnsupportedType(): void
    {
        // Arrange
        $unsupportedDto = ItemDtoBuilder::create()->withType('unsupported')->build();

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported type.');

        // Act
        $this->unit->storeItem($unsupportedDto);
    }

    public function testExistingIdsWithFruits(): void
    {
        // arrange
        $fruitDto1 = ItemDtoBuilder::create()->withId(1)->withType(Type::Fruit->value)->build();
        $fruitDto2 = ItemDtoBuilder::create()->withId(2)->withType(Type::Fruit->value)->build();
        $existingIds = [1];

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('getExistingIds')
            ->with([1, 2])
            ->willReturn($existingIds);

        $this->vegetableDataGateway
            ->expects($this->never())
            ->method('getExistingIds');

        // act
        $result = $this->unit->existingIds([$fruitDto1, $fruitDto2]);

        // assert
        $this->assertEquals($existingIds, $result);
    }

    public function testExistingIdsWithVegetables(): void
    {
        // arrange
        $vegetableDto1 = ItemDtoBuilder::create()->withId(1)->withType(Type::Vegetable->value)->build();
        $vegetableDto2 = ItemDtoBuilder::create()->withId(2)->withType(Type::Vegetable->value)->build();
        $existingIds = [1];

        $this->vegetableDataGateway
            ->expects($this->once())
            ->method('getExistingIds')
            ->with([1, 2])
            ->willReturn($existingIds);

        $this->fruitDataGateway
            ->expects($this->never())
            ->method('getExistingIds');

        // act
        $result = $this->unit->existingIds([$vegetableDto1, $vegetableDto2]);

        // assert
        $this->assertEquals($existingIds, $result);
    }

    public function testExistingIdsWithMixedTypes(): void
    {
        // arrange
        $fruitDto = ItemDtoBuilder::create()->withId(1)->withType(Type::Fruit->value)->build();
        $vegetableDto = ItemDtoBuilder::create()->withId(2)->withType(Type::Vegetable->value)->build();
        $existingFruitIds = [1];
        $existingVegetableIds = [2];

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('getExistingIds')
            ->with([1])
            ->willReturn($existingFruitIds);

        $this->vegetableDataGateway
            ->expects($this->once())
            ->method('getExistingIds')
            ->with([2])
            ->willReturn($existingVegetableIds);

        // act
        $result = $this->unit->existingIds([$fruitDto, $vegetableDto]);

        // assert
        $this->assertEquals(array_merge($existingFruitIds, $existingVegetableIds), $result);
    }

    public function testExistingIdsWithUnsupportedType(): void
    {
        // arrange
        $unsupportedDto = ItemDtoBuilder::create()->withType('unsupported')->build();

        // assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported type.');

        // act
        $this->unit->existingIds([$unsupportedDto]);
    }

    /**
     * @dataProvider itemExistsDataProvider
     *
     * @param int[] $existingIds
     * @param int[] $gatewayReturn
     */
    public function testItemExists(
        ItemDto $itemDto,
        array $existingIds,
        bool $expectedResult,
        ?string $gatewayMethod,
        ?array $gatewayReturn,
    ): void {
        // Arrange
        if ('fruit' === $itemDto->type) {
            $this->fruitDataGateway
                ->expects($this->once())
                ->method($gatewayMethod)
                ->with([$itemDto->id])
                ->willReturn($gatewayReturn);
        } elseif ('vegetable' === $itemDto->type) {
            $this->vegetableDataGateway
                ->expects($this->once())
                ->method($gatewayMethod)
                ->with([$itemDto->id])
                ->willReturn($gatewayReturn);
        } elseif ('unsupported' === $itemDto->type) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Unsupported type.');
        }

        // Act
        $result = $this->unit->itemExists($itemDto);

        // Assert
        if ('unsupported' !== $itemDto->type) {
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * @return iterable<string, array{itemDto: ItemDto, existingIds: int[], expectedResult: bool, gatewayMethod: ?string, gatewayReturn: ?int[]}>
     */
    public function itemExistsDataProvider(): iterable
    {
        yield 'existing fruit' => [
            'itemDto' => ItemDtoBuilder::create()->withId(1)->withType(Type::Fruit->value)->build(),
            'existingIds' => [1],
            'expectedResult' => true,
            'gatewayMethod' => 'getExistingIds',
            'gatewayReturn' => [1],
        ];

        yield 'existing vegetable' => [
            'itemDto' => ItemDtoBuilder::create()->withId(1)->withType(Type::Vegetable->value)->build(),
            'existingIds' => [1],
            'expectedResult' => true,
            'gatewayMethod' => 'getExistingIds',
            'gatewayReturn' => [1],
        ];

        yield 'non-existing fruit' => [
            'itemDto' => ItemDtoBuilder::create()->withId(1)->withType(Type::Fruit->value)->build(),
            'existingIds' => [],
            'expectedResult' => false,
            'gatewayMethod' => 'getExistingIds',
            'gatewayReturn' => [],
        ];

        yield 'non-existing vegetable' => [
            'itemDto' => ItemDtoBuilder::create()->withId(1)->withType(Type::Vegetable->value)->build(),
            'existingIds' => [],
            'expectedResult' => false,
            'gatewayMethod' => 'getExistingIds',
            'gatewayReturn' => [],
        ];

        yield 'unsupported type' => [
            'itemDto' => ItemDtoBuilder::create()->withType('unsupported')->build(),
            'existingIds' => [],
            'expectedResult' => false,
            'gatewayMethod' => null,
            'gatewayReturn' => null,
        ];
    }

    public function testGetFruitCollectionWithDefaultUnit(): void
    {
        // arrange
        $queryParamsDto = new QueryParamsDto(name: '', unit: Unit::Gram->value);
        $fruitCollection = new FruitCollection();
        $fruit = FruitModelBuilder::create()->build();
        $fruitCollection->add($fruit);

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('getFruitCollection')
            ->with(null)
            ->willReturn($fruitCollection);

        // act
        $result = $this->unit->getFruitCollection($queryParamsDto);

        // assert
        $this->assertSame($fruitCollection, $result);
    }

    public function testGetFruitCollectionWithKilogramUnit(): void
    {
        // arrange
        $queryParamsDto = new QueryParamsDto(name: 'Apple', unit: Unit::Kilogram->value);
        $fruitCollection = new FruitCollection();
        $fruit = FruitModelBuilder::create()->withName('Apple')->withQuantity(2000)->build();
        $fruitCollection->add($fruit);

        $this->fruitDataGateway
            ->expects($this->once())
            ->method('getFruitCollection')
            ->with('Apple')
            ->willReturn($fruitCollection);

        // act
        $result = $this->unit->getFruitCollection($queryParamsDto);

        // assert
        $this->assertSame($fruitCollection, $result);
        foreach ($result->list() as $fruit) {
            $this->assertSame(Unit::Kilogram->value, $fruit->getUnit()->value);
            $this->assertSame(2.0, $fruit->getQuantity());
        }
    }
}
