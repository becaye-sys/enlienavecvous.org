<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Form\ChangePasswordType;
use App\Form\TherapistAppointmentCancellationMessageType;
use App\Form\TherapistSettingsType;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\HistoryRepository;
use App\Repository\TherapistRepository;
use App\Services\CustomSerializer;
use App\Services\HistoryHelper;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @Route(path="/bookings", name="therapist_bookings")
     * @return Response
     */
    public function bookings(AppointmentRepository $appointmentRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $this->getCurrentTherapist();

        return $this->render(
            'therapist/bookings.html.twig',
            [
                'bookings' => $appointmentRepository->findBy(
                    [
                        'therapist' => $currentUser,
                        'booked' => true,
                        'status' => Appointment::STATUS_WAITING
                    ]
                ),
            ]
        );
    }

    /**
     * @Route(path="/booking/{id}", name="therapist_booking_status")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     */
    public function bookingStatus(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryHelper $historyHelper,
        MailerFactory $mailerFactory
    )
    {
        $status = $request->query->get('status');
        $patient = $appointment->getPatient();
        if ($status === Appointment::STATUS_DISHONORED) {
            if (null === $patient->getMalus()) {
                $malus = 1;
                $patient->setMalus($malus);
            } else {
                $malus = $patient->getMalus() + 1;
                $patient->setMalus($malus);
            }
            $historyHelper->addHistoryItem($appointment, History::ACTIONS[History::ACTION_DISHONORED]);
        } else {
            $historyHelper->addHistoryItem($appointment, History::ACTIONS[History::ACTION_HONORED]);
        }
        if ($patient->getMalus() >= 3) {
            $mailerFactory->createAndSend(
                "3 rdv non honorés...",
                '$to',
                null,
                $this->renderView('email/patient_malus.html.twig', ['patient' => $patient])
            );
        }
        $appointment->setStatus($status);
        $entityManager->flush();
        $this->addFlash('success', "Statut de la réservation mis à jour, disponible dans l'historique !");
        return $this->redirectToRoute('therapist_bookings');
    }

    /**
     * @Route(path="/booking/cancel/{id}", name="therapist_booking_cancel", methods={"POST","GET"})
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @param Appointment $appointment
     * @return Response
     */
    public function bookingCancellation(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerFactory $mailer,
        HistoryHelper $historyHelper
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");

        if (!$appointment instanceof Appointment) {
            $this->addFlash('error',"Réservation introuvable...");
            return $this->redirectToRoute('therapist_bookings');
        }
        if ($appointment->getBooked() === false && !$appointment->getPatient() instanceof Patient) {
            $this->addFlash('error', "Ce créneau n'est pas réservé...");
            return $this->redirectToRoute('therapist_bookings');
        }
        if ($appointment->getBooked() === false || !$appointment->getPatient() instanceof Patient) {
            $this->addFlash('error', "Ce créneau n'a pas été réservé correctement...");
            return $this->redirectToRoute('therapist_bookings');
        }

        $form = $this->createForm(TherapistAppointmentCancellationMessageType::class, $appointment);
        $form->handleRequest($request);

        if ($request->isMethod("POST") && $form->isSubmitted() && $form->isValid()) {
            $appointment->setBooked(false);
            $patientEmail = $appointment->getPatient()->getEmail();
            $appointment->setCancelled(true);
            $historyHelper->addHistoryItem($appointment, History::ACTIONS[History::ACTION_CANCELLED_BY_THERAPIST]);
            $historyHelper->addHistoryItem($appointment, History::ACTIONS[History::ACTION_DELETED_BY_THERAPIST]);
            $appointment->setPatient(null);
            $mailer->createAndSend(
                "Annulation du rendez-vous",
                $patientEmail,
                'no-reply@onestlapourvous.org',
                $this->renderView(
                    'email/appointment_cancelled_from_therapist.html.twig',
                    [
                        'appointment' => $appointment
                    ]
                )
            );
            $entityManager->remove($appointment);
            $entityManager->flush();
            $this->addFlash('info', "Rendez-vous annulé, créneau supprimé et message envoyé.");
            return $this->redirectToRoute('therapist_bookings');
        }

        return $this->render(
            'therapist/booking_cancellation.html.twig',
            [
                'booking_cancellation_form' => $form->createView()
            ]
        );
    }

    /**
     * @Route(path="/availabilities", name="therapist_availabilities", defaults={"page"=1})
     * @return Response
     */
    public function availabilities(
        AppointmentRepository $appointmentRepository,
        Request $request,
        EntityManagerInterface $manager,
        PaginatorInterface $paginator
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $this->getCurrentTherapist();
        $appointment = new Appointment();
        $appointment->setTherapist($currentUser);
        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);
        $appointmentForm->handleRequest($request);
        if ($appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $manager->persist($appointment);
            $manager->flush();
            $this->addFlash('success',"Créneau ajouté !");
            return $this->redirectToRoute('therapist_availabilities');
        }

        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        if (count($params) === 0) {
            $appointments = $appointmentRepository->findBy(
                ['therapist' => $currentUser],
                ['bookingDate' => 'ASC']
            );
        } else {
            $appointments = $appointmentRepository->findAvailableAppointmentsByParams($params);
        }

        $paginated = $paginator->paginate(
            $appointments,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'therapist/availabilities.html.twig',
            [
                'appointment_form' => $appointmentForm->createView(),
                'availabilities' => $paginated ?? [],
                'filters' => $params,
            ]
        );
    }

    /**
     * @Route(path="/availabilities/{id}/edit", name="therapist_availability_edit")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return Response
     */
    public function availabilitiesEdit(Appointment $appointment, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        if ($appointment->getPatient() instanceof Patient) {
            $this->addFlash('error',"Ce créneau a été réservé, impossible de le modifier.");
            return $this->redirectToRoute('therapist_availabilities');
        }
        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);
        $appointmentForm->handleRequest($request);
        if ($request->isMethod('POST') && $appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $manager->flush();
            $this->addFlash('success',"Créneau modifié !");
            return $this->redirectToRoute('therapist_availabilities');
        }

        return $this->render(
            'therapist/availability_edit.html.twig',
            [
                'appointment_form' => $appointmentForm->createView(),
            ]
        );
    }

    /**
     * @Route(path="/availabilities/{id}/delete", name="therapist_availability_delete")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return RedirectResponse
     */
    public function availabilitiesDelete(Appointment $appointment, EntityManagerInterface $manager): RedirectResponse
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        if (!$appointment || !$appointment instanceof Appointment) {
            $this->addFlash('error', "Créneau introuvable...");
            return $this->redirectToRoute('therapist_availabilities');
        }
        if ($appointment->getPatient() instanceof Patient) {
            $this->addFlash('error',"Ce créneau a été réservé... veuillez l'annuler avant de le supprimer.");
            return $this->redirectToRoute('therapist_availabilities');
        } else {
            $manager->remove($appointment);
            $manager->flush();
            $this->addFlash('success',"Créneau supprimé avec succès !");
            return $this->redirectToRoute('therapist_availabilities');
        }
    }

    /**
     * @Route(path="/history", name="therapist_history")
     * @return Response
     */
    public function history(HistoryRepository $historyRepository)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $this->getCurrentTherapist();
        return $this->render(
            'therapist/history.html.twig',
            [
                'history' => $historyRepository->findBy(['therapist' => $currentUser])
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
        return $this->render(
            'therapist/patients.html.twig'
        );
    }

    /**
     * @Route(path="/settings", name="therapist_settings")
     * @return Response
     */
    public function settings(
        Request $request,
        EntityManagerInterface $manager,
        MailerFactory $mailerFactory
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $currentUser */
        $currentUser = $this->getCurrentTherapist();
        $prevEmail = $currentUser->getEmail();
        $settingsType = $this->createForm(TherapistSettingsType::class, $currentUser);
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            /** @var Therapist $user */
            $user = $settingsType->getData();
            if ($user->getEmail() !== $prevEmail) {
                $user->setUniqueEmailToken();
                $mailerFactory->createAndSend(
                    "Changement de votre adresse email",
                    $user->getEmail(),
                    'no-reply@onestlapourvous.org',
                    $this->renderView(
                        'email/user_change_email.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    )
                );
                $manager->flush();
                $this->addFlash('success', "Vous allez recevoir un mail pour confirmer votre nouvelle adresse email.");
                return $this->redirectToRoute('therapist_settings');
            }
            $manager->flush();
            $this->addFlash('success',"Informations mises à jour !");
            return $this->redirectToRoute('therapist_settings');
        }

        return $this->render(
            'therapist/settings.html.twig',
            [
                'settings_form' => $settingsType->createView()
            ]
        );
    }

    /**
     * @Route(path="/security", name="therapist_security")
     * @return Response
     */
    public function security(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $manager
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        /** @var Therapist $user */
        $user = $this->getCurrentTherapist();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $newPassword = $changePasswordForm->getData()->getPassword();
            $encoded = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($encoded);
            $manager->flush();
            $this->addFlash('success',"Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('therapist_security');
        }
        return $this->render(
            'therapist/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView(),
                'appointments' => $appointmentRepository->findBy(['therapist' => $user, 'booked' => true])
            ]
        );
    }

    /**
     * @Route(path="/account/delete", name="therapist_account_delete")
     */
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette fonctionnalité.");
        /** @var Therapist $user */
        $user = $this->getCurrentTherapist();
        if ($user instanceof Therapist) {
            $userPassword = $request->request->get('password');
            if ($encoder->isPasswordValid($user, $userPassword)) {
                // send email account deletion
                $mailerFactory->createAndSend(
                    "Suppression de votre compte",
                    $user->getEmail(),
                    'no-reply@onestlapourvous.org',
                    $this->renderView('email/user_delete_account.html.twig')
                );
                // delete user
                $manager->remove($user);
                $manager->flush();
                // redirect
                $session = new Session();
                $session->invalidate();
                $this->addFlash('success', "Votre compte a été correctement supprimé.");
                return $this->redirectToRoute('app_logout');
            } else {
                $this->addFlash('error', "Votre mot de passe est invalide.");
                return $this->redirectToRoute('therapist_security');
            }
        }
        return $this->redirectToRoute('therapist_security');
    }

    private function getCurrentTherapist(): Therapist
    {
        return $this->therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}