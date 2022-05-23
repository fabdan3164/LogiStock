<?php

namespace App\Form;

use App\Entity\Conteneur;
use App\Entity\Produit;
use App\Entity\Reception;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ConteneurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, ['label' => 'Saisir la quantité à réceptionner', 'attr' => ['min' => 1]])
            ->add('idReception', EntityType::class, ['class' => Reception::class, 'label' => 'Sélectionner le Bon De Livraison'])
            ->add('idProduit', EntityType::class, ['class' => Produit::class, 'label' => 'Sélectionner le Part Number à réceptionner'])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conteneur::class,
        ]);
    }
}
