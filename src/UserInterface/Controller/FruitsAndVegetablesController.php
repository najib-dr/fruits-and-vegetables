<?php

namespace App\UserInterface\Controller;

use App\Application\Dto\ItemDto;
use App\Application\Dto\QueryParamsDto;
use App\Application\Reader\FileReader;
use App\Application\Service\StorageService;
use App\Domain\Enum\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

class FruitsAndVegetablesController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly StorageService $storageService,
        private readonly FileReader $fileReader,
    ) {
    }

    #[Route(path: '/fruits-and-vegetables/process', methods: ['POST'])]
    #[OA\Response(response: 201, description: 'Processes request.json file and stores items')]
    #[OA\Response(response: 400, description: 'Invalid input data')]
    #[OA\Response(response: 409, description: 'Items with the same ID already exist')]
    public function process(): JsonResponse
    {
        $jsonPayload = $this->fileReader->read(__DIR__.'/../../../request.json');

        /** @var ItemDto[] $items */
        $items = $this->serializer->deserialize($jsonPayload, ItemDto::class.'[]', 'json');

        $errors = $this->validator->validate($items);

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $existingIds = $this->storageService->existingIds($items);

        if (count($existingIds) > 0) {
            return $this->json(
                ['error' => 'Items with the following IDs already exist: '.implode(', ', $existingIds)],
                Response::HTTP_CONFLICT,
            );
        }

        $this->storageService->storeItems($items);

        return $this->json(['message' => 'created'], Response::HTTP_CREATED);
    }

    #[Route(path: '/fruits-and-vegetables', methods: [Request::METHOD_GET])]
    #[OA\Response(response: 200, description: 'Returns list of fruits and vegetables')]
    #[OA\Parameter(name: 'type', description: 'Type of item (fruit or vegetable)', in: 'query', required: false)]
    #[OA\Parameter(name: 'name', description: 'Name of item', in: 'query', required: false)]
    #[OA\Parameter(name: 'unit', description: 'Unit (g or kg)', in: 'query', required: false)]
    public function list(Request $request): JsonResponse
    {
        $type = $request->query->get('type');

        $queryParams = new QueryParamsDto(
            name: (string) $request->query->get('name'),
            unit: (string) $request->query->get('unit'),
        );

        $responseData = [];

        if ($type === Type::Fruit->value) {
            $responseData['fruits'] = $this->storageService->getFruitCollection($queryParams)->list();
        } elseif ($type === Type::Vegetable->value) {
            $responseData['vegetables'] = $this->storageService->getVegetableCollection($queryParams)->list();
        } elseif (null === $type) {
            $responseData = [
                'fruits' => $this->storageService->getFruitCollection($queryParams)->list(),
                'vegetables' => $this->storageService->getVegetableCollection($queryParams)->list(),
            ];
        }

        return $this->json($responseData);
    }

    #[Route(path: '/fruits-and-vegetables', methods: [Request::METHOD_POST],)]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: ItemDto::class,
            example: '{"id": 25, "name": "Avocado", "type": "fruit", "quantity": 5000, "unit": "g"}',
        ),
    )]
    #[OA\Response(response: 201, description: 'Item created')]
    #[OA\Response(response: 400, description: 'Invalid input data')]
    #[OA\Response(response: 409, description: 'Item with the same ID already exists')]
    public function add(Request $request): JsonResponse
    {
        try {
            $item = $this->serializer->deserialize($request->getContent(), ItemDto::class, 'json');
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Invalid input data. Please ensure all required fields are present with valid type'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $errors = $this->validator->validate($item);

        if (count($errors) > 0) {
            return $this->json(['errors' => $this->formatErrorMessages($errors)], Response::HTTP_BAD_REQUEST);
        }

        if ($this->storageService->itemExists($item)) {
            return $this->json(['error' => 'Item with the same ID already exists'], Response::HTTP_CONFLICT);
        }

        $this->storageService->storeItem($item);

        return $this->json('created');
    }

    /**
     * @return array<string, string>
     */
    private function formatErrorMessages(ConstraintViolationListInterface $errors): array
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $property = $error->getPropertyPath();
            $message = $error->getMessage();
            $errorMessages[$property] = $message;
        }

        return $errorMessages;
    }
}
