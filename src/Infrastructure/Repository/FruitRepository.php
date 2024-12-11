<?php

namespace App\Infrastructure\Repository;

use App\Application\Adapter\DataGateway\FruitDataGatewayInterface;
use App\Domain\Model\Fruit as FruitModel;
use App\Domain\Model\FruitCollection;
use App\Infrastructure\Entity\Fruit as FruitEntity;
use App\Infrastructure\Mapper\EntityMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FruitEntity>
 */
class FruitRepository extends ServiceEntityRepository implements FruitDataGatewayInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityMapper $entityMapper,
    ) {
        parent::__construct($registry, FruitEntity::class);
    }

    public function batchSave(FruitCollection $fruitCollection): void
    {
        foreach ($fruitCollection->list() as $fruitModel) {
            $this->getEntityManager()->persist($this->entityMapper->mapToFruitEntity($fruitModel));
        }

        $this->getEntityManager()->flush();
    }

    public function save(FruitModel $fruitModel): void
    {
        $this->getEntityManager()->persist($this->entityMapper->mapToFruitEntity($fruitModel));
        $this->getEntityManager()->flush();
    }

    public function getFruitCollection(string $nameFilter): FruitCollection
    {
        if ('' !== $nameFilter) {
            $entities = $this->findBy(['name' => $nameFilter]);
        } else {
            $entities = $this->findAll();
        }

        return $this->entityMapper->mapToFruitCollection($entities);
    }

    public function getExistingIds(array $fruitIds): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f.id')
            ->where('f.id IN (:ids)')
            ->setParameter('ids', $fruitIds);

        $result = $qb->getQuery()->getResult();

        return array_map(fn ($item) => $item['id'], $result);
    }
}
