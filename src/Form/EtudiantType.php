<?php

namespace App\Form;

use App\Entity\Etudiant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EtudiantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Rakoto'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Jean'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['placeholder' => 'jean.rakoto@exemple.mg'],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '+261 34 00 000 00'],
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('filiere', TextType::class, [
                'label' => 'Filière',
                'attr' => ['placeholder' => 'Informatique, Gestion, Droit...', 'list' => 'filieres-list'],
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => array_combine(Etudiant::NIVEAUX, Etudiant::NIVEAUX),
                'placeholder' => 'Choisir un niveau',
            ])
            ->add('moyenneGenerale', NumberType::class, [
                'label' => 'Moyenne générale (/20)',
                'required' => false,
                'html5' => true,
                'attr' => ['min' => 0, 'max' => 20, 'step' => 0.01],
            ])
            ->add('actif', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['Actif' => true, 'Inactif' => false],
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo (jpg, png — 2 Mo max)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Merci de déposer une image valide (jpg, png, webp).',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etudiant::class,
        ]);
    }
}
