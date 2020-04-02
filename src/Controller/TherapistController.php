<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TherapistController
 * @package App\Controller
 * @Route(path="/therapeute")
 */
class TherapistController extends AbstractController
{
    /**
     * @Route(path="/dashboard", name="therapist_dashboard")
     * @return Response
     */
    public function dashboard()
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'therapist/dashboard.html.twig'
        );
    }
}