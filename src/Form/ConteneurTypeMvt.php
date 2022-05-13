<?php

namespace App\Form;

use App\Entity\Conteneur;
use App\Entity\Stock;
use App\Repository\StockRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ConteneurTypeMvt extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        dd($stockRepository);


        $builder
            ->add('idStock', TextType::class, [
                'label' => 'Sélectionner l\'adresse de destination',
                'constraintes' => [$adresseStock]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conteneur::class,
        ]);
    }
}
