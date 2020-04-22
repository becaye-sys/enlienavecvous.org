<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\History;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TownRepository;
use App\Services\CustomSerializer;
use App\Services\HistoryHelper;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
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
     * @return JsonResponse
     */
    public function getAvailableAppointments(
        AppointmentRepository $appointmentRepository,
        CustomSerializer $serializer
    ): JsonResponse
    {
        $appoints = $appointmentRepository->findAvailableAppointments();
        dump($appoints);
        $data = $serializer->serializeByGroups($appoints, ['create_booking']);
        //$data = $serializer->serialize($appoints, ['patient','histories']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/appointments/filter", name="api_appointments_filter", methods={"POST"})
     * @return JsonResponse
     * // Not used but keep it
     */
    public function getAvailableAppointmentsFilter(
        Request $request,
        AppointmentRepository $appointmentRepository,
        CustomSerializer $serializer
    ): JsonResponse
    {
        $params = get_object_vars(json_decode($request->getContent()));
        $appoints = $appointmentRepository->findAvailableAppointmentsByParamsSplited($params);
        $data = $serializer->serialize($appoints, ['therapist', 'patient']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/create/booking/{appointment}/{user}", name="api_create_booking", methods={"POST"})
     * @return JsonResponse
     */
    public function createBooking(
        Request $request,
        AppointmentRepository $appointmentRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
        CustomSerializer $serializer
    ): JsonResponse
    {
        $appointId = (int)$request->attributes->get('appointment');
        $userId = (int)$request->attributes->get('user');
        $patient = $patientRepository->find($userId);
        $appointment = $appointmentRepository->find($appointId);
        $appointment->setStatus(Appointment::STATUS_BOOKING);
        $patient->addAppointment($appointment);
        $entityManager->flush();
        $dataToSerialize = [
            'bookingId' => $appointment->getId(),
            'bookingDate' => $appointment->getBookingDate(),
            'bookingStart' => $appointment->getBookingStart(),
            'therapistFirstName' => $appointment->getTherapist()->getFirstName(),
            'therapistLastName' => $appointment->getTherapist()->getLastName()
        ];
        $data = $serializer->serializeByGroups($dataToSerialize, ['create_booking']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/confirm/booking/{id}", name="api_confirm_booking", methods={"GET"})
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return JsonResponse
     */
    public function confirmBooking(
        Appointment $appointment,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        MailerFactory $mailer,
        HistoryHelper $historyHelper
    ): JsonResponse
    {
        //$appointId = $request->query->get('id');
        //$appointment = $appointmentRepository->findOneBy(['id' => $appointId, 'status' => Appointment::STATUS_BOOKING]);
        if (!$appointment || !$appointment instanceof Appointment) {
            return new JsonResponse("Pas de rendez-vous enregistré.", 500, [], true);
        }
        $appointment->setBooked(true);
        $appointment->setStatus(Appointment::STATUS_BOOKED);
        // add booking history
        $historyHelper->addHistoryItem(History::ACTIONS[History::ACTION_BOOKED], $appointment);
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
     * @Route(path="/departments-by-country", name="api_get_departments_by_country", methods={"GET"})
     * @return JsonResponse
     */
    public function getDepartmentsByCountry(
        DepartmentRepository $departmentRepository,
        Request $request,
        CustomSerializer $serializer
    )
    {
        $departments = $departmentRepository->findBy(
            ['country' => $request->query->get('country')],
            ['code' => 'ASC']
        );
        $data = $serializer->serialize($departments, ['towns']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/towns-by-department", name="api_get_towns_by_department", methods={"GET"})
     * @return JsonResponse
     */
    public function getTownsByDepartments(
        TownRepository $townRepository,
        DepartmentRepository $departmentRepository,
        Request $request,
        CustomSerializer $serializer
    )
    {
        $department = $departmentRepository->find($request->query->get('department'));
        $towns = $townRepository->findBy(
            ['department' => $department],
            ['code' => 'ASC']
        );
        $data = $serializer->serialize($towns, ['users','department']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/bookings-filtered", name="api_bookings_filtered", methods={"POST"})
     * @return JsonResponse
     */
    public function bookingSearchByFilters(
        Request $request,
        CustomSerializer $serializer,
        AppointmentRepository $appointmentRepository
    )
    {
        $params = [];
        foreach ($request->request as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        $appointments = $appointmentRepository->findAvailableBookingsByFilters($params);
        $data = $serializer->serializeByGroups($appointments, ['create_booking']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/get-cities", name="api_get_cities_react_select", methods={"GET"})
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * not used
     */
    public function getCitiesForReactSelect(Request $request, SerializerInterface $serializer)
    {
        $country = $request->query->get('country');
        $city = $request->query->get('city');
        $client = HttpClient::create();
        $apiLogin = 'onestlapourvous';
        $apiKey = 'so4c0d00de65b6aae5842f3e6f4a32040c0f5f7058';
        $url = "http://www.citysearch-api.com/$country/city?login=$apiLogin&apikey=$apiKey&ville=$city";
        $response = $client->request('GET', $url);
        $data = $serializer->serialize($response, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/get-ip", name="api_get_ip")
     * not used
     */
    public function getIp(SerializerInterface $serializer, Request $request) {
        $client = HttpClient::create();
        $url = "https://api.ipify.org/?format=json";
        $response = $client->request('GET', $url);
        $data = $serializer->serialize($response, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/get-localisation", name="api_get_localisation")
     * not used
     */
    public function getLocalisation(SerializerInterface $serializer, Request $request) {
        $client = HttpClient::create();
        $ip = $request->query->get('ip');
        $url = "http://ip-api.com/json/$ip";
        $response = $client->request('GET', $url);
        $data = $serializer->serialize($response, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
