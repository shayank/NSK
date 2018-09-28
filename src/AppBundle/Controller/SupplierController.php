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
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Supplier;
use AppBundle\Form\SupplierType;
use AppBundle\Form\IndexSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/supplier")
 */
class SupplierController extends Controller
{
    /**
     * @Route("/", name="supplier_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Supplier');

        $suppliers = array();

        $form = $this->createForm(IndexSearchType::class, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $suppliers = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $suppliers = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $suppliersPage = $paginator->paginate($suppliers, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Supplier:index.html.twig', array(
            'suppliers' => $suppliersPage,
            'form' => $form->createView()
            ));
    }


    /**
     * @Route("/edit/{id}", name="supplier_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $supplier = new Supplier();
        }
        else
        {
            $supplier = $em->getRepository('AppBundle:Supplier')->find($id);
        }

        $form = $this->createForm(SupplierType::class, $supplier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute('supplier_index');
        }


        return $this->render('AppBundle:Supplier:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="supplier_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $supplier = $em->getRepository('AppBundle:Supplier')->find($id);
        $em->remove($supplier);
        $em->flush();

        return $this->redirectToRoute('supplier_index');
    }
}