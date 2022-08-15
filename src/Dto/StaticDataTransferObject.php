<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle\Dto;

use Doctrine\Common\Annotations\AnnotationReader;
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

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    /**
     * @param string $entityNamespace
     */
    public function setEntityNamespace(string $entityNamespace): void
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return SmartFileInfo
     */
    public function getStaticDataFile(): SmartFileInfo
    {
        return $this->staticDataFile;
    }

    /**
     * @param SmartFileInfo $staticDataFile
     */
    public function setStaticDataFile(SmartFileInfo $staticDataFile): void
    {
        $this->staticDataFile = $staticDataFile;
    }

    public function getDeserializeData() : mixed
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer([new ObjectNormalizer($classMetadataFactory), new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder(), new XmlEncoder(), new CsvEncoder()]
        );

        return $serializer->deserialize(
            $this->staticDataFile->getContents(),
            $this->getEntityNamespace().'[]',
            $this->staticDataFile->getExtension());
    }

}