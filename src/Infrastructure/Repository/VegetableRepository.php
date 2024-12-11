<?php

namespace App\Infrastructure\Repository;

use App\Application\Adapter\DataGateway\VegetableDataGatewayInterface;
use App\Domain\Model\Vegetable as VegetableModel;
use App\Domain\Model\VegetableCollection;
use App\Infrastructure\Entity\Vegetable as VegetableEntity;
use App\Infrastructure\Mapper\EntityMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VegetableEntity>
 */
class VegetableRepository extends ServiceEntityRepository implements VegetableDataGatewayInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityMapper $entityMapper,
    ) {
        parent::__construct($registry, VegetableEntity::class);
    }

    public function batchSave(VegetableCollection $vegetableCollection): void
    {
        foreach ($vegetableCollection->list() as $vegetableModel) {
            $this->getEntityManager()->persist($this->entityMapper->mapToVegetableEntity($vegetableModel));
        }

        $this->getEntityManager()->flush();
    }

    public function save(VegetableModel $vegetableModel): void
    {
        $this->getEntityManager()->persist($this->entityMapper->mapToVegetableEntity($vegetableModel));
        $this->getEntityManager()->flush();
    }

    public function getVegetableCollection(string $nameFilter): VegetableCollection
    {
        if ('' !== $nameFilter) {
            $entities = $this->findBy(['name' => $nameFilter]);
        } else {
            $entities = $this->findAll();
        }

        return $this->entityMapper->mapToVegetableCollection($entities);
    }

    public function getExistingIds(array $vegetableIds): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v.id')
            ->where('v.id IN (:ids)')
            ->setParameter('ids', $vegetableIds);

        $result = $qb->getQuery()->getResult();

        return array_map(fn ($item) => $item['id'], $result);
    }
}
