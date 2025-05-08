<?php

namespace App\Controller\Admin;

use App\Entity\Mail;
use App\Entity\MailAttachment;
use App\Entity\MailAttachmentClick;
use App\Generator\CurrentRuestzeitGenerator;
use App\Repository\MailRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class MailOverviewController extends AbstractCrudController
{
    private $currentRuestzeitGenerator;
    private $entityManager;
    private $requestStack;

    public function __construct(
        CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->currentRuestzeitGenerator = $currentRuestzeitGenerator;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Mail::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('E-Mail')
            ->setEntityLabelInPlural('E-Mails')
            ->setPageTitle('index', 'E-Mail Übersicht')
            ->setDefaultSort(['sentAt' => 'DESC'])
            ->showEntityActionsInlined(true);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('recipient', 'Anmeldung'))
            ->add(TextFilter::new('subject', 'Betreff'))
            ->add(TextFilter::new('recipientEmail', 'Empfänger'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield AssociationField::new('recipient', 'Anmeldung');
        yield TextField::new('subject', 'Betreff');
        yield EmailField::new('recipientEmail', 'Empfänger');
        yield DateTimeField::new('sentAt', 'Gesendet am')
            ->setFormat('dd.MM.yyyy HH:mm');
        
        yield BooleanField::new('opened', 'Geöffnet');
        yield DateTimeField::new('openedAt', 'Geöffnet am')
            ->setFormat('dd.MM.yyyy HH:mm');
        
        // Custom field for attachments with click status
        $entityManager = $this->entityManager;
        yield TextField::new('attachmentsWithStatus', 'Anhänge & Status')
            ->formatValue(function ($value, $entity) use ($entityManager) {
                /** @var Mail $entity */
                $attachments = $entity->getAttachments();

                if ($attachments->isEmpty()) {
                    return '<em style="color:#999;">Keine Anhänge</em>';
                }

                // Get the repository for MailAttachmentClick
                $clickRepository = $entityManager->getRepository(\App\Entity\MailAttachmentClick::class);
                
                $result = '<ul class="attachment-list">';
                foreach ($attachments as $attachment) {
                    /** @var MailAttachment $attachment */
                    // Find click information directly using the repository
                    $click = $clickRepository->findOneBy([
                        'mail' => $entity->getId(),
                        'attachment' => $attachment->getId()
                    ]);

                    $clicked = $click && $click->isClicked();
                    $clickedIcon = $clicked
                        ? '<i class="fas fa-check text-success" title="Angeklickt"></i>'
                        : '<i class="fas fa-times text-danger" title="Nicht angeklickt"></i>';
                    
                    $clickedAt = $click ? $click->getClickedAt() : null;

                    $clickedAtText = $clickedAt ? '<small> am ' . $clickedAt->format('d.m.Y H:i') . '</small>': '';
                    
                    $result .= sprintf(
                        '<li>%s %s%s</li>',
                        $attachment->getFilename(),
                        $clickedIcon,
                        $clickedAt ? $clickedAtText : ''
                    );
                }
                $result .= '</ul>';
                
                return $result;
            })
            ->setVirtual(true)
            ->renderAsHtml();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    /**
     * This method ensures we only show emails for the current Ruestzeit
     */
    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Get current Ruestzeit
        $currentRuestzeit = $this->currentRuestzeitGenerator->get();
        
        // Filter by current Ruestzeit
        $queryBuilder
            ->andWhere('entity.ruestzeit = :ruestzeit')
            ->setParameter('ruestzeit', $currentRuestzeit);
        
        return $queryBuilder;
    }
}