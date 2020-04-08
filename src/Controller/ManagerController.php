<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}