<?php

namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactMessage[]    findAll()
 * @method ContactMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    /**
     * @return array
     */
    public function findAllGroupedByNameandEmail(): array
    {
        /** @var ContactMessage[] $messages */
        $messages = $this->findAll();
        /** @var array<string, array<ContactMessage>> $messagesGrouped */
        $messagesGrouped = [];

        foreach ($messages as $message) {
            /** @var string $identifier */
            $identifier = $message->getName() . ' - ' . $message->getEmail();
            if (isset($messagesGrouped[$identifier])) {
                $messagesGrouped[$identifier][] = $message;
            } else {
                $messagesGrouped[$identifier] = [$message];
            }
        }

        return $messagesGrouped;
    }
}
