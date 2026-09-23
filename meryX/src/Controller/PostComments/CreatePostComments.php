<?php
namespace App\Controller\PostComments;

use App\Entity\PostComments;
use App\Repository\CompanyPostRepository;
use App\Repository\PostCommentsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreatePostComments extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyPostRepository $companyPostRepo, UserRepository $userRepo)
    {
        $postComment = new PostComments();
        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid post comment payload'], 400);
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
                    $postComment->setPostId($post);
                } else {
                    return $this->json(['message' => 'Invalid post ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Post ID is required'], 400);
            }

            $authorId = $resolveEntityId($data['authorId'] ?? null);
            if ($authorId !== null) {
                $user = $userRepo->find($authorId);
                if ($user) {
                    $postComment->setAuthorId($user);
                } else {
                    return $this->json(['message' => 'Invalid author ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Author ID is required'], 400);
            }

            $postComment->setContent($data['content'] ?? null);
            $postComment->setCreatedAt(new \DateTimeImmutable());
            $postComment->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($postComment);
            $em->flush();

            return $this->json($this->buildPayload($postComment), 201);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(PostComments $postComment): array
    {
        return [
            'id' => $postComment->getId(),
            'postId' => $postComment->getPostId()?->getId(),
            'authorId' => $postComment->getAuthorId()?->getId(),
            'author' => $postComment->getAuthorId()?->getEmail(),
            'content' => $postComment->getContent(),
            'createdAt' => $postComment->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $postComment->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'success' => true,
        ];
    }
}
