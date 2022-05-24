<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('denomination')
            ->add('fournisseur')
            ->add('description')
            ->add('prixUnitaire')
            ->add('stockMin')
            ->add('stockMax')
            ->add('image', FileType::class, ['label' => 'Image du produit', 'mapped' => false, 'required' => false, 'constraints' => [
                new File([
                    'maxSize' => '4M',
                    'mimeTypes' => [
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Merci de télécharger un document au format JPEG',
                ])
            ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
