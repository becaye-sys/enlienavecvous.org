<?php


namespace App\Services;


use Symfony\Component\DependencyInjection\Container;

class MailerFactory
{
    private $mailer;
    private $container;

    public function __construct(\Swift_Mailer $mailer, Container $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function createAndSend($subject, $to, $from, $bodyView, ...$bodyParams)
    {
        $message = (new \Swift_Message($subject))
            ->setTo($to)
            ->setFrom($from)
            ->setBody(
                $this->container->get('twig')->render(
                    "email/{$bodyView}.html.twig",
                    $bodyParams
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}