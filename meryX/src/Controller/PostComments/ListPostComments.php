<?php
namespace App\Controller\PostComments;

use App\Repository\PostCommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListPostComments extends AbstractController
{
    public function __invoke(PostCommentsRepository $postCommentsRepository)
    {
        $postComments = $postCommentsRepository->findAll();
        return $this->json($postComments, 200, [], [
            'groups' => ['postComments:read']
        ]);
    }
}
