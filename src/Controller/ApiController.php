<?php


namespace App\Controller;


use App\Repository\AppointmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     */
    public function getAvailableAppointmentsFilter(Request $request, AppointmentRepository $appointmentRepository, SerializerInterface $serializer): JsonResponse
    {
        $params = get_object_vars(json_decode($request->getContent()));
        dump($params);
        //$params['bookingDate'] = new \DateTime($params['bookingDate']);
        dump($params['bookingDate']);
        $appoints = $appointmentRepository->findAvailableAppointmentsByParamsSplited($params);
        //$appoints = $appointmentRepository->findBy(
        //    ['location' => $params['location']]
        //);
        dump($appoints);
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($appoints, 'json', ['ignored_attributes' => ['therapist', 'patient']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
