<?php


namespace App\Controller;


use App\Entity\Department;
use App\Entity\Town;
use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ManagerController
 * @package App\Controller
 * @Route(path="/manager")
 */
class ManagerController extends AbstractController
{
    /**
     * @Route(path="/new-users", name="manager_new_users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function newUsers(UserRepository $userRepository, Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        if ($request->isMethod("POST")) {
            $from = $request->request->get('date_from');
            $to = $request->request->get('date_to');
            $newUsers = $userRepository->findRecentlyRegistered($from, $to);
            return $this->render(
                'manager/new_users.html.twig',
                [
                    'new_users' => $newUsers
                ]
            );
        }
        $newUsers = $userRepository->findAll();
        return $this->render(
            'manager/new_users.html.twig',
            [
                'new_users' => $newUsers
            ]
        );
    }

    /**
     * @Route(path="/resend-email-confirmation/{id}", name="manager_resend_email_confirmation")
     * @ParamConverter(name="id", class="App\Entity\User")
     * @return RedirectResponse
     */
    public function resendEmailValidation(User $user)
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette fonctionnalité.");
        dump($user);
        return $this->redirectToRoute('manager_new_users');
    }
    /**
     * @Route(path="/manage-users", name="manager_manage_users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function manageUsers(UserRepository $userRepository, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_MANAGER", null, "Vous n'avez pas accès à cette page.");

        if ($request->isMethod("POST")) {
            $role = $request->request->get('user_role');
            $userId = $request->request->get('user_id');
            $selectedUser = $userRepository->find($userId);
            if ($selectedUser instanceof User && !in_array($role, $selectedUser->getRoles())) {
                $existentRoles = $selectedUser->getRoles();
                array_push($existentRoles, $role);
                $selectedUser->setRoles($existentRoles);
                $manager->flush();
                $firstName = $selectedUser->getFirstName();
                $lastName = $selectedUser->getLastName();
                $this->addFlash('success', "Role ajouté à $firstName $lastName !");
                return $this->redirectToRoute('manager_manage_users');
            } else {
                $this->addFlash('error', "Ce membre a déjà le role sélectionné.");
                return $this->redirectToRoute('manager_manage_users');
            }
        }

        $params = [];
        dump($request->query);
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }
        dump($params);

        if (count($params) === 0) {
            dump('no params');
            $users = $userRepository->findByParams();
        } else {
            dump('some params');
            $users = $userRepository->findByParams($params);
        }

        return $this->render(
            'manager/manage_members.html.twig',
            [
                'users' => $users
            ]
        );
    }

    /**
     * @Route(path="/en-attente", name="manager_users_waiting")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function usersWaitingForActivation(UserRepository $userRepository)
    {
        return $this->render(
            'manager/users_waiting_for_activation.html.twig',
            [
                'new_users' => $userRepository->findBy(['isActive' => false])
            ]
        );
    }

    /**
     * @Route(path="/zones", name="manager_zones")
     * @param DepartmentRepository $departmentRepository
     * @return Response
     */
    public function geolocalisation(
        Request $request,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository,
        EntityManagerInterface $entityManager
    )
    {
        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        $countries = [
            'fr' => "France",
            'be' => "Belgique",
            'lu' => "Luxembourg",
            'ch' => "Suisse"
        ];

        if ($request->isMethod("POST")) {
            if ($request->request->get('action')) {
                $action = $request->request->get('action');
                // convert in switch case
                if ($action === 'delete') {
                    $code = $request->request->get('code');
                    $deparment = $departmentRepository->findOneBy(['code' => $code]);
                    $departName = $deparment->getName();
                    $cities = $townRepository->findBy(['department' => $deparment]);
                    foreach ($cities as $city) {
                        $entityManager->remove($city);
                    }
                    $entityManager->flush();
                    $this->addFlash('success', "Les villes du département $departName ont été correctement supprimées.");
                    if (count($request->query) > 0) {
                        return $this->redirectToRoute('manager_zones', ['country_filter' => $request->query->get("country_filter")]);
                    } else {
                        return $this->redirectToRoute('manager_zones');
                    }
                }
            }
            $subcode = substr($request->request->get('code'), 0, 2);
            $code = $request->request->get('code');
            $country = $request->request->get('country');
            $deparment = $departmentRepository->findOneBy(['code' => $code]);
            $departName = $deparment->getName();
            $client = HttpClient::create();
            $url = "http://www.citysearch-api.com/$country/city?login=onestlapourvous&apikey=so4c0d00de65b6aae5842f3e6f4a32040c0f5f7058&dp=$code";
            $response = $client->request('GET', $url);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $cities = $response->toArray();
                dump($cities);
                if ($cities["results"]) {
                    foreach ($cities["results"] as $city) {
                        $town = new Town();
                        $town->setDepartment($deparment);
                        $town->setScalarDepart($code);
                        $town->setCode($city["cp"]);
                        $town->setName($city["ville"]);
                        $town->setZipCodes([$city["cp"]]);
                        $entityManager->persist($town);
                    }
                    $entityManager->flush();
                    $this->addFlash('success', "Villes chargées pour le département $departName.");
                    if (count($request->query) > 0) {
                        return $this->redirectToRoute('manager_zones', ['country_filter' => $request->query->get("country_filter")]);
                    } else {
                        return $this->redirectToRoute('manager_zones');
                    }
                } else {
                    $this->addFlash('success', "Le département $code constitue une ville en lui-meme");
                    return $this->redirectToRoute('manager_zones');
                }

            } else {
                $this->addFlash('success', "La récupération des villes pour le département $departName a échoué.");
            }
        }

        if (count($params) === 0) {
            $departments = $departmentRepository->findBy(['country' => 'fr']);
        } else {
            $departments = $departmentRepository->findByParams($params);
        }

        return $this->render(
            'manager/geolocalisation.html.twig',
            [
                'departments' => $departments,
                'countries' => $countries
            ]
        );
    }

    /**
     * @Route(path="/zones/department/{id}", name="manager_zones_by_department")
     * @ParamConverter(name="id", class="App\Entity\Department")
     * @param Department $department
     * @return Response
     */
    public function geolocTownsByDepartment(Department $department)
    {
        return $this->render(
            'manager/geoloc_towns_by_department.html.twig',
            [
                'department' => $department
            ]
        );
    }
}