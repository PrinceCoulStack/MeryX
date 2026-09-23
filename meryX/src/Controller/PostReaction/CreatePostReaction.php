<?php
namespace App\Controller\PostReaction;

use App\Entity\PostReaction;
use App\Repository\CompanyPostRepository;
use App\Repository\PostReactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreatePostReaction extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyPostRepository $companyPostRepo, UserRepository $userRepo)
    {
        $postReaction = new PostReaction();
        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid post reaction payload'], 400);
        }

        $resolveEntityId = static function ($value): ?int {
            if ($value === null || $value === '' || $value === []) {
                return null;
            }

            if (is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }

            if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches)) {
                return (int) $matches[1];
            }

            if (is_scalar($value)) {
                $raw = trim((string) $value);
                if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                    return null;
                }

                $idValue = (int) $raw;
                return $idValue > 0 ? $idValue : null;
            }

            return null;
        };

        if ($request->isMethod('POST')) {
            $postId = $resolveEntityId($data['postId'] ?? null);
            if ($postId !== null) {
                $post = $companyPostRepo->find($postId);
                if ($post) {
                    $postReaction->setPostId($post);
                } else {
                    return $this->json(['message' => 'Invalid post ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Post ID is required'], 400);
            }

            $userId = $resolveEntityId($data['userId'] ?? null);
            if ($userId !== null) {
                $user = $userRepo->find($userId);
                if ($user) {
                    $postReaction->setUserId($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            } else {
                return $this->json(['message' => 'User ID is required'], 400);
            }

            $postReaction->setType($data['type'] ?? 'like');
            $postReaction->setCreatedAt(new \DateTimeImmutable());

            $em->persist($postReaction);
            $em->flush();

            return $this->json($this->buildPayload($postReaction), 201);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(PostReaction $postReaction): array
    {
        return [
            'id' => $postReaction->getId(),
            'postId' => $postReaction->getPostId()?->getId(),
            'userId' => $postReaction->getUserId()?->getId(),
            'type' => $postReaction->getType(),
            'createdAt' => $postReaction->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'success' => true,
        ];
    }
}
