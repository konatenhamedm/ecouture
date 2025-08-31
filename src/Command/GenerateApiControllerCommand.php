<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:knh-crud',
    description: 'Génère des contrôleurs API CRUD complets avec documentation OpenAPI'
)]
class GenerateApiControllerCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $fs = new Filesystem()
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('entities', InputArgument::IS_ARRAY, 'Noms des entités (ex: Product ou App\Entity\Product)')
            ->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'Groupe de sérialisation', 'default')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'écrasement des fichiers existants')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Générer pour toutes les entités')
            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Répertoire de sortie', 'src/Controller/Apis/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérification des dépendances
        /*  if (!class_exists('Nelmio\ApiDocBundle\Annotation\Model')) {
            $io->error('Le bundle NelmioApiDocBundle n\'est pas installé. Exécutez: composer require nelmio/api-doc-bundle');
            return Command::FAILURE;
        } */

        $entities = $this->resolveEntities($input, $io);

        if (empty($entities)) {
            $io->error('Aucune entité valide spécifiée');
            return Command::FAILURE;
        }

        foreach ($entities as $entityClass) {
            $this->generateController($entityClass, $input, $io);
        }

        $io->success('Génération terminée!');
        return Command::SUCCESS;
    }

    private function resolveEntities(InputInterface $input, SymfonyStyle $io): array
    {
        if ($input->getOption('all')) {
            return $this->getAllEntities();
        }

        $entities = [];
        foreach ($input->getArgument('entities') as $entityName) {
            $entityClass = $this->resolveEntityClass($entityName, $io);
            if ($entityClass) {
                $entities[] = $entityClass;
            }
        }

        return $entities;
    }

    private function resolveEntityClass(string $name, SymfonyStyle $io): ?string
    {
        if (class_exists($name)) {
            return $name;
        }

        if (!str_contains($name, '\\')) {
            $fullName = 'App\\Entity\\' . $name;
            if (class_exists($fullName)) {
                return $fullName;
            }
        }

        $matches = $this->findMatchingEntities($name);
        if (count($matches) === 1) {
            return $matches[0];
        }

        if (count($matches) > 1) {
            return $io->choice("Plusieurs entités correspondent à '$name'", $matches);
        }

        $io->warning("Aucune entité trouvée pour '$name'");
        return null;
    }

    private function findMatchingEntities(string $partialName): array
    {
        return array_filter($this->getAllEntities(), fn($e) => stripos($e, $partialName) !== false);
    }

    private function getAllEntities(): array
    {
        $entities = [];
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
            if (str_starts_with($meta->getName(), 'App\\Entity\\')) {
                $entities[] = $meta->getName();
            }
        }
        sort($entities);
        return $entities;
    }

    private function generateController(string $entityClass, InputInterface $input, SymfonyStyle $io): void
    {
        $metadata = $this->em->getClassMetadata($entityClass);
        $entityName = basename(str_replace('\\', '/', $entityClass));
        $entityVar = lcfirst($entityName);
        $routeName = $this->toRouteName($entityName);
        $group = $input->getOption('group');
        $outputDir = rtrim($input->getOption('dir'), '/') . '/';

        $fields = $this->getEntityFields($metadata);
        $controllerContent = $this->buildControllerContent(
            $entityName,
            $entityClass,
            $routeName,
            $entityVar,
            $group,
            $fields
        );

        $this->fs->mkdir($outputDir);
        $filePath = $outputDir . "{$entityName}Controller.php";

        if ($input->getOption('force') || !$this->fs->exists($filePath)) {
            $this->fs->dumpFile($filePath, $controllerContent);
            $io->text("Généré: $filePath");
        } else {
            $io->note("Existe déjà: $filePath (utilisez --force pour écraser)");
        }
    }

    private function getEntityFields(ClassMetadata $metadata): array
    {
        $fields = [];

        foreach ($metadata->fieldMappings as $mapping) {
            if ($mapping['fieldName'] !== 'id') {
                $fields[$mapping['fieldName']] = [
                    'type' => $mapping['type'],
                    'nullable' => $mapping['nullable'] ?? false
                ];
            }
        }

        foreach ($metadata->associationMappings as $mapping) {
            if ($mapping['type'] === ClassMetadata::MANY_TO_ONE) {
                $fields[$mapping['fieldName']] = [
                    'type' => 'association',
                    'targetEntity' => $mapping['targetEntity']
                ];
            }
        }

        return $fields;
    }

    private function buildControllerContent(
        string $entityName,
        string $entityClass,
        string $routeName,
        string $entityVar,
        string $group,
        array $fields
    ): string {
        $settersCreate = $this->generateSetters($fields, $entityVar, 'create');
        $settersUpdate = $this->generateSetters($fields, $entityVar, 'update');
        $propertiesDoc = $this->generatePropertiesDoc($fields);
        $filterParams = $this->generateFilterParams($fields);

        return <<<PHP
<?php

namespace App\Controller\Apis;

use $entityClass;
use App\Repository\\{$entityName}Repository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Attribute\Model as AttributeModel;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/$routeName', name: 'api_{$routeName}_')]
class {$entityName}Controller extends AbstractController
{
    public function __construct(
        private EntityManagerInterface \$em,
        private SerializerInterface \$serializer,
        private ValidatorInterface \$validator
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[OA\Get(
        summary: 'Liste les {$routeName}',
        tags: ['$entityName']
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Numéro de page',
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Nombre d\'éléments par page',
        schema: new OA\Schema(type: 'integer', default: 20)
    )]
    $filterParams
    #[OA\Response(
        response: 200,
        description: 'Liste des {$routeName}',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new AttributeModel(type: {$entityName}::class, groups: ['$group']))
        )
    )]
    public function index(Request \$request, {$entityName}Repository \$repository): JsonResponse
    {
        \$page = \$request->query->getInt('page', 1);
        \$limit = \$request->query->getInt('limit', 20);
        \$filters = \$request->query->all();

        \$results = \$repository->findByFilters(\$filters, \$page, \$limit);
        
        return \$this->json(
            \$results,
            Response::HTTP_OK,
            [],
            ['groups' => ['$group']]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Affiche un {$routeName}',
        tags: ['$entityName']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID du {$routeName}',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Détails du {$routeName}',
        content: new OA\JsonContent(ref: new AttributeModel(type: {$entityName}::class, groups: ['$group']))
    )]
    #[OA\Response(response: 404, description: 'Ressource non trouvée')]
    public function show(?{$entityName} \${$entityVar}): JsonResponse
    {
        if (!\${$entityVar}) {
            return \$this->json(['error' => '{$entityName} non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return \$this->json(
            \${$entityVar},
            Response::HTTP_OK,
            [],
            ['groups' => ['$group']]
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Crée un {$routeName}',
        tags: ['$entityName'],
        requestBody: new OA\RequestBody(
            description: 'Données à créer',
            content: new OA\JsonContent(
                properties: [
                    $propertiesDoc
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: '{$entityName} créé',
        content: new OA\JsonContent(ref: new AttributeModel(type: {$entityName}::class, groups: ['$group']))
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides'
    )]
    public function create(Request \$request): JsonResponse
    {
        \$data = json_decode(\$request->getContent(), true);
        \${$entityVar} = new {$entityName}();

        // Setters
        $settersCreate

        \$errors = \$this->validator->validate(\${$entityVar});
        if (count(\$errors) > 0) {
            return \$this->json(\$errors, Response::HTTP_BAD_REQUEST);
        }

        \$this->em->persist(\${$entityVar});
        \$this->em->flush();

        return \$this->json(
            \${$entityVar},
            Response::HTTP_CREATED,
            [],
            ['groups' => ['$group']]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Met à jour un {$routeName}',
        tags: ['$entityName'],
        requestBody: new OA\RequestBody(
            description: 'Données à mettre à jour',
            content: new OA\JsonContent(
                properties: [
                    $propertiesDoc
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: '{$entityName} mis à jour',
        content: new OA\JsonContent(ref: new AttributeModel(type: {$entityName}::class, groups: ['$group']))
    )]
    #[OA\Response(response: 400, description: 'Données invalides')]
    #[OA\Response(response: 404, description: 'Ressource non trouvée')]
    public function update(Request \$request, ?{$entityName} \${$entityVar}): JsonResponse
    {
        if (!\${$entityVar}) {
            return \$this->json(['error' => '{$entityName} non trouvé'], Response::HTTP_NOT_FOUND);
        }

        \$data = json_decode(\$request->getContent(), true);

        // Setters
        $settersUpdate

        \$errors = \$this->validator->validate(\${$entityVar});
        if (count(\$errors) > 0) {
            return \$this->json(\$errors, Response::HTTP_BAD_REQUEST);
        }

        \$this->em->flush();

        return \$this->json(
            \${$entityVar},
            Response::HTTP_OK,
            [],
            ['groups' => ['$group']]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Supprime un {$routeName}',
        tags: ['$entityName']
    )]
    #[OA\Response(response: 204, description: '{$entityName} supprimé')]
    #[OA\Response(response: 404, description: 'Ressource non trouvée')]
    public function delete(?{$entityName} \${$entityVar}): JsonResponse
    {
        if (!\${$entityVar}) {
            return \$this->json(['error' => '{$entityName} non trouvé'], Response::HTTP_NOT_FOUND);
        }

        \$this->em->remove(\${$entityVar});
        \$this->em->flush();

        return \$this->json(null, Response::HTTP_NO_CONTENT);
    }
}
PHP;
    }

    private function generateSetters(array $fields, string $entityVar, string $context): string
    {
        $setters = [];
        foreach ($fields as $field => $config) {
            $setter = 'set' . ucfirst($field);

            if ($config['type'] === 'association') {
                $target = basename(str_replace('\\', '/', $config['targetEntity']));
                $repo = lcfirst($target) . 'Repo';

                $setters[] = <<<PHP
if (isset(\$data['$field'])) {
    \${$repo} = \$this->em->getRepository($target::class);
    \${$entityVar}->$setter(\${$repo}->find(\$data['$field']));
}
PHP;
            } else {
                if ($context === 'create') {
                    $setters[] = "\${$entityVar}->$setter(\$data['$field'] ?? null);";
                } else {
                    $setters[] = <<<PHP
if (array_key_exists('$field', \$data)) {
    \${$entityVar}->$setter(\$data['$field']);
}
PHP;
                }
            }
        }
        return implode("\n        ", $setters);
    }

    private function generatePropertiesDoc(array $fields): string
    {
        $properties = [];
        foreach ($fields as $field => $config) {
            $type = $this->mapOpenApiType($config['type']);
            $example = $this->getExampleValue($config['type']);

            $properties[] = <<<PHP
new OA\Property(
    property: '$field',
    type: '$type',
    example: $example,
    nullable: {$this->getNullableValue($config['nullable'] ?? false)}
)
PHP;
        }
        return implode(",\n                    ", $properties);
    }

    private function generateFilterParams(array $fields): string
    {
        $params = [];
        foreach ($fields as $field => $config) {
            $type = $this->mapOpenApiType($config['type']);

            $params[] = <<<PHP
#[OA\Parameter(
    name: '$field',
    in: 'query',
    description: 'Filtrer par $field',
    schema: new OA\Schema(type: '$type')
)]
PHP;
        }
        return implode("\n    ", $params);
    }

    private function mapOpenApiType(string $doctrineType): string
    {
        return match (strtolower($doctrineType)) {
            'integer', 'smallint', 'bigint' => 'integer',
            'float', 'decimal' => 'number',
            'boolean' => 'boolean',
            'date', 'datetime', 'time' => 'string',
            'association' => 'integer',
            default => 'string'
        };
    }

    private function getExampleValue(string $type): string
    {
        return match (strtolower($type)) {
            'integer', 'smallint', 'bigint' => '1',
            'float', 'decimal' => '1.99',
            'boolean' => 'true',
            'date' => '"2023-01-01"',
            'datetime' => '"2023-01-01T12:00:00+00:00"',
            'time' => '"12:00:00"',
            'association' => '1',
            default => '"exemple"'
        };
    }

    private function getNullableValue(bool $nullable): string
    {
        return $nullable ? 'true' : 'false';
    }

    private function toRouteName(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
    }
}
