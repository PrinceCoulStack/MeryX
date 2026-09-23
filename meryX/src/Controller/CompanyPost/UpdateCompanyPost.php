<?php

namespace App\Controller\CompanyPost;

use App\Repository\CompanyPostRepository;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use App\Service\CompanyPost\CompanyPostResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateCompanyPost extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyPostRepository $repository, CompanyRepository $companyRepo, UserRepository $userRepo, CompanyPostResponseBuilder $responseBuilder, int $id)
    {
        $companyPost = $repository->find($id);
        if (!$companyPost) {
            return $this->json(['message' => 'Company post not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid company post payload'], 400);
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

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            $companyId = $resolveEntityId($data['companyId'] ?? null);
            if ($companyId !== null) {
                $company = $companyRepo->find($companyId);
                if ($company) {
                    $companyPost->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            }

            $authorId = $resolveEntityId($data['authorId'] ?? null);
            if ($authorId !== null) {
                $author = $userRepo->find($authorId);
                if ($author) {
                    $companyPost->setAuthorId($author);
                } else {
                    return $this->json(['message' => 'Invalid author ID'], 400);
                }
            }

            foreach (['title', 'content', 'category', 'visibility'] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $companyPost->$setter($data[$field]);
                }
            }

            if (array_key_exists('imageUrl', $data) || array_key_exists('image', $data)) {
                $companyPost->setImageUrl($this->sanitizeImageInput($data['imageUrl'] ?? $data['image'] ?? null));
            }

            if (isset($data['publiedAt'])) {
                $companyPost->setPubliedAt(new \DateTimeImmutable($data['publiedAt']));
            }

            if (isset($data['createdAt'])) {
                $companyPost->setCreatedAt(new \DateTimeImmutable($data['createdAt']));
            }

            if (isset($data['updatedAt'])) {
                $companyPost->setUpdatedAt(new \DateTimeImmutable($data['updatedAt']));
            } else {
                $companyPost->setUpdatedAt(new \DateTimeImmutable());
            }

            $em->flush();

            return $this->json($responseBuilder->buildItem($companyPost), 200);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }

    private function sanitizeImageInput(mixed $image): ?string
    {
        if (!is_string($image)) {
            return null;
        }

        $normalized = trim($image);

        return $normalized !== '' ? $normalized : null;
    }
}
