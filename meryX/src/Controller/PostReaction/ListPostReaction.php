<?php
namespace App\Controller\PostReaction;

use App\Repository\PostReactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListPostReaction extends AbstractController
{
    public function __invoke(PostReactionRepository $postReactionRepository)
    {
        $postReactions = $postReactionRepository->findAll();
        return $this->json($postReactions, 200, [], [
            'groups' => ['postReaction:read']
        ]);
    }
}
