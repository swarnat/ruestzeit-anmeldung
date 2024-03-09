<?php

namespace App\Controller\Admin;

use App\Entity\Ruestzeit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RuestzeitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ruestzeit::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextEditorField::new('description'),
            IntegerField::new('memberlimit'),
            DateTimeField::new('registration_start'),
        ];
    }
    
}
