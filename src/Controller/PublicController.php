<?php


namespace App\Controller;


use App\Entity\Department;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\Town;
use App\Form\PatientRegisterType;
use App\Form\TherapistRegisterType;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\TherapistRepository;
use App\Repository\TownRepository;
use App\Repository\UserRepository;
use App\Services\MailerFactory;
use App\Services\StatisticTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PublicController extends AbstractController
{
    use StatisticTrait;

    /**
     * @Route(path="/", name="index")
     * @return Response
     */
    public function index()
    {
        $funFacts = $this->getFunFacts();
        return $this->render(
            'public/index.html.twig',
            [
                'fun_facts' => $funFacts
            ]
        );
    }

    /**
     * @Route(path="/demander-de-l-aide", name="ask_for_help")
     */
    public function askForHelpRegister(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory,
        EntityManagerInterface $entityManager,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository
    )
    {
        $patient = new Patient();
        $patientForm = $this->createForm(PatientRegisterType::class, $patient);
        $patientForm->handleRequest($request);

        if ($request->isMethod('POST') && $patientForm->isSubmitted() && $patientForm->isValid()) {
            $selectedCountry = $request->request->get('country');
            $selectedDepartment = $request->request->get('department');
            $selectedTown = $request->request->get('town');
            $department = $departmentRepository->findOneBy(['country' => $selectedCountry, 'id' => $selectedDepartment]);
            $town = $townRepository->find($selectedTown);
            $town->setDepartment($department);
            if ($patientForm->getData() instanceof Patient) {
                /** @var Patient $user */
                $user = $patientForm->getData();
                $user->setCountry($selectedCountry);
                $user->setTown($town);
                $user = $user->setUniqueEmailToken();
                $user = $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $emailToken = $user->getEmailToken();
                $mailerFactory->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    'accueil@enlienavecvous.org',
                    $this->renderView(
                        'email/patient_registration.html.twig',
                        ['email_token' => $emailToken, 'project_url' => $_ENV['PROJECT_URL']]
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("success","Votre compte a été créé avec succès !");
                return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
            }
        }

        return $this->render(
            'public/ask_for_help.html.twig',
            [
                'patient_register_form' => $patientForm->createView(),
            ]
        );
    }

    /**
     * @Route(path="/proposer-mon-aide", name="therapist_register")
     */
    public function therapistRegister(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailer,
        EntityManagerInterface $entityManager,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository
    )
    {
        $therapist = new Therapist();
        $therapistForm = $this->createForm(TherapistRegisterType::class, $therapist);
        $therapistForm->handleRequest($request);

        if ($request->isMethod('POST') && $therapistForm->isSubmitted() && $therapistForm->isValid()) {
            $selectedCountry = $request->request->get('country');
            $selectedDepartment = $request->request->get('department');
            $selectedTown = $request->request->get('town');
            $department = $departmentRepository->findOneBy(['country' => $selectedCountry, 'id' => $selectedDepartment]);
            $town = $townRepository->find($selectedTown);
            $town->setDepartment($department);
            if ($therapistForm->getData() instanceof Therapist) {
                /** @var Therapist $user */
                $user = $therapistForm->getData();
                $user->setCountry($selectedCountry);
                $user->setTown($town);
                $user = $user->setUniqueEmailToken();
                $user = $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $emailToken = $user->getEmailToken();
                $mailer->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    'accueil@enlienavecvous.org',
                    $this->renderView(
                        'email/therapist_registration.html.twig',
                        ['email_token' => $emailToken, 'project_url' => $_ENV['PROJECT_URL']]
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("success","Votre compte a été créé avec succès !");
                return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
            }
        }

        return $this->render(
            'public/therapist_register.html.twig',
            [
                'therapist_register_form' => $therapistForm->createView(),
            ]
        );
    }

    /**
     * @Route(path="/email/confirmation/{emailToken}")
     * @param Request $request
     */
    public function registrationConfirmationCheck(Request $request, RequestContext $requestContext, UserRepository $userRepository, TherapistRepository $therapistRepository, EntityManagerInterface $entityManager)
    {
        $token = substr($requestContext->getPathInfo(), 20, strlen($requestContext->getPathInfo()));
        $user = $userRepository->findOneBy(['emailToken' => $token]);
        if ($user && false === $user->isActive()) {
            $user->setEmailToken('')->setIsActive(true);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        } else if ($user && true === $user->isActive()) {
            $this->addFlash('error', "Votre nouvelle adresse email vient d'être confirmée.");
            return $this->redirectToRoute('app_login');
        } else {
            $this->addFlash('error', "Votre code de confirmation n'est pas valide, veuillez contacter le support de la plateforme ou créer votre compte.");
            return $this->redirectToRoute('therapist_register');
        }
    }

    /**
     * @Route(path="/proposer-mon-aide/en-attente-de-validation", name="registration_waiting_for_email_validation")
     * @return Response
     */
    public function registrationSuccessfull()
    {
        return $this->render(
            'public/registration_successfull.html.twig'
        );
    }

    /**
     * @Route(path="/comment-ca-marche", name="how_it_works")
     */
    public function howItWorks()
    {
        return $this->render(
            'public/how_it_works.html.twig'
        );
    }

    /**
     * @Route(path="/numeros-d-urgence", name="emergency_numbers")
     */
    public function emergencyNumbers()
    {
        return $this->render(
            'public/emergency_numbers.html.twig'
        );
    }

    /**
     * @Route(path="/le-projet", name="the_project")
     */
    public function theProject()
    {
        return $this->render(
            'public/the_project.html.twig'
        );
    }

    /**
     * @Route(path="/qui-sommes-nous", name="who_are_we")
     */
    public function whoAreWe()
    {
        return $this->render(
            'public/who_are_we.html.twig'
        );
    }

    /**
     * @Route(path="/politique-de-protection-des-donnees", name="data_privacy_policy")
     */
    public function dataPrivacy()
    {
        return $this->render(
            'public/data_privacy_policy.html.twig'
        );
    }

    /**
     * @Route(path="/conditions-d-utilisation", name="terms_of_use")
     */
    public function termsOfUse()
    {
        return $this->render(
            'public/terms_of_use.html.twig'
        );
    }

    /**
     * @Route(path="/mentions-legales", name="legal_notices")
     */
    public function legalNotices()
    {
        return $this->render(
            'public/legal_notices.html.twig'
        );
    }

    /**
     * @Route(path="/coming-soon", name="coming_soon")
     * @return Response
     */
    public function comingSoon()
    {
        return $this->render('public/coming_soon.html.twig');
    }
}
