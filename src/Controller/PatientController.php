<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PatientSettingsType;
use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class PatientController
 * @package App\Controller
 * @Route(path="/patient")
 */
class PatientController extends AbstractController
{
    private $patientRepository;

    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

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

    /**
     * @Route(path="/rendez-vous", name="patient_appointments")
     * @return Response
     */
    public function appointments(AppointmentRepository $appointmentRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        /** @var Patient $currentPatient */
        $currentPatient = $this->getCurrentPatient();
        $appoints = $appointmentRepository->findBy(['patient' => $currentPatient, 'booked' => true]);
        return $this->render(
            'patient/appointments.html.twig',
            [
                'appoints' => $appoints
            ]
        );
    }

    /**
     * @Route(path="/rendez-vous/annuler/{id}", name="patient_appointment_cancel")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return Response
     */
    public function appointmentCancel(Appointment $appointment, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        if ($appointment instanceof Appointment && $appointment->getBooked() === true) {
            $appointment->setBooked(false);
            $appointment->setCancelled(true);
            $appointment->setPatient(null);
            $entityManager->flush();
            $this->addFlash('info', "Rendez-vous annulé. Vous allez recevoir un mail de confirmation de l'annulation.");
            // mail
            return $this->redirectToRoute('patient_appointments');
        } else {
            $this->addFlash('error', "Erreur lors de l'annulation.");
            return $this->redirectToRoute('patient_appointments');
        }
    }

    /**
     * @Route(path="/recherche", name="patient_research")
     * @return Response
     */
    public function research(Request $request, AppointmentRepository $appointmentRepository, Security $security)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        return $this->render(
            'patient/research.html.twig',
            [
                'current_user' => $security->getUser()
            ]
        );
    }

    /**
     * @Route(path="/historique", name="patient_history")
     * @return Response
     */
    public function history(AppointmentRepository $appointmentRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        $currentUser = $this->getCurrentPatient();
        $history = $appointmentRepository->findBy(['patient' => $currentUser]);
        return $this->render(
            'patient/history.html.twig',
            [
                'history' => $history
            ]
        );
    }

    /**
     * @Route(path="/parametres", name="patient_settings")
     * @return Response
     */
    public function settings(Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        /** @var Patient $currentUser */
        $currentUser = $this->getCurrentPatient();
        $settingsType = $this->createForm(PatientSettingsType::class, $currentUser);
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            $manager->flush();
            $this->addFlash('success',"Informations mises à jour !");
            return $this->redirectToRoute('patient_settings');
        }
        return $this->render(
            'patient/settings.html.twig',
            [
                'settings_form' => $settingsType->createView()
            ]
        );
    }

    /**
     * @Route(path="/securite", name="patient_security")
     * @return Response
     */
    public function security(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette page.");
        /** @var User $user */
        $user = $this->getUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $newPassword = $changePasswordForm->getData()->getPassword();
            $encoded = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($encoded);
            $manager->flush();
            $this->addFlash('success',"Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('patient_security');
        }
        return $this->render(
            'patient/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView()
            ]
        );
    }

    private function getCurrentPatient(): Patient
    {
        return $this->patientRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}
