<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
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

    /**
     * @Route(path="/availabilities", name="therapist_availabilites")
     * @return Response
     */
    public function availabilities()
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'therapist/availabilities.html.twig'
        );
    }

    /**
     * @Route(path="/history", name="therapist_history")
     * @return Response
     */
    public function history()
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'therapist/history.html.twig'
        );
    }

    /**
     * @Route(path="/patients", name="therapist_patients")
     * @return Response
     */
    public function patients()
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'therapist/patients.html.twig'
        );
    }

    /**
     * @Route(path="/settings", name="therapist_settings")
     * @return Response
     */
    public function settings(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        $currentUser = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        return $this->render(
            'therapist/settings.html.twig',
            [
                'user' => $currentUser
            ]
        );
    }

    /**
     * @Route(path="/security", name="therapist_security")
     * @return Response
     */
    public function security(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var User $user */
        $user = $this->getUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        return $this->render(
            'therapist/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView()
            ]
        );
    }
}