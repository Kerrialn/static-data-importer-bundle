<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle\Dto;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Kerrialn\Bundle\StaticDataImporterBundle\Normalizer\EntityNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symplify\SmartFileSystem\SmartFileInfo;

class StaticDataTransferObject
{


    private string $entityName;

    private string $entityNamespace;

    private SmartFileInfo $staticDataFile;

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    public function setEntityNamespace(string $entityNamespace): void
    {
        $this->entityNamespace = $entityNamespace;
    }

    public function getStaticDataFile(): SmartFileInfo
    {
        return $this->staticDataFile;
    }

    public function setStaticDataFile(SmartFileInfo $staticDataFile): void
    {
        $this->staticDataFile = $staticDataFile;
    }

    public function getDeserializeData(): mixed
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizers = [new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            propertyTypeExtractor: new ReflectionExtractor()),
            new ArrayDenormalizer(),
            new EntityNormalizer($this->entityManager)
        ];
        $encoders = [new JsonEncoder(), new XmlEncoder(), new CsvEncoder()];

        $serializer = new Serializer(
            normalizers: $normalizers,
            encoders: $encoders
        );

        return $serializer->deserialize(
            $this->staticDataFile->getContents(),
            $this->getEntityNamespace() . '[]',
            $this->staticDataFile->getExtension()
        );
    }
}
