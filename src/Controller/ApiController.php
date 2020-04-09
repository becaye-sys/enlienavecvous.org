<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TownRepository;
use App\Services\CustomSerializer;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ApiController
 * @package App\Controller
 * @Route(path="/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route(path="/appointments", name="api_appointments", methods={"GET"})
     * @param Request $request
     * @param AppointmentRepository $appointmentRepository
     * @return JsonResponse
     */
    public function getAvailableAppointments(Request $request, AppointmentRepository $appointmentRepository, SerializerInterface $serializer): JsonResponse
    {
        $appoints = $appointmentRepository->findAvailableAppointments();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($appoints, 'json', ['ignored_attributes' => ['therapist', 'patient']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/appointments/filter", name="api_appointments_filter", methods={"POST"})
     * @param Request $request
     * @param AppointmentRepository $appointmentRepository
     * @return JsonResponse
     * // Not used but keep it
     */
    public function getAvailableAppointmentsFilter(Request $request, AppointmentRepository $appointmentRepository, SerializerInterface $serializer): JsonResponse
    {
        $params = get_object_vars(json_decode($request->getContent()));
        $appoints = $appointmentRepository->findAvailableAppointmentsByParamsSplited($params);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($appoints, 'json', ['ignored_attributes' => ['therapist', 'patient']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/create/booking/{appointment}/{user}", name="api_create_booking", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createBooking(
        Request $request,
        AppointmentRepository $appointmentRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $appointId = (int)$request->attributes->get('appointment');
        $userId = (int)$request->attributes->get('user');
        $patient = $patientRepository->find($userId);
        $appointment = $appointmentRepository->find($appointId);
        $patient->addAppointment($appointment);
        $entityManager->flush();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            'groups' => ['create_booking']
        ];

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize(
            $appointment,
            'json',
            $defaultContext
        );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/confirm/booking/{id}", name="api_confirm_booking", methods={"POST"})
     * @param Request $request
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return JsonResponse
     */
    public function confirmBooking(
        Request $request,
        Appointment $appointment,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        MailerFactory $mailer
    ): JsonResponse
    {
        $appointment->setBooked(true);
        $entityManager->flush();

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getPatient()->getEmail(),
            'no-reply@onestlapourvous.org',
            $this->renderView('email/appointment_booked_patient.html.twig', ['appointment' => $appointment])
        );

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getTherapist()->getEmail(),
            'no-reply@onestlapourvous.org',
            $this->renderView('email/appointment_booked_therapist.html.twig', ['appointment' => $appointment])
        );

        $data = $serializer->serialize(
            "Rendez-vous confirmé !",
            'json'
        );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/departments-by-country", name="api_get_departments_by_country", methods={"POST"})
     * @return JsonResponse
     */
    public function getDepartmentsByCountry(DepartmentRepository $departmentRepository, Request $request, CustomSerializer $serializer)
    {
        $departments = $departmentRepository->findBy(
            ['country' => $request->request->get('country')],
            ['code' => 'ASC']
        );
        $data = $serializer->serialize($departments, ['towns']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/towns-by-departments", name="api_get_towns_by_department", methods={"POST"})
     * @return JsonResponse
     */
    public function getTownsByDepartments(TownRepository $townRepository, Request $request, CustomSerializer $serializer)
    {
        $departments = $townRepository->findBy(
            ['department' => $request->request->get('department')],
            ['code' => 'ASC']
        );
        $data = $serializer->serialize($departments, ['towns']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
