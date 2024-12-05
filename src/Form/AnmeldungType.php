<?php

namespace App\Form;

use App\Entity\Anmeldung;
use App\Entity\Ruestzeit;
use App\Enum\MealType;
use App\Enum\RoomType;
use App\Generator\CurrentRuestzeitGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnmeldungType extends AbstractType
{
    public function __construct(private CurrentRuestzeitGenerator $currentRuestzeitGenerator)
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentRuestzeit = $this->currentRuestzeitGenerator->get();

        $builder
            ->add('ruestzeit', EntityType::class, [
                // looks for choices from this entity
                'class' => Ruestzeit::class,
            
                // uses the User.username property as the visible option string
                'choice_label' => 'title',
            
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate', null, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                // 2. Disable HTML5 option
                'html5' => false                
            ])
            ->add('postalcode')
            ->add('city')
            ->add('address')
            ->add('phone')
            ->add('email');

        if($currentRuestzeit->isAskSchoolclass()) {
            $builder->add('schoolclass');
        }
        if($currentRuestzeit->isShowRoomRequest()) {
            $builder->add('roomRequest', EnumType::class, [
                'class' => RoomType::class
            ]);
        }
        if($currentRuestzeit->isShowReferer()) {
            $builder->add('referer');
        }

        $builder->add('mealtype', EnumType::class, [
                'class' => MealType::class
            ])
            ->add('notes', TextareaType::class, [
                "empty_data" => "",
            ])
            ->add('dsgvo_agree')->setRequired(true)
            ->add('agb_agree')->setRequired(true)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Anmeldung::class,
        ]);
    }
}
