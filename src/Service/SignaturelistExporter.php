<?php

namespace App\Service;

use App\Entity\Ruestzeit;
use App\Enum\MealType;
use App\Enum\AnmeldungStatus;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SignaturelistExporter
{
    
    public function __construct(
        private TranslatorInterface $translator,
        private Environment $twig,
        protected EntityManagerInterface $entityManager
    )
    {}


    public function generatePDF(Ruestzeit $ruestzeit, FieldCollection $fields, string $filename) {
        $query = $this->entityManager->createQuery('SELECT a FROM App\Entity\Anmeldung a WHERE a.ruestzeit = ' . $ruestzeit->getId() . " AND a.status = '" . AnmeldungStatus::ACTIVE->value . "' ORDER BY a.landkreis, a.lastname, a.postalcode");
        $anmeldungen = $query->getResult();

        $anmeldeListe = [];
        foreach($anmeldungen as $anmeldung) {
            $landkreis = $anmeldung->getLandkreis();
            if(empty($landkreis)) continue;

            if(!isset($anmeldeListe[$landkreis])) {
                $anmeldeListe[$landkreis] = [];
            }

            $anmeldeListe[$landkreis][] = $anmeldung;
        }

        $template = $this->twig->render('signaturelist.html.twig', [
            'landkreise' => $anmeldeListe,
            'ruestzeit' => $ruestzeit
        ]);

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 10,
            'margin_top' => 15,
            'margin_right' => 10,
            'margin_bottom' => 15,
            'margin_header' => 7,
            'margin_footer' => 5,
            'default_font_size' => 10,
            'orientation' => 'L'
        ]);
        $mpdf->SetHTMLHeader('<table style="width:100%;" cellspacing=0 cellpadding=0><tr><td>'.$ruestzeit->getInternalTitle().'</td><td align="right">' . $ruestzeit->getDateFrom()->format("d.m.Y") . ' - ' . $ruestzeit->getDateTo()->format("d.m.Y") . '</td></tr></table>');
        $mpdf->SetHTMLFooter('<table style="width:100%;" cellspacing=0 cellpadding=0><tr><td>{DATE j.m.Y H:m:s}</td><td align="right">Seite {PAGENO} / {nbpg}</td></tr></table>');
        

        $mpdf->WriteHTML($template);

        $mpdf->Output();

        exit();
    }
}
