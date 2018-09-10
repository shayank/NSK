<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
 */

namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Company
 *
 * @ORM\Table(name="acompany")
 * @ORM\Entity(repositoryClass="AdminBundle\Repository\CompanyRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"c" = "Customer", "p" = "Partner"})
 */
abstract class ACompany
{
    public function __construct() {
        $this->addresses = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var ArrayCollection|Address[]
     *
     * @ORM\OneToMany(targetEntity="Address", mappedBy="company", cascade={"all"})
     */
    protected $addresses;

    /**
     * @var int
     *
     * @ORM\Column(name="kvk_nr", type="integer", nullable=true)
     */
    protected $kvkNr;

    /**
     * @var string
     *
     * @ORM\Column(name="representative", type="string", length=255, nullable=true)
     */
    protected $representative;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ACompany
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set kvkNr
     *
     * @param integer $kvkNr
     *
     * @return ACompany
     */
    public function setKvkNr($kvkNr)
    {
        $this->kvkNr = $kvkNr;

        return $this;
    }

    /**
     * Get kvkNr
     *
     * @return int
     */
    public function getKvkNr()
    {
        return $this->kvkNr;
    }

    /**
     * Set representative
     *
     * @param string $representative
     *
     * @return ACompany
     */
    public function setRepresentative($representative)
    {
        $this->representative = $representative;

        return $this;
    }

    /**
     * Get representative
     *
     * @return string
     */
    public function getRepresentative()
    {
        return $this->representative;
    }

    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
}
