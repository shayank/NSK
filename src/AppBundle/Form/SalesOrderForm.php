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

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductOrderRelation;

class SalesOrderForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \AppBundle\Entity\User */
        $user = $options['user'];

        /** @var SalesOrder */
        $order = $builder->getData();

        if ($order->getId())
        {
            $builder
                ->add('orderNr', TextType::class, [
                    'attr'=> ['placeholder' => 'Keep empty for autogeneration'],
                    'required' => false
                ]);
        }
        else
        {
            $builder
                ->add('orderNr', TextType::class, [
                    'attr'=> ['placeholder' => 'Keep empty for autogeneration', 'class' => 'focus'],
                    'required' => false
                ]);
        }

        $builder->add('remarks', TextareaType::class, ['required' => false, 'attr' => ['rows' => '4']])
            ->add('orderDate', DateType::class)
            ->add('transport', MoneyType::class, ['required' => false])
            ->add('discount', MoneyType::class, ['required' => false])
            ->add('isGift', CheckboxType::class, ['required' => false])
            ->add('status', EntityType::class, [
                'class' => 'AppBundle:OrderStatus',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('os')->where('os.isSale = true')->orderBy("os.name", "ASC"); }
            ])
            ->add('customer', EntityType::class, [
                'class' => 'AppBundle:Customer',
                'choice_label' => 'name',
                'label' => 'Select',
                'required' => false,
                'attr' => ['class' => 'combobox'],
                'query_builder' => function (EntityRepository $er) use ($user) { 
                    $qb = $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); 
                    /** @var \AppBundle\Entity\User $user */
                    if ($user->hasRole("ROLE_PARTNER"))
                        $qb = $qb->where('x.partner = :partner')->setParameter('partner', $user->getPartner() ?? -1); 
                    return $qb;
                }
            ])
            ->add('newCustomer', CustomerForm::class, [
                'mapped' => false,
                'user' => $user,
                'customer' => $order->getCustomer()
            ])
            ->add('newOrExistingCustomer', ChoiceType::class, [
                'label' => false,
                'mapped' => false,
                'expanded' => true,
                'multiple' => false,
                'data' => 'existing',
                'choices' => [
                    'Existing' => 'existing',
                    'New' => 'new',
                ]
            ])         
            ->add('productRelations', CollectionType::class, [
                'entry_type' => ProductOrderRelationForm::class
            ])
            ->add('newService',  EntityType::class, [
                    'required' => false,
                    'mapped' => false,
                    'data' => null,
                    'class' => 'AppBundle:ProductOrderRelation',
                    'choices' => $order->getProductRelations(),
                    'attr' => ['class' => 'combobox'],
                    'choice_label' => function (ProductOrderRelation $r) {
                        return $r->getProduct()->getSku();
                    },
                ])
            ->add('backorder', CheckboxType::class, ['required' => false, 'mapped' => false, 'label' => 'Back order: This creates empty purchase order too']) // new
            ->add('repairorder', CheckboxType::class, ['required' => false, 'mapped' => false, 'label' => 'Repair order: These products are not purchased', 'data' => $options['isRepair']]) // new
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn-success btn-120']
            ]);

        if ($order->getRepair())
        {
            $builder
                ->add('repair', RepairForm::class, ['label' => false]);
        }

        if ($order->getBackingPurchaseOrder() || $order->getRepair())
        {
            $builder->add('newProduct',  EntityType::class, [
                'required' => false,
                'mapped' => false,
                'class' => 'AppBundle:ProductType',
                'choice_label' => 'name',
                'attr' => ['class' => 'combobox'],
                'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
            ]);
        }
        else
        {
            $builder->add('addProduct', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'class' => Product::class,
                'choice_label' => function(Product $p) {
                    return $p->getSku() . ' - ' . $p->getName();
                },
                'choices' => $options['stock'],
                'attr' => ['class' => 'combobox focus']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SalesOrder::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'sorder',
        ));

        $resolver->setRequired(array('user', 'stock', 'isRepair'));
    }
}
