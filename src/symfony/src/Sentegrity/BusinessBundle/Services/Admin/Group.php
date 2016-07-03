<?php
namespace Sentegrity\BusinessBundle\Services\Admin;

use Sentegrity\BusinessBundle\Entity\Documents\Groups;
use Sentegrity\BusinessBundle\Entity\Documents\Organization as OrganizationEntity;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Entity\Repository\GroupsRepository;
use Sentegrity\BusinessBundle\Services\Service;

class Group extends Service
{
    /** @var GroupsRepository $repository */
    private $repository;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->repository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\Groups'
        );
    }

    /**
     * Gets a group by group id and organization
     * @param $groupId
     * @param OrganizationEntity $organization
     * @return Groups
     * @throws ValidatorException
     */
    public function getGroupByGroupAndOrganization($groupId, OrganizationEntity $organization)
    {
        $group = $this->repository->getByGroupAndOrganization($groupId, $organization);

        if (!$group) {
            throw new ValidatorException(
                null,
                $this->translator->trans('Group not founded.'),
                ErrorCodes::NOT_FOUND
            );
        }

        return $group;
    }

    /**
     * Deletes all groups owned by an organization
     * @param OrganizationEntity $organization
     * @return bool;
     */
    public function deleteGroupsByOrganization(OrganizationEntity $organization)
    {
        return $this->repository->deleteByOrganization($organization);
    }
}