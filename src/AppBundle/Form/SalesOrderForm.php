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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\Serializer\Annotation\VirtualProperty;

use AppBundle\Entity\SalesOrder;

class SalesOrderForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \AppBundle\Entity\User */
        $user = $options['user'];

        $builder
            ->add('orderNr', TextType::class, [
                'attr'=> ['placeholder' => 'Keep empty for autogeneration', 'class' => 'focus'],
                'required' => false
            ])
            ->add('orderDate', DateType::class)
            ->add('status', EntityType::class, [
                'class' => 'AppBundle:OrderStatus',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('os')->where('os.isSale = true');
                }
            ])
            ->add('customer', EntityType::class, [
                'class' => 'AppBundle:Customer',
                'choice_label' => 'name',
                'label' => 'Select',
                'required' => false,
            ])
            ->add('newCustomer', CustomerForm::class, [
                'mapped' => false,
                'user' => $user
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
            ->add('newProduct',  EntityType::class, [
                'required' => false,
                'mapped' => false,
                'class' => 'AppBundle:Product',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('p');
                    $qb->leftJoin('p.orderRelations', 'r');
                    $qb->leftJoin('r.order', 'o', \Doctrine\ORM\Query\Expr\Join::WITH, 'o INSTANCE OF AppBundle:SalesOrder')
                        ->having('COUNT(o.id) = 0')
                        ->groupBy('p.id');
                    return $qb;
                }
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => [
                    'class' => 'btn-success',
                ]
            ]);

        if ($user->hasRole("ROLE_MANAGER") || $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER_ADMIN"))
        {
            $builder->add('location',  EntityType::class, [
                    'class' => 'AppBundle:Location',
                    'choice_label' => 'name',
                    'required' => false,
                    'data' => $user->getLocation()
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

        $resolver->setRequired(array('user'));
    }
}
