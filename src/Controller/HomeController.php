<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        /** @var string[] $infos */
        $infos = [];
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ContactMessage $message */
            $message = $form->getData();

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $fs = new Filesystem();
            try {
                $filename = $message->getName() . '_' . $message->getEmail() . '_' . $message->getCreatedAt()->format('d-m-Y-h-m-s') . '.json';
                $fs->dumpFile($this->getParameter('json_directory') . $filename, json_encode($message->serialize()));
            } catch (IOException $exception) {
                dd($exception);
            }

            $infos = ['Votre message a bien été envoyé'];
        }

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
            'infos' => $infos
        ]);
    }
}
