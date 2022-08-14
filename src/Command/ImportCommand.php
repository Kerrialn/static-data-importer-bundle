<?php

namespace Sdi\Command;

use Sdi\DTO\StaticDataTransferObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;
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
    private const INVALID_FILE_NAMING_ERROR = 'invalid static data file naming, please follow the convention "{entity name}_{order number}.{format}" eg. Blog_10.json, user_20.csv';
    private const ENTITY_NOT_FOUND_ERROR = 'Entity %s not found in %s';
    private const STATIC_DATA_FILE_EMPTY_SKIP_IMPORT = 'Skipping static data file %s. No content';
    private const STATIC_DATA_FILE_NOT_FOUND_ERROR = 'Static data file %s not found in %s';
    private const ENTITY_IMPORT_SUCCESSFUL = '%s records of Entity type %s have been added';
    private const IMPORT_COMPLETE = 'import complete';

    public function __construct(
        private SerializerInterface $serializer,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::COMMAND_ARG_DIR, InputArgument::OPTIONAL, 'directory of the files your want to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument(self::COMMAND_ARG_DIR);
        $array = $this->getFiles($directory, $io);

        foreach ($array as $data) {
            $entityDto = new StaticDataTransferObject($this->serializer);
            $entityDto->setEntityName($data['entityName']);
            $entityDto->setEntityNamespace($data['entityNamespace']);
            $entityDto->setStaticDataFile($data['file']);

            $entities = $entityDto->getDeserializeData();
            foreach ($entities as $entity) {
//                $this->entityManager->persist($entity);
            }
            $io->info(sprintf(self::ENTITY_IMPORT_SUCCESSFUL, count($entities), $entityDto->getEntityNamespace()));
        }
//        $this->entityManager->flush();

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
            $filename = $dirFile->getFilenameWithoutExtension();
            $nameParts = explode('_', $filename);

            if (count($nameParts) < 2) {
                $io->error(self::INVALID_FILE_NAMING_ERROR);
                return Command::FAILURE;
            }

            $entityName = ucfirst($nameParts[0]);
            $entityNamespace = 'App\\Entity\\' . $entityName;
            $order = (int)$nameParts[1];
            $entityPath = __DIR__ . '/../Entity/' . $entityName . '.php';

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

            if($dirFile->getContents() === ''){
                $io->error(sprintf(self::STATIC_DATA_FILE_EMPTY_SKIP_IMPORT, $dirFile->getFilename()));
                continue;
            }

            $files[$order] = [
                'entityName' => $entityName,
                'entityNamespace' => $entityNamespace,
                'file' => new SmartFileInfo($dirFile->getRealPath()),
            ];
        }
        ksort($files, SORT_NUMERIC);
        return $files;
    }
}
