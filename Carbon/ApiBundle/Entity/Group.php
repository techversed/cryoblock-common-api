<?php

namespace Carbon\ApiBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\MappedSuperclass
 */
class Group extends BaseGroup
{
    /**
     * Returns an ARRAY of Role objects with the default Role object appended.
     *
     * @return array
     */
    public function getRoleValues()
    {
        return $this->roles->map(function ($role) {
            return $role->getRole();
        })->toArray();
    }

    /**
     * Returns an ARRAY of Role objects with the default Role object appended.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Returns the true ArrayCollection of Roles.
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null.
     * @param string $role
     * @return Role|null
     */
    public function getRole( $role )
    {
        foreach ( $this->getRoles() as $roleItem )
        {
            if ( $role == $roleItem->getRole() )
            {
                return $roleItem;
            }
        }
        return null;
    }

    /**
     * Pass a string, checks if we have that Role. Same functionality as getRole() except returns a real boolean.
     * @param string $role
     * @return boolean
     */
    public function hasRole( $role )
    {
        if ( $this->getRole( $role ) )
        {
            return true;
        }
        return false;
    }

    /**
     * Adds a Role OBJECT to the ArrayCollection. Can't type hint due to interface so throws Exception.
     * @throws Exception
     * @param Role $role
     */
    public function addRole( $role )
    {
        if ( !$role instanceof Role )
        {
            throw new \Exception( "addRole takes a Role object as the parameter" );
        }

        if ( !$this->hasRole( $role->getRole() ) )
        {
            $this->roles->add( $role );
        }
    }

    /**
     * Pass a string, remove the Role object from collection.
     * @param string $role
     */
    public function removeRole( $role )
    {
        $roleElement = $this->getRole( $role );
        if ( $roleElement )
        {
            $this->roles->removeElement( $roleElement );
        }
    }

    /**
     * Pass an ARRAY of Role objects and will clear the collection and re-set it with new Roles.
     * Type hinted array due to interface.
     * @param array $roles Of Role objects.
     */
    public function setRoles( array $roles )
    {
        $this->roles->clear();
        foreach ( $roles as $role )
        {
            $this->addRole( $role );
        }
    }

    /**
     * Directly set the ArrayCollection of Roles. Type hinted as Collection which is the parent of (Array|Persistent)Collection.
     * @param Doctrine\Common\Collections\Collection $role
     */
    public function setRolesCollection( Collection $collection )
    {
        $this->roles = $collection;
    }
}
