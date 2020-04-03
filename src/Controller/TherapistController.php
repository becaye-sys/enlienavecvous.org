<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Therapist;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Form\ChangePasswordType;
use App\Form\TherapistSettingsType;
use App\Repository\AppointmentRepository;
use App\Repository\TherapistRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class TherapistController
 * @package App\Controller
 * @Route(path="/therapeute")
 */
class TherapistController extends AbstractController
{
    private $therapistRepository;

    public function __construct(TherapistRepository $therapistRepository)
    {
        $this->therapistRepository = $therapistRepository;
    }

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
    public function availabilities(TherapistRepository $therapistRepository, AppointmentRepository $appointmentRepository, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        if (!$currentUser instanceof Therapist) {
            return $this->redirectToRoute('therapist_dashboard');
        }
        $appointment = new Appointment($currentUser);
        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);
        $appointmentForm->handleRequest($request);
        if ($request->isMethod('POST') && $appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $manager->persist($appointment);
            $manager->flush();
            return $this->redirectToRoute('therapist_availabilites');
        }
        return $this->render(
            'therapist/availabilities.html.twig',
            [
                'appointment_form' => $appointmentForm->createView(),
                'availabilities' => $appointmentRepository->findBy(['therapist' => $currentUser])
            ]
        );
    }

    /**
     * @Route(path="/history", name="therapist_history")
     * @return Response
     */
    public function history(AppointmentRepository $appointmentRepository, TherapistRepository $therapistRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        return $this->render(
            'therapist/history.html.twig',
            [
                'history' => $appointmentRepository->findBy(['therapist' => $currentUser, 'booked' => true])
            ]
        );
    }

    /**
     * @Route(path="/patients", name="therapist_patients")
     * @return Response
     */
    public function patients()
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        $currentUser = $this->getCurrentTherapist();
        return $this->render(
            'therapist/patients.html.twig'
        );
    }

    /**
     * @Route(path="/settings", name="therapist_settings")
     * @return Response
     */
    public function settings(UserRepository $userRepository, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var User $currentUser */
        $currentUser = $userRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        $settingsType = $this->createForm(TherapistSettingsType::class, $currentUser);
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            $manager->flush();
            return $this->redirectToRoute('therapist_settings');
        }
        return $this->render(
            'therapist/settings.html.twig',
            [
                'user' => $currentUser,
                'settings_form' => $settingsType->createView()
            ]
        );
    }

    /**
     * @Route(path="/security", name="therapist_security")
     * @return Response
     */
    public function security(UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var User $user */
        $user = $this->getUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $newPassword = $changePasswordForm->getData()->getPassword();
            $encoded = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($encoded);
            $manager->flush();
            return $this->redirectToRoute('therapist_security');
        }
        return $this->render(
            'therapist/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView()
            ]
        );
    }

    private function getCurrentTherapist(): Therapist
    {
        return $this->therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}