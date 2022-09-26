<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Given an entity, return a flattened representation of itself
     * Replaces relation keys in $data with their corresponding id fields
     * $data = ['company'=>$companyEntity] -> $data = ['companyId'=>1]
     *
     * TODO: Does not handle relations that manifest as ArrayCollections. Either needs to ignore them or deal with them.
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        $metadata = $this->entityManager->getClassMetadata($object::class);
        $fields = $metadata->getFieldNames();
        $relations = $metadata->getAssociationMappings();

        // $fields contains plain data (no relations), $relations only contains properties mapped to other entities
        // So we have to loop through both

        foreach ($fields as $propertyName)
        {
            $res[$propertyName] = $object->{"get".ucfirst($propertyName)}();
        }

        // TODO: Figure out behavior of ToMany associations because they are collection properties
        // Might be best to ignore them since they can get *big*
        foreach ($relations as $fieldName => $relation)
        {
            if ($relation['type'] & ClassMetadata::TO_MANY)
            {
                //echo "TO MANY\n";
                continue;
            }

            // Area's location -> Location's locationId
            $res[$fieldName.'Id'] = $this->getRelationId($object, $relation); // camel-snake-case normalizer handles changing names :)
        }

        return $res;
    }

    /**
     * Given an entity and one of its relations, return the identifier of the related entity
     * @param EntityInterface $entity
     * @param array $relation doctrine relation info in ClassMetadata
     * @return mixed related identifier
     */
    private function getRelationId(EntityManagerInterface $entity, array $relation): mixed
    {
        $fieldName = $relation['fieldName'];
        $relatedEntityClass = $relation['targetEntity'];
        $relatedMetadata = $this->entityManager->getClassMetadata($relatedEntityClass);
        $relatedIdentifier = $relatedMetadata->getSingleIdentifierFieldName();

        // null check operator prevents calling get<Entity>Id on null relations
        return $entity->{"get".$fieldName}()?->{"get".ucfirst($relatedIdentifier)}();
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        // TODO: Implement denormalize() method.
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        // TODO: Implement supportsDenormalization() method.
    }

    public function supportsNormalization(mixed $data, string $format = null)
    {
        // TODO: Implement supportsNormalization() method.
    }
}