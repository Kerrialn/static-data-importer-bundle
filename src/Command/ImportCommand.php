<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Kerrialn\Bundle\StaticDataImporterBundle\Dto\StaticDataTransferObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

#[AsCommand(
    name: 'import',
    description: 'import csv, json or xml as entity objects',
    hidden: false,
)]
class ImportCommand extends Command
{
    private const COMMAND_ARG_DIR = 'directory';

    private const COMMAND_OPTION_DUMP = 'dump';

    private const COMMAND_OPTION_DUMP_SHORT = 'd';

    private const ENTITY_PATH = __DIR__ . '/../../../../../src/Entity';

    private const INVALID_FILE_NAMING_ERROR = 'invalid static data file naming, please follow the convention "{order number}_{entity name}.{format}" eg. 10_Blog.json, 20_user.csv';

    private const ENTITY_NOT_FOUND_ERROR = 'Entity %s not found in %s';

    private const LOADING_ENTITY = 'preparing entity: %s';

    private const STATIC_DATA_FILE_EMPTY_SKIP_IMPORT = 'Skipping static data file %s. No content';

    private const STATIC_DATA_FILE_NOT_FOUND_ERROR = 'Static data file %s not found in %s';

    private const ENTITY_IMPORT_SUCCESSFUL = '%s records of type %s have been generated';

    private const TOTAL_IMPORT_COUNT = '%s records inserted';

    private const IMPORT_COMPLETE = 'import complete';

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::COMMAND_ARG_DIR, InputArgument::REQUIRED, 'directory of the files your want to import');
        $this->addOption(self::COMMAND_OPTION_DUMP, self::COMMAND_OPTION_DUMP_SHORT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument(self::COMMAND_ARG_DIR);
        $hasOptionDump = $input->getOption(self::COMMAND_OPTION_DUMP);
        $array = $this->getFiles($directory, $io);

        if (is_array($array) === false) {
            return Command::FAILURE;
        }

        $totalCount = 0;
        foreach ($array as $data) {
            $entityDto = new StaticDataTransferObject();
            $entityDto->setEntityName($data['entityName']);
            $entityDto->setEntityNamespace($data['entityNamespace']);
            $entityDto->setStaticDataFile($data['file']);

            $io->info(sprintf(self::LOADING_ENTITY, $entityDto->getEntityName()));

            $entities = $entityDto->getDeserializeData();
            foreach ($entities as $entity) {

                $this->entityManager->persist($entity);

                if ($hasOptionDump) {
                    print_r($entity);
                }

                $totalCount++;
            }

            $io->info(sprintf(self::ENTITY_IMPORT_SUCCESSFUL, is_countable($entities) ? count($entities) : 0, $entityDto->getEntityNamespace()));
            $this->entityManager->flush();
        }

        $io->info(sprintf(self::TOTAL_IMPORT_COUNT, $totalCount));
        $io->success(self::IMPORT_COMPLETE);
        return Command::SUCCESS;
    }

    private function getFiles(mixed $directory, SymfonyStyle $io): int|array
    {
        $finder = new Finder();
        $dirFiles = $finder->in($directory)->files();
        $fileSystem = new SmartFileSystem();

        $files = [];
        foreach ($dirFiles as $dirFile) {
            $file = new SmartFileInfo($dirFile->getRealPath());

            if (str_contains($file->getFilenameWithoutExtension(), 'SKIP')) {
                continue;
            }

            $filename = $dirFile->getFilenameWithoutExtension();
            $nameParts = explode('_', $filename);

            if (count($nameParts) < 2) {
                $io->error(self::INVALID_FILE_NAMING_ERROR);
                return Command::FAILURE;
            }

            $entityName = ucfirst($nameParts[1]);
            $order = $nameParts[0];
            $entityNamespace = 'App\\Entity\\' . $entityName;
            $entityPath = self::ENTITY_PATH . '/' . $entityName . '.php';

            if ($fileSystem->exists($entityPath) === false) {
                $io->error(sprintf(self::ENTITY_NOT_FOUND_ERROR, $entityName, $entityPath));
                return Command::FAILURE;
            }

            if (is_int((int)$order) === false) {
                $io->error(self::INVALID_FILE_NAMING_ERROR);
                return Command::FAILURE;
            }

            if ($fileSystem->exists($dirFile) === false) {
                $io->error(sprintf(self::STATIC_DATA_FILE_NOT_FOUND_ERROR, $dirFile->getFilename(), $dirFile));
                return Command::FAILURE;
            }

            if ($dirFile->getContents() === '') {
                $io->error(sprintf(self::STATIC_DATA_FILE_EMPTY_SKIP_IMPORT, $dirFile->getFilename()));
                continue;
            }


            $files[$order] = [
                'entityName' => $entityName,
                'entityNamespace' => $entityNamespace,
                'file' => $file
            ];
        }
        ksort($files, SORT_NUMERIC);
        return $files;
    }
}
