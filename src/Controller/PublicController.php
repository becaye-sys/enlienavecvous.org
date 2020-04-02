<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\TherapistRegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PublicController extends AbstractController
{

    /**
     * @Route(path="/", name="index")
     * @return Response
     */
    public function index()
    {
        return $this->render(
            'public/index.html.twig'
        );
    }

    /**
     * @Route(path="/demander-de-l-aide", name="ask_for_help")
     */
    public function askForHelpRegister()
    {
        return $this->render(
            'public/ask_for_help.html.twig'
        );
    }

    /**
     * @Route(path="/proposer-mon-aide", name="therapist_register")
     */
    public function therapistRegister(Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer, EntityManagerInterface $entityManager, RequestContext $requestContext)
    {
        $therapistForm = $this->createForm(TherapistRegisterType::class);
        $therapistForm->handleRequest($request);

        if ($request->isMethod('POST') && $therapistForm->isSubmitted() && $therapistForm->isValid()) {
            if ($therapistForm->getData() instanceof User) {
                dump($therapistForm->getData());
                /** @var User $user */
                $user = $therapistForm->getData();
                $user = $user->setUniqueEmailToken();
                $user = $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $emailToken = $user->getEmailToken();
                // send email
                $email = (new Email())
                    ->from('hello@onestlapourvous.org')
                    ->to('louisthomas76750@gmail.com')
                    ->subject("Inscription sur la plateforme Onestlapourvous.org")
                    ->html('<a href=\'https://127.0.0.1:8000/mon-compte/confirmation/'.$emailToken.'\'>Confirmer mon compte<a>', 'utf-8');
                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    throw new TransportException($e->getMessage(), $e->getCode());
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("success","Votre compte a été créé avec succès !");
                return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
            }
        }

        if ($requestContext->getQueryString()) {
            $queryString = $requestContext->getQueryString();
            if (strpos($queryString, '=')) {
                $message = substr($queryString, strpos($queryString, "=")+1);
            }
        }

        return $this->render(
            'public/therapist_register.html.twig',
            [
                'therapist_register_form' => $therapistForm->createView(),
                'message' => $message ?? null
            ]
        );
    }

    /**
     * @Route(path="/mon-compte/confirmation/{emailToken}")
     * @param Request $request
     */
    public function registrationConfirmationCheck(Request $request, RequestContext $requestContext, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $token = substr($requestContext->getPathInfo(), 25, strlen($requestContext->getPathInfo()));
        $user = $userRepository->findOneBy(['emailToken' => $token]);
        if ($user && false === $user->isActive()) {
            $user->setEmailToken('')->setIsActive(true);
            $entityManager->flush();
            return $this->redirectToRoute('index');
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
}
