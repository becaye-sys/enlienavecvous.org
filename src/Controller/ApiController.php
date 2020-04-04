<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
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
     * @Route(path="/current/{role}", name="api_current_role", methods={"GET"})
     * @return JsonResponse
     */
    public function getCurrentUser(Request $request, PatientRepository $patientRepository, Security $security): JsonResponse
    {
        //$this->denyAccessUnlessGranted("ROLE_PATIENT", null, "Vous n'avez pas accès à cette entrée.");
        dump($security->getUser());
        $routeParams = $request->attributes->get('_route_params');
        if (array_key_exists('role', $routeParams)) {
            switch ($routeParams['role']) {
                case 'patient':
                    //$user = $patientRepository->findOneBy(['email' => $this->getUser()]);
                    break;
                case 'therapist':
                    $user = '';
                    break;
                case 'manager':
                    $user = '';
                    break;
                default:
                    $user = '';
            }
        }
        //$currentUser = $patientRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize("Get current user", 'json');
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
        dump($request->attributes->get('appointment'));
        dump($request->attributes->get('user'));
        $appointId = (int)$request->attributes->get('appointment');
        $userId = (int)$request->attributes->get('user');
        $patient = $patientRepository->find($userId);
        $appointment = $appointmentRepository->find($appointId);
        $patient->addAppointment($appointment);
        $entityManager->flush();
        dump($appointment);
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
        SerializerInterface $serializer
    ): JsonResponse
    {
        $appointment->setBooked(true);
        $entityManager->flush();
        $normalizer = new ObjectNormalizer();

        $data = $serializer->serialize(
            "Rendez-vous confirmé !",
            'json',
        );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
