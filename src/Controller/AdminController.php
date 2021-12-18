<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * AdminController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="admin_home")
     */
    public function index(): Response
    {
        /** @var ContactMessageRepository $contactMessageRepository */
        $contactMessageRepository = $this->entityManager->getRepository(ContactMessage::class);

        /** @var ContactMessage[] $ccontactMessages */
        $contactMessages = $contactMessageRepository->findAllGroupedByNameandEmail();

        return $this->render('admin/index.html.twig', [
            'contactMessagesGrouped' => $contactMessages,
        ]);
    }

    /**
     * @param string $identifier
     * @return Response
     * @Route("/show/{identifier}", name="admin_messages_show")
     */
    public function show(string $identifier): Response
    {
        /** @var ContactMessageRepository $messageRepo */
        $messageRepo = $this->entityManager->getRepository(ContactMessage::class);
        /** @var string[] $identifierExploded */
        $identifierExploded = explode(' - ', $identifier);
        /** @var ContactMessage[] $contactMessages */
        $contactMessages = $messageRepo->findBy(['name' =>$identifierExploded[0], 'email' => $identifierExploded[1]]);

        return $this->render('admin/message_show.html.twig', [
            'contactMessages' => $contactMessages
        ]);
    }

    /**
     * @param string $id
     * @return Response
     * @Route("/show/one/{id}", name="admin_message_show")
     */
    public function showOne(string $id): Response
    {
        /** @var ContactMessage|null $contactMessage */
        $contactMessage = $this->entityManager->getRepository(ContactMessage::class)->find($id);
        return $this->render('admin/messageOne_show.html.twig', [
            'contactMessage' => $contactMessage
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/delete", name="admin_message_delete")
     */
    public function delete(Request $request): JsonResponse
    {
        /** @var string $id */
        $id = $request->get('id');
        /** @var ContactMessageRepository $contactMessageRepo */
        $contactMessageRepo = $this->entityManager->getRepository(ContactMessage::class);
        /** @var ContactMessage|null $contactMessageToDelete */
        $contactMessageToDelete = $contactMessageRepo->find($id);

        if ($contactMessageToDelete) {
            /** @var string $filename */
            $filename = $contactMessageToDelete->getName() . '_' .
                $contactMessageToDelete->getEmail() . '_' .
                $contactMessageToDelete->getCreatedAt()->format('d-m-Y-h-m-s') . '.json';
            /** @var Filesystem $fs */
            $fs = new Filesystem();
            $fs->remove($this->getParameter('json_directory') . $filename);

            $this->entityManager->remove($contactMessageToDelete);
            $this->entityManager->flush();
            return $this->json(true);
        }

        return $this->json(false);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/review", name="admin_message_review")
     */
    public function review(Request $request): JsonResponse
    {
        /** @var string $id */
        $id = $request->get('id');
        /** @var string|null $review */
        $review = $request->get('review');
        /** @var ContactMessageRepository $contactMessageRepo */
        $contactMessageRepo = $this->entityManager->getRepository(ContactMessage::class);
        /** @var ContactMessage|null $messageToReview */
        $messageToReview = $contactMessageRepo->find($id);

        if ($messageToReview && ($review === null || $review === "true") ) {
            $messageToReview->setReviewed(true);
            $this->entityManager->flush();
            return $this->json(true);
        } else if ($messageToReview && $review === "false") {
            $messageToReview->setReviewed(false);
            $this->entityManager->flush();
            return $this->json(true);
        }

        return $this->json(false);
    }
}
