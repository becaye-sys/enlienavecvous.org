<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PatientController
 * @package App\Controller
 * @Route(path="/patient")
 */
class PatientController extends AbstractController
{
    /**
     * @Route(path="/dashboard", name="patient_dashboard")
     * @return Response
     */
    public function dashboard()
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'patient/dashboard.html.twig'
        );
    }
}
