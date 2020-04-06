<?php


namespace App\Controller;


use App\Entity\Region;
use App\Entity\Therapist;
use App\Form\TherapistRegisterType;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AjaxController
 * @package App\Controller
 * @Route(path="/ajax")
 */
class AjaxController extends AbstractController
{
    /**
     * @Route(path="/department-select/{id}", name="ajax_department_select", defaults={"id"=1})
     * @ParamConverter(name="id", class="App\Entity\Region")
     * @param Request $request
     */
    public function getDepartmentSelect(Request $request, Region $region, DepartmentRepository $departmentRepository)
    {
        $user = new Therapist();
        $departments = $departmentRepository->findBy(['codeRegion' => $region->getCode()]);
        dump($departments);
        $user->setRegion($region);

        $form = $this->createForm(TherapistRegisterType::class, $user);
        if (!$form->has('department')) {
            return new Response(null, 204);
        }
        return $this->render(
            'public/_forms/_department_form_field.html.twig',
            [
                'therapist_register_form' => $form->createView()
            ]
        );
    }

    /**
     * @Route(path="/town-select/", name="ajax_town_select")
     * @param Request $request
     */
    public function getTownSelect(Request $request, TownRepository $townRepository, DepartmentRepository $departmentRepository)
    {
        $user = new Therapist();
        $code = $request->query->get('code');
        $department = $departmentRepository->findOneBy(['code' => $code]);
        $user->setDepartment($department);
        $towns = $townRepository->findBy(['department' => $code]);

        $form = $this->createForm(TherapistRegisterType::class, $user);
        if (!$form->has('town')) {
            return new Response(null, 204);
        }
        return $this->render(
            'public/_forms/_town_form_field.html.twig',
            [
                'therapist_register_form' => $form->createView()
            ]
        );
    }

    /**
     * @Route(path="/town-confirmation/", name="ajax_town_confirmation")
     * @param Request $request
     */
    public function getTownConfirmation(Request $request, TownRepository $townRepository)
    {
        $user = new Therapist();
        $id = $request->query->get('id');
        $town = $townRepository->find($id);
        $user->setTown($town);

        return new JsonResponse($user, 200, [], 'json');
    }
}